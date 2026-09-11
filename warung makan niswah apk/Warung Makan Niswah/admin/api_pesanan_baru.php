<?php
/**
 * Endpoint khusus aplikasi Android Admin Niswah
 * Lokasi: /admin/api_pesanan_baru.php
 */
require_once "../includes/config.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Ambil pesanan dengan status 'menunggu' yang terbaru
$query = "
    SELECT id, kode_pesanan, status_pesanan, total_harga, created_at
    FROM pesanan
    WHERE status_pesanan = 'menunggu'
    ORDER BY id DESC
    LIMIT 1
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'has_new_order' => true,
        'latest_order_id' => (int)$row['id'],
        'order_code' => $row['kode_pesanan'],
        'order_status' => $row['status_pesanan'],
        'created_at' => $row['created_at']
    ], JSON_UNESCAPED_UNICODE);
} else {
    $baselineQuery = "SELECT id FROM pesanan ORDER BY id DESC LIMIT 1";
    $baselineResult = $conn->query($baselineQuery);
    $lastId = 0;
    if ($baselineResult && $baselineResult->num_rows > 0) {
        $lastRow = $baselineResult->fetch_assoc();
        $lastId = (int)$lastRow['id'];
    }

    echo json_encode([
        'success' => true,
        'has_new_order' => false,
        'latest_order_id' => $lastId,
        'order_code' => null,
        'order_status' => null
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>
