<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID PILIHAN
// =====================================================

$id = intval(
    $_GET['id'] ?? 0
);

if ($id <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA PILIHAN
// =====================================================

$stmt = $conn->prepare("
    SELECT
        pm.id,
        pm.menu_id,
        pm.kelompok_id,
        pm.nama_pilihan,
        pm.status,

        kp.nama_kelompok

    FROM pilihan_menu pm

    LEFT JOIN kelompok_pilihan kp
        ON kp.id = pm.kelompok_id

    WHERE pm.id = ?

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


$pilihan =
    $result->fetch_assoc();

$stmt->close();


// =====================================================
// TOGGLE STATUS
// =====================================================

if (
    $pilihan['status']
    === 'tersedia'
) {

    $statusBaru =
        'tidak_tersedia';

} else {

    $statusBaru =
        'tersedia';

}


// =====================================================
// UPDATE STATUS
// =====================================================

$stmt = $conn->prepare("
    UPDATE pilihan_menu
    SET status = ?
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $statusBaru,
    $id
);


if (
    $stmt->execute()
) {

    $stmt->close();


    // =============================================
    // KEMBALI KE KELOMPOK
    // =============================================

    header(
        "Location: kelompok.php?menu_id="
        . intval(
            $pilihan['menu_id']
        )
    );

    exit;

}


$stmt->close();


// =====================================================
// JIKA GAGAL
// =====================================================

header(
    "Location: kelompok.php?menu_id="
    . intval(
        $pilihan['menu_id']
    )
);

exit;

?>