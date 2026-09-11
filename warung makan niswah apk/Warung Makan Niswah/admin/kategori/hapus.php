<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);


if ($id > 0) {

    // Cek apakah kategori masih dipakai menu
    $cek = $conn->prepare("
        SELECT COUNT(*) AS jumlah
        FROM menu
        WHERE kategori_id = ?
    ");

    $cek->bind_param(
        "i",
        $id
    );

    $cek->execute();

    $result =
        $cek->get_result()->fetch_assoc();


    if ($result['jumlah'] == 0) {

        $delete = $conn->prepare("
            DELETE FROM kategori
            WHERE id = ?
        ");

        $delete->bind_param(
            "i",
            $id
        );

        $delete->execute();

        $delete->close();
    }

    $cek->close();
}


header("Location: index.php");

exit;

?>