<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID VARIAN
// =====================================================

$varianId = intval(
    $_GET['id'] ?? 0
);


// =====================================================
// VALIDASI
// =====================================================

if ($varianId <= 0) {

    header("Location: ../menu/index.php");
    exit;

}


// =====================================================
// AMBIL DATA VARIAN
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        menu_id,
        nama_varian,
        status
    FROM varian_menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $varianId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: ../menu/index.php");
    exit;

}


$varian = $result->fetch_assoc();

$stmt->close();


// =====================================================
// TENTUKAN STATUS BARU
// =====================================================

if ($varian['status'] === 'tersedia') {

    $statusBaru = 'tidak_tersedia';

} else {

    $statusBaru = 'tersedia';

}


// =====================================================
// UPDATE STATUS
// =====================================================

$stmt = $conn->prepare("
    UPDATE varian_menu
    SET
        status = ?,
        updated_at = NOW()
    WHERE id = ?
");

$stmt->bind_param(
    "si",
    $statusBaru,
    $varianId
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?menu_id="
        . intval($varian['menu_id'])
    );

    exit;

}


// =====================================================
// JIKA GAGAL
// =====================================================

$stmt->close();

header(
    "Location: index.php?menu_id="
    . intval($varian['menu_id'])
);

exit;

?>