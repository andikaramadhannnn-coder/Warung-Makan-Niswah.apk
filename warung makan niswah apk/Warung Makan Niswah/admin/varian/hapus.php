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
        nama_varian
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
// SIMPAN MENU ID
// =====================================================

$menuId = intval(
    $varian['menu_id']
);


// =====================================================
// HAPUS VARIAN
// =====================================================

$stmt = $conn->prepare("
    DELETE FROM varian_menu
    WHERE id = ?
      AND menu_id = ?
");

$stmt->bind_param(
    "ii",
    $varianId,
    $menuId
);


// =====================================================
// EKSEKUSI
// =====================================================

$stmt->execute();

$stmt->close();


// =====================================================
// KEMBALI KE DAFTAR VARIAN
// =====================================================

header(
    "Location: index.php?menu_id="
    . $menuId
);

exit;

?>