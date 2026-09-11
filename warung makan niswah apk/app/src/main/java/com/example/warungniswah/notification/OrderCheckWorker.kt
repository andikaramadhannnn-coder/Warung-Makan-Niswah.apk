package com.example.warungniswah.notification

import android.content.Context
import android.util.Log
import androidx.work.*
import java.util.concurrent.TimeUnit

class OrderCheckWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    override suspend fun doWork(): Result {
        Log.d("OrderCheckWorker", "Executing background periodic check for new orders...")
        return try {
            OrderChecker.checkNewOrders(applicationContext)
            Result.success()
        } catch (e: Exception) {
            Log.e("OrderCheckWorker", "Error in periodic check: ${e.message}", e)
            Result.retry()
        }
    }

    companion object {
        private const val WORK_NAME = "NiswahPeriodicOrderCheck"

        fun schedule(context: Context) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build()

            // 15 minutes is the Android WorkManager minimum interval for PeriodicWorkRequest
            val periodicWork = PeriodicWorkRequestBuilder<OrderCheckWorker>(15, TimeUnit.MINUTES)
                .setConstraints(constraints)
                .setBackoffCriteria(BackoffPolicy.EXPONENTIAL, 1, TimeUnit.MINUTES)
                .build()

            WorkManager.getInstance(context).enqueueUniquePeriodicWork(
                WORK_NAME,
                ExistingPeriodicWorkPolicy.KEEP,
                periodicWork
            )
            Log.d("OrderCheckWorker", "Periodic WorkManager enqueued with 15-minute interval.")
        }
    }
}
