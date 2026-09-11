package com.example.warungniswah.notification

import android.content.Context
import android.util.Log
import android.webkit.CookieManager
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONArray
import org.json.JSONObject
import java.io.BufferedReader
import java.io.InputStreamReader
import java.net.HttpURLConnection
import java.net.URL

object OrderChecker {

    private const val TAG = "OrderChecker"

    private val CHECK_ENDPOINTS = listOf(
        "https://warungmakanniswah.free.nf/api_pesanan_baru.php",
        "https://warungmakanniswah.free.nf/admin/api_pesanan_baru.php",
        "https://warungmakanniswah.free.nf/admin/pesanan/cek-baru.php",
        "https://warungmakanniswah.free.nf/admin/notifikasi.php"
    )

    data class CheckResult(
        val hasNewOrder: Boolean,
        val orderId: Int,
        val orderCode: String?,
        val orderStatus: String?
    )

    suspend fun checkNewOrders(context: Context): Boolean = withContext(Dispatchers.IO) {
        val lastNotifiedId = NotificationHelper.getLastNotifiedOrderId(context)
        Log.d(TAG, "Checking for new orders. Last notified ID: $lastNotifiedId")

        // Retrieve current cookies from WebView for domain
        val cookie = try {
            CookieManager.getInstance().getCookie("https://warungmakanniswah.free.nf")
        } catch (e: Exception) {
            null
        }

        var result: CheckResult? = null

        for (endpoint in CHECK_ENDPOINTS) {
            try {
                val jsonResponse = fetchUrl(endpoint, cookie)
                if (!jsonResponse.isNullOrBlank()) {
                    val parsed = parseResponse(jsonResponse)
                    if (parsed != null) {
                        result = parsed
                        Log.d(TAG, "Success from $endpoint -> $result")
                        break
                    }
                }
            } catch (e: Exception) {
                Log.w(TAG, "Failed fetching $endpoint: ${e.message}")
            }
        }

        if (result == null) {
            Log.d(TAG, "No successful endpoint response")
            return@withContext false
        }

        val orderId = result.orderId
        val hasNew = result.hasNewOrder

        if (hasNew && orderId > 0 && orderId > lastNotifiedId) {
            Log.i(TAG, "New order detected! ID: $orderId, Code: ${result.orderCode}. Triggering notification.")
            NotificationHelper.showNewOrderNotification(
                context = context,
                orderId = orderId,
                orderCode = result.orderCode
            )
            // Save last notified ID to prevent duplicate notifications
            NotificationHelper.setLastNotifiedOrderId(context, orderId)
            return@withContext true
        } else if (lastNotifiedId == 0 && orderId > 0 && !hasNew) {
            // First run baseline: save latest ID so we only alert for future incoming orders
            Log.d(TAG, "Setting baseline order ID: $orderId")
            NotificationHelper.setLastNotifiedOrderId(context, orderId)
        }

        return@withContext false
    }

    private fun parseResponse(jsonStr: String): CheckResult? {
        return try {
            val root = JSONObject(jsonStr)

            // Pattern 1: api_pesanan_baru.php
            if (root.has("has_new_order")) {
                val hasNew = root.optBoolean("has_new_order", false)
                val id = root.optInt("latest_order_id", 0)
                val code = root.optString("order_code", null)
                val status = root.optString("order_status", null)
                return CheckResult(hasNew, id, code, status)
            }

            // Pattern 2: cek-baru.php -> {"success": true, "pesanan": {"id": ..., "kode_pesanan": ...}}
            if (root.has("pesanan") && !root.isNull("pesanan")) {
                val pesananObj = root.optJSONObject("pesanan")
                if (pesananObj != null) {
                    val id = pesananObj.optInt("id", 0)
                    val code = pesananObj.optString("kode_pesanan", null)
                    return CheckResult(true, id, code, "menunggu")
                }

                // Pattern 3: notifikasi.php -> {"success": true, "jumlah": ..., "pesanan": [{...}]}
                val pesananArray = root.optJSONArray("pesanan")
                if (pesananArray != null && pesananArray.length() > 0) {
                    val first = pesananArray.getJSONObject(0)
                    val id = first.optInt("id", 0)
                    val code = first.optString("kode", null)
                    return CheckResult(true, id, code, "menunggu")
                }
            }

            // Pattern 4: If pesanan is explicitly null, no new pending orders
            if (root.has("pesanan") && root.isNull("pesanan")) {
                return CheckResult(false, 0, null, null)
            }

            null
        } catch (e: Exception) {
            Log.e(TAG, "JSON parse error: ${e.message}")
            null
        }
    }

    private fun fetchUrl(urlString: String, cookie: String?): String? {
        var connection: HttpURLConnection? = null
        return try {
            val url = URL(urlString)
            connection = url.openConnection() as HttpURLConnection
            connection.requestMethod = "GET"
            connection.connectTimeout = 8000
            connection.readTimeout = 8000
            connection.setRequestProperty("User-Agent", "Mozilla/5.0 NiswahAdminAndroid/1.0")
            connection.setRequestProperty("Accept", "application/json")
            if (!cookie.isNullOrBlank()) {
                connection.setRequestProperty("Cookie", cookie)
            }

            val responseCode = connection.responseCode
            if (responseCode == HttpURLConnection.HTTP_OK) {
                val reader = BufferedReader(InputStreamReader(connection.inputStream))
                val sb = StringBuilder()
                var line: String?
                while (reader.readLine().also { line = it } != null) {
                    sb.append(line)
                }
                reader.close()
                sb.toString()
            } else {
                null
            }
        } catch (e: Exception) {
            null
        } finally {
            connection?.disconnect()
        }
    }
}
