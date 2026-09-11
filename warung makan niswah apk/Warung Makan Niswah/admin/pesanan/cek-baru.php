<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL PESANAN MENUNGGU TERBARU
// =====================================================

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.kode_pesanan,
        p.total_harga,
        p.metode_pembayaran,
        p.created_at,
        pl.nama
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    WHERE p.status_pesanan = 'menunggu'
    ORDER BY p.created_at DESC
    LIMIT 1
");


$stmt->execute();


$result =
    $stmt->get_result();


$data = null;


if (
    $result->num_rows > 0
) {

    $data =
        $result->fetch_assoc();

}


$stmt->close();


// =====================================================
// RESPONSE JSON
// =====================================================

header(
    'Content-Type: application/json; charset=utf-8'
);


echo json_encode(

    [

        'success' =>
            true,

        'pesanan' =>
            $data

    ],

    JSON_UNESCAPED_UNICODE

);


exit;