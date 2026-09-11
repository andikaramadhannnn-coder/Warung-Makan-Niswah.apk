<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID
// =====================================================

$id = intval(
    $_GET['id'] ?? 0
);

$menuId = intval(
    $_GET['menu_id'] ?? 0
);

if ($id <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL MENU ID DARI DATABASE
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        menu_id
    FROM pilihan_menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$pilihan =
    $result->fetch_assoc();

$stmt->close();


// Pakai menu_id database supaya
// URL tidak bisa mengarahkan
// ke menu yang salah.

$menuId =
    intval(
        $pilihan['menu_id']
    );


// =====================================================
// HAPUS PILIHAN
// =====================================================

$stmt = $conn->prepare("
    DELETE FROM pilihan_menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();

$stmt->close();


// =====================================================
// KEMBALI
// =====================================================

header(
    "Location: kelompok.php?menu_id="
    . $menuId
);

exit;

?>