package com.example.warungniswah

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.webkit.*
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.core.content.ContextCompat
import com.example.warungniswah.notification.NotificationHelper
import com.example.warungniswah.notification.OrderCheckWorker
import com.example.warungniswah.notification.OrderChecker
import com.example.warungniswah.ui.theme.OrangeLight
import com.example.warungniswah.ui.theme.OrangePrimary
import com.example.warungniswah.ui.theme.OrangePrimaryDark
import com.example.warungniswah.ui.theme.WarungMakanNiswahTheme
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class MainActivity : ComponentActivity() {

    companion object {
        const val ADMIN_LOGIN_URL = "https://warungmakanniswah.free.nf/admin/login.php"
        const val ADMIN_ORDERS_URL = "https://warungmakanniswah.free.nf/admin/pesanan/index.php"
    }

    private var pendingTargetUrl: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        // Enable cookies globally and ensure persistence
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)

        // Initialize Notification Channel with Importance HIGH
        NotificationHelper.createNotificationChannel(this)

        // Schedule periodic background checking via WorkManager
        OrderCheckWorker.schedule(this)

        // Check if opened from notification click
        handleIntent(intent)

        setContent {
            WarungMakanNiswahTheme {
                AdminWebViewScreen(targetUrl = pendingTargetUrl)
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleIntent(intent)
    }

    private fun handleIntent(intent: Intent?) {
        if (intent?.getBooleanExtra("OPEN_ORDERS", false) == true) {
            val orderId = intent.getIntExtra("ORDER_ID", 0)
            pendingTargetUrl = if (orderId > 0) {
                "https://warungmakanniswah.free.nf/admin/pesanan/detail.php?id=$orderId"
            } else {
                ADMIN_ORDERS_URL
            }
        }
    }

    override fun onPause() {
        super.onPause()
        CookieManager.getInstance().flush()
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@SuppressLint("SetJavaScriptEnabled")
@Composable
fun AdminWebViewScreen(targetUrl: String? = null) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    var webViewInstance by remember { mutableStateOf<WebView?>(null) }
    var canGoBack by remember { mutableStateOf(false) }
    var pageTitle by remember { mutableStateOf("Admin Niswah") }
    var currentUrl by remember { mutableStateOf(targetUrl ?: MainActivity.ADMIN_LOGIN_URL) }
    var isLoading by remember { mutableStateOf(true) }
    var progressValue by remember { mutableFloatStateOf(0f) }
    var loadError by remember { mutableStateOf<String?>(null) }

    // File upload callback for WebChromeClient.onShowFileChooser
    var filePathCallback by remember { mutableStateOf<ValueCallback<Array<Uri>>?>(null) }

    // Request Notification permission for Android 13+ (Tiramisu)
    val notifPermissionLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.RequestPermission()
    ) { isGranted ->
        if (isGranted) {
            Toast.makeText(context, "Notifikasi pesanan baru aktif!", Toast.LENGTH_SHORT).show()
        }
    }

    LaunchedEffect(Unit) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            val permissionCheck = ContextCompat.checkSelfPermission(
                context,
                Manifest.permission.POST_NOTIFICATIONS
            )
            if (permissionCheck != PackageManager.PERMISSION_GRANTED) {
                notifPermissionLauncher.launch(Manifest.permission.POST_NOTIFICATIONS)
            }
        }
    }

    // Foreground periodic checker: checks every 30 seconds while app is active
    LaunchedEffect(Unit) {
        while (true) {
            delay(30_000L)
            try {
                OrderChecker.checkNewOrders(context)
            } catch (e: Exception) {
                // Ignore transient network errors
            }
        }
    }

    // Activity result launcher for file/image picking
    val filePickerLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val intentData = result.data
            val uris = when {
                intentData?.clipData != null -> {
                    val count = intentData.clipData!!.itemCount
                    Array(count) { i -> intentData.clipData!!.getItemAt(i).uri }
                }
                intentData?.data != null -> {
                    arrayOf(intentData.data!!)
                }
                else -> null
            }
            filePathCallback?.onReceiveValue(uris)
        } else {
            filePathCallback?.onReceiveValue(null)
        }
        filePathCallback = null
    }

    // Handle Android system back press inside WebView
    BackHandler(enabled = canGoBack) {
        if (webViewInstance?.canGoBack() == true) {
            webViewInstance?.goBack()
        }
    }

    Scaffold(
        modifier = Modifier.fillMaxSize(),
        topBar = {
            Column {
                TopAppBar(
                    colors = TopAppBarDefaults.topAppBarColors(
                        containerColor = Color.White,
                        titleContentColor = Color(0xFF1F1A17)
                    ),
                    title = {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(10.dp)
                        ) {
                            Surface(
                                shape = RoundedCornerShape(8.dp),
                                color = OrangeLight,
                                modifier = Modifier.size(34.dp)
                            ) {
                                Box(contentAlignment = Alignment.Center) {
                                    Text("🍽️", fontSize = 18.sp)
                                }
                            }
                            Column {
                                Text(
                                    text = "Admin Niswah",
                                    style = MaterialTheme.typography.titleMedium,
                                    fontWeight = FontWeight.Bold,
                                    color = OrangePrimaryDark
                                )
                                Text(
                                    text = if (currentUrl.contains("/admin/")) "Panel Administrator" else "Warung Makan Niswah",
                                    fontSize = 11.sp,
                                    color = Color.Gray,
                                    maxLines = 1
                                )
                            }
                        }
                    },
                    actions = {
                        // Check order status / test notification
                        IconButton(
                            onClick = {
                                coroutineScope.launch {
                                    Toast.makeText(context, "Mengecek pesanan baru...", Toast.LENGTH_SHORT).show()
                                    val found = OrderChecker.checkNewOrders(context)
                                    if (!found) {
                                        Toast.makeText(context, "Belum ada pesanan baru yang belum dinotifikasi.", Toast.LENGTH_SHORT).show()
                                    }
                                }
                            },
                            modifier = Modifier.testTag("admin_check_orders_button")
                        ) {
                            Icon(
                                imageVector = Icons.Default.NotificationsActive,
                                contentDescription = "Cek Pesanan Baru",
                                tint = OrangePrimary
                            )
                        }

                        // Go to orders page
                        IconButton(
                            onClick = {
                                loadError = null
                                webViewInstance?.loadUrl(MainActivity.ADMIN_ORDERS_URL)
                            },
                            modifier = Modifier.testTag("admin_orders_page_button")
                        ) {
                            Icon(
                                imageVector = Icons.Default.ReceiptLong,
                                contentDescription = "Daftar Pesanan",
                                tint = Color(0xFF5A524C)
                            )
                        }

                        // Home Admin
                        IconButton(
                            onClick = {
                                loadError = null
                                webViewInstance?.loadUrl(MainActivity.ADMIN_LOGIN_URL)
                            },
                            modifier = Modifier.testTag("admin_home_button")
                        ) {
                            Icon(
                                imageVector = Icons.Default.Home,
                                contentDescription = "Home Admin",
                                tint = OrangePrimaryDark
                            )
                        }

                        // Reload button
                        IconButton(
                            onClick = {
                                loadError = null
                                webViewInstance?.reload()
                            },
                            modifier = Modifier.testTag("admin_refresh_button")
                        ) {
                            Icon(
                                imageVector = Icons.Default.Refresh,
                                contentDescription = "Muat Ulang",
                                tint = Color(0xFF5A524C)
                            )
                        }
                    }
                )

                // Loading progress indicator bar
                AnimatedVisibility(
                    visible = isLoading && progressValue < 1f,
                    enter = fadeIn(),
                    exit = fadeOut()
                ) {
                    LinearProgressIndicator(
                        progress = { progressValue },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(3.dp),
                        color = OrangePrimary,
                        trackColor = OrangeLight
                    )
                }
            }
        }
    ) { innerPadding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
        ) {
            AndroidView(
                modifier = Modifier
                    .fillMaxSize()
                    .testTag("admin_webview"),
                factory = { ctx ->
                    WebView(ctx).apply {
                        val cookieManager = CookieManager.getInstance()
                        cookieManager.setAcceptCookie(true)
                        cookieManager.setAcceptThirdPartyCookies(this, true)

                        settings.apply {
                            javaScriptEnabled = true
                            domStorageEnabled = true
                            allowFileAccess = true
                            allowContentAccess = true
                            loadWithOverviewMode = true
                            useWideViewPort = true
                            setSupportZoom(true)
                            builtInZoomControls = true
                            displayZoomControls = false
                            cacheMode = WebSettings.LOAD_DEFAULT
                            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
                            // Modern mobile user-agent
                            val defaultUa = userAgentString
                            userAgentString = "$defaultUa Mobile NiswahAdmin/1.0"
                        }

                        webChromeClient = object : WebChromeClient() {
                            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                                progressValue = newProgress / 100f
                                isLoading = newProgress < 100
                            }

                            override fun onReceivedTitle(view: WebView?, title: String?) {
                                if (!title.isNullOrBlank()) {
                                    pageTitle = title
                                }
                            }

                            // Support for file/photo uploading (menu photos, payment proofs)
                            override fun onShowFileChooser(
                                webView: WebView?,
                                newFilePathCallback: ValueCallback<Array<Uri>>?,
                                fileChooserParams: FileChooserParams?
                            ): Boolean {
                                filePathCallback?.onReceiveValue(null)
                                filePathCallback = newFilePathCallback

                                val intent = Intent(Intent.ACTION_GET_CONTENT).apply {
                                    addCategory(Intent.CATEGORY_OPENABLE)
                                    type = "*/*"
                                    fileChooserParams?.acceptTypes?.let { types ->
                                        val validTypes = types.filter { it.isNotBlank() }
                                        if (validTypes.isNotEmpty()) {
                                            if (validTypes.size == 1) {
                                                type = validTypes[0]
                                            } else {
                                                putExtra(Intent.EXTRA_MIME_TYPES, validTypes.toTypedArray())
                                            }
                                        }
                                    }
                                }

                                return try {
                                    filePickerLauncher.launch(
                                        Intent.createChooser(intent, "Pilih File / Foto")
                                    )
                                    true
                                } catch (e: Exception) {
                                    filePathCallback?.onReceiveValue(null)
                                    filePathCallback = null
                                    false
                                }
                            }
                        }

                        webViewClient = object : WebViewClient() {
                            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                                isLoading = true
                                loadError = null
                                url?.let { currentUrl = it }
                                canGoBack = canGoBack()
                            }

                            override fun onPageFinished(view: WebView?, url: String?) {
                                isLoading = false
                                url?.let { currentUrl = it }
                                canGoBack = canGoBack()
                                CookieManager.getInstance().flush()
                            }

                            override fun onReceivedError(
                                view: WebView?,
                                request: WebResourceRequest?,
                                error: WebResourceError?
                            ) {
                                if (request?.isForMainFrame == true) {
                                    isLoading = false
                                    loadError = error?.description?.toString() ?: "Gagal memuat halaman"
                                }
                            }

                            override fun shouldOverrideUrlLoading(
                                view: WebView?,
                                request: WebResourceRequest?
                            ): Boolean {
                                val url = request?.url?.toString() ?: return false
                                if (url.startsWith("http://") || url.startsWith("https://")) {
                                    return false
                                }
                                return try {
                                    val externalIntent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                                    ctx.startActivity(externalIntent)
                                    true
                                } catch (e: Exception) {
                                    false
                                }
                            }
                        }

                        loadUrl(targetUrl ?: MainActivity.ADMIN_LOGIN_URL)
                        webViewInstance = this
                    }
                },
                update = { webView ->
                    webViewInstance = webView
                    canGoBack = webView.canGoBack()
                }
            )

            // Connection Error Fallback Screen
            if (loadError != null) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .background(Color(0xFFFDFBF7))
                        .padding(24.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Card(
                        shape = RoundedCornerShape(20.dp),
                        colors = CardDefaults.cardColors(containerColor = Color.White),
                        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Column(
                            modifier = Modifier.padding(24.dp),
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.spacedBy(14.dp)
                        ) {
                            Surface(
                                color = OrangeLight,
                                shape = RoundedCornerShape(16.dp),
                                modifier = Modifier.size(64.dp)
                            ) {
                                Box(contentAlignment = Alignment.Center) {
                                    Icon(
                                        imageVector = Icons.Default.CloudOff,
                                        contentDescription = null,
                                        tint = OrangePrimary,
                                        modifier = Modifier.size(36.dp)
                                    )
                                }
                            }

                            Text(
                                text = "Koneksi Terputus",
                                style = MaterialTheme.typography.titleLarge,
                                fontWeight = FontWeight.Bold,
                                color = Color(0xFF1F1A17)
                            )

                            Text(
                                text = "Tidak dapat terhubung ke server website. Pastikan perangkat Anda terhubung ke internet.",
                                style = MaterialTheme.typography.bodyMedium,
                                color = Color.Gray,
                                textAlign = androidx.compose.ui.text.style.TextAlign.Center
                            )

                            Button(
                                onClick = {
                                    loadError = null
                                    webViewInstance?.reload()
                                },
                                colors = ButtonDefaults.buttonColors(containerColor = OrangePrimary),
                                shape = RoundedCornerShape(12.dp),
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .height(48.dp)
                                    .testTag("admin_retry_button")
                            ) {
                                Icon(imageVector = Icons.Default.Refresh, contentDescription = null)
                                Spacer(modifier = Modifier.width(8.dp))
                                Text("Coba Lagi", fontWeight = FontWeight.Bold)
                            }

                            OutlinedButton(
                                onClick = {
                                    loadError = null
                                    webViewInstance?.loadUrl(MainActivity.ADMIN_LOGIN_URL)
                                },
                                shape = RoundedCornerShape(12.dp),
                                modifier = Modifier.fillMaxWidth()
                            ) {
                                Text("Buka Halaman Login Awal")
                            }
                        }
                    }
                }
            }
        }
    }
}
