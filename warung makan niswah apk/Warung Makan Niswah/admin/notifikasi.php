<?php

require_once "../includes/config.php";
require_once "../includes/functions.php";

cekLogin();

header('Content-Type: application/json');

$query = "
    SELECT
        p.id,
        p.kode_pesanan,
        p.total_harga,
        p.status_pesanan,
        p.created_at,
        pl.nama
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    WHERE p.status_pesanan = 'menunggu'
    ORDER BY p.created_at DESC
    LIMIT 10
";

$result = $conn->query($query);

$data = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $data[] = [

            'id' => (int) $row['id'],

            'kode' =>
                $row['kode_pesanan'],

            'nama' =>
                $row['nama'],

            'total' =>
                number_format(
                    $row['total_harga'],
                    0,
                    ',',
                    '.'
                ),

            'status' =>
                $row['status_pesanan'],

            'tanggal' =>
                date(
                    'd-m-Y H:i',
                    strtotime(
                        $row['created_at']
                    )
                )

        ];

    }

}

echo json_encode([
    'success' => true,
    'jumlah' => count($data),
    'pesanan' => $data
]);

?>