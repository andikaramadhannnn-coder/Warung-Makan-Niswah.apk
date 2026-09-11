package com.example.warungniswah.notification

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.example.warungniswah.MainActivity
import com.example.warungniswah.R

object NotificationHelper {

    const val CHANNEL_ID = "niswah_admin_orders_channel"
    private const val CHANNEL_NAME = "Pesanan Baru Admin"
    private const val CHANNEL_DESC = "Notifikasi suara dan getaran saat ada pesanan baru masuk ke Warung Makan Niswah"

    private const val PREFS_NAME = "niswah_admin_prefs"
    private const val KEY_LAST_NOTIFIED_ID = "last_notified_order_id"

    fun getPrefs(context: Context): SharedPreferences {
        return context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
    }

    fun getLastNotifiedOrderId(context: Context): Int {
        return getPrefs(context).getInt(KEY_LAST_NOTIFIED_ID, 0)
    }

    fun setLastNotifiedOrderId(context: Context, orderId: Int) {
        getPrefs(context).edit().putInt(KEY_LAST_NOTIFIED_ID, orderId).apply()
    }

    fun createNotificationChannel(context: Context) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
            val audioAttributes = AudioAttributes.Builder()
                .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                .setUsage(AudioAttributes.USAGE_NOTIFICATION_RINGTONE)
                .build()

            val channel = NotificationChannel(
                CHANNEL_ID,
                CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = CHANNEL_DESC
                enableLights(true)
                enableVibration(true)
                vibrationPattern = longArrayOf(0, 500, 250, 500)
                setSound(soundUri, audioAttributes)
                lockscreenVisibility = NotificationCompat.VISIBILITY_PUBLIC
            }

            val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            notificationManager.createNotificationChannel(channel)
        }
    }

    fun showNewOrderNotification(context: Context, orderId: Int = 0, orderCode: String? = null) {
        createNotificationChannel(context)

        val intent = Intent(context, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra("OPEN_ORDERS", true)
            if (orderId > 0) {
                putExtra("ORDER_ID", orderId)
            }
        }

        val pendingIntent = PendingIntent.getActivity(
            context,
            1001,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)

        val contentText = if (!orderCode.isNullOrBlank()) {
            "Ada pesanan baru ($orderCode) yang masuk. Silakan buka Admin Panel."
        } else {
            "Ada pesanan baru yang masuk. Silakan buka Admin Panel."
        }

        val notification = NotificationCompat.Builder(context, CHANNEL_ID)
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentTitle("Pesanan Baru!")
            .setContentText(contentText)
            .setStyle(NotificationCompat.BigTextStyle().bigText(contentText))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setCategory(NotificationCompat.CATEGORY_MESSAGE)
            .setSound(soundUri)
            .setVibrate(longArrayOf(0, 500, 250, 500))
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .build()

        try {
            val notificationManager = NotificationManagerCompat.from(context)
            val notifId = if (orderId > 0) orderId else 101
            notificationManager.notify(notifId, notification)
        } catch (e: SecurityException) {
            // Permission missing on Android 13+
        }
    }
}
