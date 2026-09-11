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

$menuIdUrl = intval(
    $_GET['menu_id'] ?? 0
);


if ($id <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA KELOMPOK
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        menu_id,
        nama_kelompok
    FROM kelompok_pilihan
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if (
    $result->num_rows !== 1
) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$kelompok =
    $result->fetch_assoc();

$stmt->close();


// =====================================================
// MENU ID ASLI
// =====================================================

$menuId =
    intval(
        $kelompok['menu_id']
    );


// =====================================================
// HAPUS PILIHAN DALAM KELOMPOK
// =====================================================

$stmt = $conn->prepare("
    DELETE FROM pilihan_menu
    WHERE kelompok_id = ?
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


// =====================================================
// HAPUS KELOMPOK
// =====================================================

$stmt = $conn->prepare("
    DELETE FROM kelompok_pilihan
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