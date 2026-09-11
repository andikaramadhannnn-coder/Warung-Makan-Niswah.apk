<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_POST['id'] ?? 0);

$status = $_POST['status'] ?? "";


$statusValid = [
    'menunggu',
    'diterima',
    'diproses',
    'siap_diantar',
    'selesai',
    'dibatalkan'
];


if (
    $id > 0 &&
    in_array($status, $statusValid, true)
) {

    $stmt = $conn->prepare("
        UPDATE pesanan
        SET status_pesanan = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "si",
        $status,
        $id
    );

    $stmt->execute();

    $stmt->close();
}


header(
    "Location: detail.php?id=" . $id
);

exit;

?>