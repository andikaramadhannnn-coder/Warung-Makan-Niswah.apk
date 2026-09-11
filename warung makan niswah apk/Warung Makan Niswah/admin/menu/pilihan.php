<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// MENU ID
// =====================================================

$menu_id = intval($_GET['id'] ?? 0);

if ($menu_id <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA MENU
// =====================================================

$stmt = $conn->prepare("
    SELECT *
    FROM menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $menu_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}

$menu = $result->fetch_assoc();

$stmt->close();


// =====================================================
// VARIABEL
// =====================================================

$error = "";

$success = "";


// =====================================================
// FUNGSI REDIRECT
// =====================================================

function kembaliPilihan($menu_id)
{

    header(
        "Location: pilihan.php?id=" .
        intval($menu_id)
    );

    exit;

}


// =====================================================
// PROSES POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $action =
        $_POST['action'] ?? "";


    // =================================================
    // TAMBAH KELOMPOK
    // =================================================

    if ($action === 'tambah_kelompok') {


        $namaKelompok =
            trim(
                $_POST['nama_kelompok'] ?? ''
            );


        $tipeKelompok =
            $_POST['tipe_pilihan'] ?? 'single';


        $wajib =
            isset(
                $_POST['wajib']
            )
            ? 1
            : 0;


        $minPilih =
            intval(
                $_POST['min_pilih'] ?? 0
            );


        $maxPilih =
            intval(
                $_POST['max_pilih'] ?? 1
            );


        $urutan =
            intval(
                $_POST['urutan'] ?? 1
            );


        $status =
            $_POST['status'] ?? 'tersedia';


        $tipeValid = [

            'single',

            'multiple'

        ];


        $statusValid = [

            'tersedia',

            'tidak_tersedia'

        ];


        if (
            $namaKelompok === ''
        ) {

            $error =
                "Nama kelompok wajib diisi.";

        }

        elseif (
            !in_array(
                $tipeKelompok,
                $tipeValid,
                true
            )
        ) {

            $error =
                "Tipe pilihan tidak valid.";

        }

        elseif (
            !in_array(
                $status,
                $statusValid,
                true
            )
        ) {

            $error =
                "Status tidak valid.";

        }

        else {


            if (
                $tipeKelompok === 'single'
            ) {

                $minPilih =
                    $wajib
                    ? 1
                    : 0;

                $maxPilih = 1;

            }

            else {

                if (
                    $minPilih < 0
                ) {

                    $minPilih = 0;

                }

                if (
                    $maxPilih < 1
                ) {

                    $maxPilih = 1;

                }

                if (
                    $minPilih > $maxPilih
                ) {

                    $minPilih =
                        $maxPilih;

                }

            }


            if (
                $urutan < 1
            ) {

                $urutan = 1;

            }


            $stmt =
                $conn->prepare("

                    INSERT INTO kelompok_pilihan
                    (
                        menu_id,
                        nama_kelompok,
                        tipe_pilihan,
                        wajib,
                        min_pilih,
                        max_pilih,
                        urutan,
                        status
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )

                ");


            $stmt->bind_param(

                "issiiiis",

                $menu_id,

                $namaKelompok,

                $tipeKelompok,

                $wajib,

                $minPilih,

                $maxPilih,

                $urutan,

                $status

            );


            if (
                $stmt->execute()
            ) {

                $stmt->close();

                kembaliPilihan(
                    $menu_id
                );

            }


            $error =
                "Kelompok gagal ditambahkan.";

            $stmt->close();

        }

    }



    // =================================================
    // EDIT KELOMPOK
    // =================================================

    elseif (
        $action === 'edit_kelompok'
    ) {


        $kelompokId =
            intval(
                $_POST['kelompok_id'] ?? 0
            );


        $namaKelompok =
            trim(
                $_POST['nama_kelompok'] ?? ''
            );


        $tipeKelompok =
            $_POST['tipe_pilihan'] ?? 'single';


        $wajib =
            isset(
                $_POST['wajib']
            )
            ? 1
            : 0;


        $minPilih =
            intval(
                $_POST['min_pilih'] ?? 0
            );


        $maxPilih =
            intval(
                $_POST['max_pilih'] ?? 1
            );


        $urutan =
            intval(
                $_POST['urutan'] ?? 1
            );


        $status =
            $_POST['status'] ?? 'tersedia';


        $tipeValid = [

            'single',

            'multiple'

        ];


        if (
            $kelompokId <= 0
        ) {

            $error =
                "Kelompok tidak valid.";

        }

        elseif (
            $namaKelompok === ''
        ) {

            $error =
                "Nama kelompok wajib diisi.";

        }

        elseif (
            !in_array(
                $tipeKelompok,
                $tipeValid,
                true
            )
        ) {

            $error =
                "Tipe pilihan tidak valid.";

        }

        else {


            if (
                $tipeKelompok === 'single'
            ) {

                $minPilih =
                    $wajib
                    ? 1
                    : 0;

                $maxPilih = 1;

            }

            else {

                if (
                    $minPilih < 0
                ) {

                    $minPilih = 0;

                }

                if (
                    $maxPilih < 1
                ) {

                    $maxPilih = 1;

                }

                if (
                    $minPilih > $maxPilih
                ) {

                    $minPilih =
                        $maxPilih;

                }

            }


            if (
                $urutan < 1
            ) {

                $urutan = 1;

            }


            $stmt =
                $conn->prepare("

                    UPDATE kelompok_pilihan

                    SET

                        nama_kelompok = ?,

                        tipe_pilihan = ?,

                        wajib = ?,

                        min_pilih = ?,

                        max_pilih = ?,

                        urutan = ?,

                        status = ?,

                        updated_at = NOW()

                    WHERE id = ?

                      AND menu_id = ?

                ");


            $stmt->bind_param(

                "ssiiiisii",

                $namaKelompok,

                $tipeKelompok,

                $wajib,

                $minPilih,

                $maxPilih,

                $urutan,

                $status,

                $kelompokId,

                $menu_id

            );


            if (
                $stmt->execute()
            ) {

                $stmt->close();

                kembaliPilihan(
                    $menu_id
                );

            }


            $error =
                "Kelompok gagal diperbarui.";

            $stmt->close();

        }

    }



    // =================================================
    // HAPUS KELOMPOK
    // =================================================

    elseif (
        $action === 'hapus_kelompok'
    ) {


        $kelompokId =
            intval(
                $_POST['kelompok_id'] ?? 0
            );


        if (
            $kelompokId > 0
        ) {


            $stmt =
                $conn->prepare("

                    DELETE FROM pilihan_menu

                    WHERE kelompok_id = ?

                      AND menu_id = ?

                ");


            $stmt->bind_param(

                "ii",

                $kelompokId,

                $menu_id

            );


            $stmt->execute();

            $stmt->close();


            $stmt =
                $conn->prepare("

                    DELETE FROM kelompok_pilihan

                    WHERE id = ?

                      AND menu_id = ?

                ");


            $stmt->bind_param(

                "ii",

                $kelompokId,

                $menu_id

            );


            $stmt->execute();

            $stmt->close();

        }


        kembaliPilihan(
            $menu_id
        );

    }



    // =================================================
    // TAMBAH PILIHAN
    // =================================================

    elseif (
        $action === 'tambah_pilihan'
    ) {


        $kelompokId =
            intval(
                $_POST['kelompok_id'] ?? 0
            );


        $namaPilihan =
            trim(
                $_POST['nama_pilihan'] ?? ''
            );


        $harga =
            floatval(
                $_POST['harga_tambahan'] ?? 0
            );


        if (
            $kelompokId <= 0
        ) {

            $error =
                "Pilih kelompok terlebih dahulu.";

        }

        elseif (
            $namaPilihan === ''
        ) {

            $error =
                "Nama pilihan wajib diisi.";

        }

        elseif (
            $harga < 0
        ) {

            $error =
                "Harga tambahan tidak boleh negatif.";

        }

        else {


            // Pastikan kelompok memang milik menu

            $stmt =
                $conn->prepare("

                    SELECT
                        id,
                        tipe_pilihan

                    FROM kelompok_pilihan

                    WHERE id = ?

                      AND menu_id = ?

                    LIMIT 1

                ");


            $stmt->bind_param(

                "ii",

                $kelompokId,

                $menu_id

            );


            $stmt->execute();


            $groupResult =
                $stmt->get_result();


            $group =
                $groupResult->fetch_assoc();


            $stmt->close();


            if (!$group) {

                $error =
                    "Kelompok pilihan tidak ditemukan.";

            }

            else {


                // Tipe pilihan mengikuti kelompok

                $tipePilihan =
                    $group[
                        'tipe_pilihan'
                    ];


                $stmt =
                    $conn->prepare("

                        INSERT INTO pilihan_menu
                        (
                            menu_id,
                            kelompok_id,
                            tipe_pilihan,
                            nama_pilihan,
                            harga_tambahan,
                            status
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            'tersedia'
                        )

                    ");


                $stmt->bind_param(

                    "iissd",

                    $menu_id,

                    $kelompokId,

                    $tipePilihan,

                    $namaPilihan,

                    $harga

                );


                if (
                    $stmt->execute()
                ) {

                    $stmt->close();

                    kembaliPilihan(
                        $menu_id
                    );

                }


                $error =
                    "Pilihan gagal ditambahkan.";

                $stmt->close();

            }

        }

    }



    // =================================================
    // EDIT PILIHAN
    // =================================================

    elseif (
        $action === 'edit_pilihan'
    ) {


        $pilihanId =
            intval(
                $_POST['pilihan_id'] ?? 0
            );


        $kelompokId =
            intval(
                $_POST['kelompok_id'] ?? 0
            );


        $namaPilihan =
            trim(
                $_POST['nama_pilihan'] ?? ''
            );


        $harga =
            floatval(
                $_POST['harga_tambahan'] ?? 0
            );


        if (
            $pilihanId <= 0
            ||
            $kelompokId <= 0
        ) {

            $error =
                "Data pilihan tidak valid.";

        }

        elseif (
            $namaPilihan === ''
        ) {

            $error =
                "Nama pilihan wajib diisi.";

        }

        elseif (
            $harga < 0
        ) {

            $error =
                "Harga tambahan tidak boleh negatif.";

        }

        else {


            $stmt =
                $conn->prepare("

                    SELECT tipe_pilihan

                    FROM kelompok_pilihan

                    WHERE id = ?

                      AND menu_id = ?

                    LIMIT 1

                ");


            $stmt->bind_param(

                "ii",

                $kelompokId,

                $menu_id

            );


            $stmt->execute();


            $groupResult =
                $stmt->get_result();


            $group =
                $groupResult->fetch_assoc();


            $stmt->close();


            if (!$group) {

                $error =
                    "Kelompok tidak valid.";

            }

            else {


                $tipePilihan =
                    $group[
                        'tipe_pilihan'
                    ];


                $stmt =
                    $conn->prepare("

                        UPDATE pilihan_menu

                        SET

                            kelompok_id = ?,

                            tipe_pilihan = ?,

                            nama_pilihan = ?,

                            harga_tambahan = ?

                        WHERE id = ?

                          AND menu_id = ?

                    ");


                $stmt->bind_param(

                    "issdii",

                    $kelompokId,

                    $tipePilihan,

                    $namaPilihan,

                    $harga,

                    $pilihanId,

                    $menu_id

                );


                if (
                    $stmt->execute()
                ) {

                    $stmt->close();

                    kembaliPilihan(
                        $menu_id
                    );

                }


                $error =
                    "Pilihan gagal diperbarui.";

                $stmt->close();

            }

        }

    }



    // =================================================
    // STATUS PILIHAN
    // =================================================

    elseif (
        $action === 'status_pilihan'
    ) {


        $pilihanId =
            intval(
                $_POST['pilihan_id'] ?? 0
            );


        $status =
            $_POST['status'] ?? 'tersedia';


        if (
            $pilihanId > 0
            &&
            in_array(
                $status,
                [
                    'tersedia',
                    'tidak_tersedia'
                ],
                true
            )
        ) {


            $stmt =
                $conn->prepare("

                    UPDATE pilihan_menu

                    SET status = ?

                    WHERE id = ?

                      AND menu_id = ?

                ");


            $stmt->bind_param(

                "sii",

                $status,

                $pilihanId,

                $menu_id

            );


            $stmt->execute();

            $stmt->close();

        }


        kembaliPilihan(
            $menu_id
        );

    }



    // =================================================
    // HAPUS PILIHAN
    // =================================================

    elseif (
        $action === 'hapus_pilihan'
    ) {


        $pilihanId =
            intval(
                $_POST['pilihan_id'] ?? 0
            );


        if (
            $pilihanId > 0
        ) {


            $stmt =
                $conn->prepare("

                    DELETE FROM pilihan_menu

                    WHERE id = ?

                      AND menu_id = ?

                ");


            $stmt->bind_param(

                "ii",

                $pilihanId,

                $menu_id

            );


            $stmt->execute();

            $stmt->close();

        }


        kembaliPilihan(
            $menu_id
        );

    }

}


// =====================================================
// AMBIL KELOMPOK
// =====================================================

$kelompokQuery = $conn->prepare("

    SELECT *

    FROM kelompok_pilihan

    WHERE menu_id = ?

    ORDER BY urutan ASC, id ASC

");

$kelompokQuery->bind_param(
    "i",
    $menu_id
);

$kelompokQuery->execute();

$kelompokResult =
    $kelompokQuery->get_result();

$kelompok = [];

while (
    $row =
    $kelompokResult->fetch_assoc()
) {

    $kelompok[] = $row;

}

$kelompokQuery->close();


// =====================================================
// AMBIL PILIHAN
// =====================================================

$pilihanQuery = $conn->prepare("

    SELECT
        pm.*,
        kp.nama_kelompok,
        kp.tipe_pilihan AS tipe_kelompok

    FROM pilihan_menu pm

    LEFT JOIN kelompok_pilihan kp
        ON pm.kelompok_id = kp.id

    WHERE pm.menu_id = ?

    ORDER BY
        COALESCE(kp.urutan, 999),
        pm.id

");

$pilihanQuery->bind_param(
    "i",
    $menu_id
);

$pilihanQuery->execute();

$pilihanResult =
    $pilihanQuery->get_result();

$pilihan = [];

while (
    $row =
    $pilihanResult->fetch_assoc()
) {

    $pilihan[] = $row;

}

$pilihanQuery->close();


// =====================================================
// GROUP PILIHAN
// =====================================================

$pilihanPerKelompok = [];

$tanpaKelompok = [];

foreach (
    $pilihan as $item
) {

    $gid =
        intval(
            $item['kelompok_id']
            ?? 0
        );


    if (
        $gid > 0
    ) {

        if (
            !isset(
                $pilihanPerKelompok[$gid]
            )
        ) {

            $pilihanPerKelompok[$gid] = [];

        }


        $pilihanPerKelompok[$gid][] =
            $item;

    }

    else {

        $tanpaKelompok[] =
            $item;

    }

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>

        Pilihan Menu |
        Warung Makan Niswah

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <style>


        /* =====================================================
           INFO MENU
        ===================================================== */

        .menu-info {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;

            margin-bottom:
                20px;

            padding:
                18px;

            background:
                #fff8ef;

            border:
                1px solid #ffe1c2;

            border-radius:
                15px;

        }


        .menu-info-image {

            width:
                65px;

            height:
                65px;

            border-radius:
                12px;

            overflow:
                hidden;

            background:
                #f5eadf;

            flex-shrink:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                30px;

        }


        .menu-info-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .menu-info h3 {

            margin:
                0;

        }


        .menu-info p {

            margin:
                5px 0 0;

            color:
                #888;

            font-size:
                12px;

        }



        /* =====================================================
           GROUP CARD
        ===================================================== */

        .group-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                16px;

            margin-bottom:
                15px;

            overflow:
                hidden;

        }


        .group-header {

            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                15px;

            padding:
                18px;

            border-bottom:
                1px solid #eee;

        }


        .group-header h3 {

            margin:
                0;

            font-size:
                16px;

        }


        .group-meta {

            display:
                flex;

            flex-wrap:
                wrap;

            gap:
                6px;

            margin-top:
                7px;

        }


        .group-badge {

            display:
                inline-block;

            padding:
                5px 8px;

            border-radius:
                8px;

            background:
                #f5f5f5;

            color:
                #666;

            font-size:
                10px;

            font-weight:
                700;

        }


        .group-actions {

            display:
                flex;

            gap:
                6px;

            flex-shrink:
                0;

        }


        .group-body {

            padding:
                15px;

        }



        /* =====================================================
           OPTION
        ===================================================== */

        .option-row {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            padding:
                12px;

            border:
                1px solid #eee;

            border-radius:
                11px;

            margin-bottom:
                8px;

        }


        .option-row:last-child {

            margin-bottom:
                0;

        }


        .option-name strong {

            display:
                block;

            font-size:
                13px;

        }


        .option-name span {

            display:
                block;

            margin-top:
                3px;

            color:
                #888;

            font-size:
                11px;

        }


        .option-actions {

            display:
                flex;

            gap:
                5px;

            flex-shrink:
                0;

        }


        .mini-button {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            width:
                34px;

            height:
                34px;

            border:
                0;

            border-radius:
                8px;

            cursor:
                pointer;

            text-decoration:
                none;

            font-size:
                14px;

        }


        .mini-edit {

            background:
                #fff0df;

        }


        .mini-delete {

            background:
                #ffe8e8;

        }


        .mini-status {

            background:
                #e9f8ef;

        }



        /* =====================================================
           FORM
        ===================================================== */

        .form-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                16px;

            padding:
                20px;

            margin-bottom:
                20px;

        }


        .form-card h3 {

            margin:
                0;

        }


        .form-help {

            color:
                #888;

            font-size:
                12px;

            margin:
                6px 0 18px;

        }


        .form-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                14px;

        }


        .form-group label {

            display:
                block;

            margin-bottom:
                6px;

            font-size:
                12px;

            font-weight:
                700;

        }


        .form-group input,

        .form-group select {

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                11px 12px;

            border:
                1px solid #ddd;

            border-radius:
                9px;

            font-size:
                13px;

        }


        .checkbox-line {

            display:
                flex;

            align-items:
                center;

            gap:
                8px;

            margin-top:
                25px;

            font-size:
                12px;

            font-weight:
                700;

        }


        .checkbox-line input {

            width:
                auto;

        }


        .form-actions {

            margin-top:
                15px;

            display:
                flex;

            justify-content:
                flex-end;

        }



        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-options {

            padding:
                25px;

            text-align:
                center;

            color:
                #999;

            font-size:
                12px;

        }



        /* =====================================================
           MODAL
        ===================================================== */

        .modal {

            display:
                none;

            position:
                fixed;

            inset:
                0;

            background:
                rgba(0,0,0,.45);

            z-index:
                9999;

            align-items:
                center;

            justify-content:
                center;

            padding:
                20px;

            box-sizing:
                border-box;

        }


        .modal.show {

            display:
                flex;

        }


        .modal-box {

            width:
                100%;

            max-width:
                520px;

            max-height:
                90vh;

            overflow:
                auto;

            background:
                white;

            border-radius:
                18px;

            padding:
                22px;

            box-sizing:
                border-box;

        }


        .modal-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            margin-bottom:
                18px;

        }


        .modal-header h3 {

            margin:
                0;

        }


        .modal-close {

            border:
                0;

            background:
                #f5f5f5;

            width:
                32px;

            height:
                32px;

            border-radius:
                8px;

            cursor:
                pointer;

        }


        @media (
            max-width: 700px
        ) {

            .form-grid {

                grid-template-columns:
                    1fr;

            }


            .group-header {

                flex-direction:
                    column;

            }


            .group-actions {

                width:
                    100%;

            }


            .option-row {

                align-items:
                    flex-start;

            }

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <div class="logo-icon">
                🍛
            </div>

            <div class="brand-text">

                <h2>
                    Niswah
                </h2>

                <span>
                    Admin Panel
                </span>

            </div>

        </div>


        <nav class="sidebar-menu">

            <a href="../dashboard.php">

                <span>📊</span>

                <span>Dashboard</span>

            </a>


            <a
                href="index.php"
                class="active"
            >

                <span>🍛</span>

                <span>Menu Makanan</span>

            </a>


            <a href="../kategori/index.php">

                <span>📂</span>

                <span>Kategori</span>

            </a>


            <a href="../pesanan/index.php">

                <span>🛒</span>

                <span>Pesanan</span>

            </a>


            <a href="../pelanggan/index.php">

                <span>👥</span>

                <span>Pelanggan</span>

            </a>


            <a href="../pembayaran/index.php">

                <span>💰</span>

                <span>Pembayaran</span>

            </a>


            <a href="../laporan/index.php">

                <span>📈</span>

                <span>Laporan</span>

            </a>

        </nav>


        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="logout"
            >

                <span>🚪</span>

                <span>Keluar</span>

            </a>

        </div>

    </aside>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="main-content">


        <!-- HEADER -->

        <header class="page-header">

            <div>

                <h1>
                    Pilihan Menu
                </h1>

                <p>
                    Atur pilihan untuk:
                    <strong>
                        <?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>
                    </strong>
                </p>

            </div>


            <a
                href="index.php"
                class="btn-secondary"
            >
                ← Kembali
            </a>

        </header>



        <!-- INFO MENU -->

        <div class="menu-info">

            <div class="menu-info-image">

                <?php if (
                    !empty(
                        $menu['foto']
                    )
                ): ?>

                    <img
                        src="../../assets/uploads/menu/<?= htmlspecialchars(
                            $menu['foto']
                        ); ?>"
                        alt="<?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>"
                    >

                <?php else: ?>

                    🍛

                <?php endif; ?>

            </div>


            <div>

                <h3>

                    <?= htmlspecialchars(
                        $menu['nama_menu']
                    ); ?>

                </h3>


                <p>

                    Rp<?= number_format(
                        $menu['harga'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </p>

            </div>

        </div>



        <!-- =====================================================
             TAMBAH KELOMPOK
        ====================================================== -->

        <div class="form-card">

            <h3>
                ⚙️ Tambah Kelompok Pilihan
            </h3>

            <p class="form-help">

                Kelompok menentukan aturan pilihan
                yang akan muncul kepada pelanggan.

            </p>


            <form method="POST">


                <input
                    type="hidden"
                    name="action"
                    value="tambah_kelompok"
                >


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Nama Kelompok *
                        </label>

                        <input
                            type="text"
                            name="nama_kelompok"
                            placeholder="Contoh: Bagian Ayam"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Tipe Pilihan *
                        </label>

                        <select
                            name="tipe_pilihan"
                            id="tipeKelompok"
                            onchange="ubahTipeKelompok()"
                        >

                            <option value="single">
                                Single
                            </option>

                            <option value="multiple">
                                Multiple
                            </option>

                        </select>

                    </div>



                    <div class="form-group">

                        <label>
                            Status
                        </label>

                        <select
                            name="status"
                        >

                            <option value="tersedia">
                                Tersedia
                            </option>

                            <option value="tidak_tersedia">
                                Tidak Tersedia
                            </option>

                        </select>

                    </div>



                    <div class="form-group">

                        <label>
                            Minimal Pilih
                        </label>

                        <input
                            type="number"
                            name="min_pilih"
                            id="minPilih"
                            value="1"
                            min="0"
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Maksimal Pilih
                        </label>

                        <input
                            type="number"
                            name="max_pilih"
                            id="maxPilih"
                            value="1"
                            min="1"
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Urutan
                        </label>

                        <input
                            type="number"
                            name="urutan"
                            value="<?= count($kelompok) + 1; ?>"
                            min="1"
                        >

                    </div>

                </div>


                <label class="checkbox-line">

                    <input
                        type="checkbox"
                        name="wajib"
                        id="wajib"
                        checked
                    >

                    Pilihan wajib diisi pelanggan

                </label>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-primary"
                    >

                        + Tambah Kelompok

                    </button>

                </div>


            </form>

        </div>



        <?php if ($error): ?>

            <div class="alert-error">

                <?= htmlspecialchars(
                    $error
                ); ?>

            </div>

        <?php endif; ?>



        <!-- =====================================================
             DAFTAR KELOMPOK
        ====================================================== -->

        <?php foreach (
            $kelompok as $group
        ): ?>


            <div class="group-card">


                <div class="group-header">


                    <div>

                        <h3>

                            <?php

                            if (
                                $group[
                                    'tipe_pilihan'
                                ] === 'single'
                            ) {

                                echo "🔘 ";

                            } else {

                                echo "☑️ ";

                            }

                            ?>

                            <?= htmlspecialchars(
                                $group[
                                    'nama_kelompok'
                                ]
                            ); ?>

                        </h3>


                        <div class="group-meta">


                            <span
                                class="group-badge"
                            >

                                <?= $group[
                                    'tipe_pilihan'
                                ] === 'single'
                                    ? 'Pilih 1'
                                    : 'Bisa Banyak'; ?>

                            </span>


                            <span
                                class="group-badge"
                            >

                                <?php if (
                                    $group['wajib']
                                ): ?>

                                    Wajib

                                <?php else: ?>

                                    Opsional

                                <?php endif; ?>

                            </span>


                            <span
                                class="group-badge"
                            >

                                Min:
                                <?= (int)
                                    $group[
                                        'min_pilih'
                                    ]; ?>

                                /

                                Max:
                                <?= (int)
                                    $group[
                                        'max_pilih'
                                    ]; ?>

                            </span>


                            <span
                                class="group-badge"
                            >

                                <?php if (
                                    $group[
                                        'status'
                                    ]
                                    === 'tersedia'
                                ): ?>

                                    🟢 Aktif

                                <?php else: ?>

                                    🔴 Nonaktif

                                <?php endif; ?>

                            </span>


                        </div>

                    </div>


                    <div
                        class="group-actions"
                    >


                        <button
                            type="button"
                            class="mini-button mini-edit"
                            onclick='bukaEditKelompok(
                                <?= json_encode(
                                    $group,
                                    JSON_HEX_TAG |
                                    JSON_HEX_APOS |
                                    JSON_HEX_QUOT |
                                    JSON_HEX_AMP
                                ); ?>
                            )'
                            title="Edit kelompok"
                        >
                            ✏️
                        </button>


                        <form
                            method="POST"
                            onsubmit="return confirm(
                                'Hapus kelompok ini beserta semua pilihannya?'
                            );"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="hapus_kelompok"
                            >

                            <input
                                type="hidden"
                                name="kelompok_id"
                                value="<?= (int)
                                    $group['id']; ?>"
                            >

                            <button
                                type="submit"
                                class="mini-button mini-delete"
                                title="Hapus kelompok"
                            >
                                🗑️
                            </button>

                        </form>


                    </div>


                </div>



                <div class="group-body">


                    <!-- TAMBAH PILIHAN -->

                    <form
                        method="POST"
                        style="margin-bottom:15px;"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="tambah_pilihan"
                        >

                        <input
                            type="hidden"
                            name="kelompok_id"
                            value="<?= (int)
                                $group['id']; ?>"
                        >


                        <div
                            style="
                                display:flex;
                                gap:8px;
                                flex-wrap:wrap;
                            "
                        >

                            <input
                                type="text"
                                name="nama_pilihan"
                                placeholder="Nama pilihan, contoh: Dada"
                                required
                                style="
                                    flex:1;
                                    min-width:180px;
                                    padding:10px;
                                    border:1px solid #ddd;
                                    border-radius:9px;
                                "
                            >


                            <input
                                type="number"
                                name="harga_tambahan"
                                value="0"
                                min="0"
                                placeholder="Harga"
                                style="
                                    width:130px;
                                    padding:10px;
                                    border:1px solid #ddd;
                                    border-radius:9px;
                                "
                            >


                            <button
                                type="submit"
                                class="btn-primary"
                            >
                                + Tambah
                            </button>

                        </div>

                    </form>



                    <!-- DAFTAR PILIHAN -->

                    <?php

                    $items =
                        $pilihanPerKelompok[
                            $group['id']
                        ]
                        ?? [];

                    ?>


                    <?php if (
                        count($items) > 0
                    ): ?>


                        <?php foreach (
                            $items as $item
                        ): ?>


                            <div
                                class="option-row"
                            >


                                <div
                                    class="option-name"
                                >

                                    <strong>

                                        <?= htmlspecialchars(
                                            $item[
                                                'nama_pilihan'
                                            ]
                                        ); ?>

                                    </strong>


                                    <span>

                                        <?php if (
                                            floatval(
                                                $item[
                                                    'harga_tambahan'
                                                ]
                                            ) > 0
                                        ): ?>

                                            +Rp<?= number_format(
                                                $item[
                                                    'harga_tambahan'
                                                ],
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        <?php else: ?>

                                            Gratis

                                        <?php endif; ?>


                                        ·


                                        <?php if (
                                            $item[
                                                'status'
                                            ]
                                            === 'tersedia'
                                        ): ?>

                                            🟢 Tersedia

                                        <?php else: ?>

                                            🔴 Nonaktif

                                        <?php endif; ?>

                                    </span>

                                </div>


                                <div
                                    class="option-actions"
                                >


                                    <button
                                        type="button"
                                        class="mini-button mini-edit"
                                        onclick='bukaEditPilihan(
                                            <?= json_encode(
                                                $item,
                                                JSON_HEX_TAG |
                                                JSON_HEX_APOS |
                                                JSON_HEX_QUOT |
                                                JSON_HEX_AMP
                                            ); ?>
                                        )'
                                    >
                                        ✏️
                                    </button>


                                    <form
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="status_pilihan"
                                        >

                                        <input
                                            type="hidden"
                                            name="pilihan_id"
                                            value="<?= (int)
                                                $item['id']; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="<?= $item['status']
                                                === 'tersedia'
                                                ? 'tidak_tersedia'
                                                : 'tersedia'; ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="mini-button mini-status"
                                            title="Ubah status"
                                        >

                                            <?= $item[
                                                'status'
                                            ] === 'tersedia'
                                                ? '🔴'
                                                : '🟢'; ?>

                                        </button>

                                    </form>


                                    <form
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Hapus pilihan ini?'
                                        );"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="hapus_pilihan"
                                        >

                                        <input
                                            type="hidden"
                                            name="pilihan_id"
                                            value="<?= (int)
                                                $item['id']; ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="mini-button mini-delete"
                                        >
                                            🗑️
                                        </button>

                                    </form>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div
                            class="empty-options"
                        >

                            Belum ada pilihan
                            di kelompok ini.

                        </div>


                    <?php endif; ?>


                </div>


            </div>


        <?php endforeach; ?>



        <!-- =====================================================
             PILIHAN TANPA KELOMPOK
        ====================================================== -->

        <?php if (
            count($tanpaKelompok) > 0
        ): ?>


            <div class="group-card">


                <div class="group-header">

                    <div>

                        <h3>
                            ⚠️ Pilihan Belum Dikelompokkan
                        </h3>

                        <div
                            class="group-meta"
                        >

                            <span
                                class="group-badge"
                            >

                                Pilihan lama

                            </span>

                        </div>

                    </div>

                </div>


                <div class="group-body">


                    <?php foreach (
                        $tanpaKelompok as $item
                    ): ?>


                        <div
                            class="option-row"
                        >

                            <div
                                class="option-name"
                            >

                                <strong>

                                    <?= htmlspecialchars(
                                        $item[
                                            'nama_pilihan'
                                        ]
                                    ); ?>

                                </strong>


                                <span>

                                    <?= htmlspecialchars(
                                        $item[
                                            'tipe_pilihan'
                                        ]
                                    ); ?>

                                </span>

                            </div>


                            <button
                                type="button"
                                class="mini-button mini-edit"
                                onclick='bukaEditPilihan(
                                    <?= json_encode(
                                        $item,
                                        JSON_HEX_TAG |
                                        JSON_HEX_APOS |
                                        JSON_HEX_QUOT |
                                        JSON_HEX_AMP
                                    ); ?>
                                )'
                            >

                                ✏️

                            </button>


                        </div>


                    <?php endforeach; ?>


                </div>


            </div>


        <?php endif; ?>


    </main>


</div>



<!-- =====================================================
     MODAL EDIT KELOMPOK
====================================================== -->

<div
    id="modalKelompok"
    class="modal"
>

    <div class="modal-box">


        <div class="modal-header">

            <h3>
                ✏️ Edit Kelompok
            </h3>


            <button
                type="button"
                class="modal-close"
                onclick="tutupModal('modalKelompok')"
            >
                ✕
            </button>

        </div>


        <form method="POST">


            <input
                type="hidden"
                name="action"
                value="edit_kelompok"
            >


            <input
                type="hidden"
                name="kelompok_id"
                id="editKelompokId"
            >


            <div class="form-group">

                <label>
                    Nama Kelompok
                </label>

                <input
                    type="text"
                    name="nama_kelompok"
                    id="editNamaKelompok"
                    required
                >

            </div>


            <br>


            <div class="form-group">

                <label>
                    Tipe
                </label>

                <select
                    name="tipe_pilihan"
                    id="editTipeKelompok"
                    onchange="ubahTipeEdit()"
                >

                    <option value="single">
                        Single
                    </option>

                    <option value="multiple">
                        Multiple
                    </option>

                </select>

            </div>


            <br>


            <div class="form-group">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    id="editStatusKelompok"
                >

                    <option value="tersedia">
                        Tersedia
                    </option>

                    <option value="tidak_tersedia">
                        Tidak Tersedia
                    </option>

                </select>

            </div>


            <br>


            <div class="form-grid">


                <div class="form-group">

                    <label>
                        Minimal
                    </label>

                    <input
                        type="number"
                        name="min_pilih"
                        id="editMinPilih"
                        min="0"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Maksimal
                    </label>

                    <input
                        type="number"
                        name="max_pilih"
                        id="editMaxPilih"
                        min="1"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Urutan
                    </label>

                    <input
                        type="number"
                        name="urutan"
                        id="editUrutan"
                        min="1"
                    >

                </div>


            </div>


            <label class="checkbox-line">

                <input
                    type="checkbox"
                    name="wajib"
                    id="editWajib"
                >

                Pilihan wajib

            </label>


            <div class="form-actions">

                <button
                    type="submit"
                    class="btn-primary"
                >

                    💾 Simpan Perubahan

                </button>

            </div>


        </form>


    </div>

</div>



<!-- =====================================================
     MODAL EDIT PILIHAN
====================================================== -->

<div
    id="modalPilihan"
    class="modal"
>

    <div class="modal-box">


        <div class="modal-header">

            <h3>
                ✏️ Edit Pilihan
            </h3>


            <button
                type="button"
                class="modal-close"
                onclick="tutupModal('modalPilihan')"
            >
                ✕
            </button>

        </div>


        <form method="POST">


            <input
                type="hidden"
                name="action"
                value="edit_pilihan"
            >


            <input
                type="hidden"
                name="pilihan_id"
                id="editPilihanId"
            >


            <div class="form-group">

                <label>
                    Kelompok
                </label>

                <select
                    name="kelompok_id"
                    id="editPilihanKelompok"
                    required
                >

                    <?php foreach (
                        $kelompok as $group
                    ): ?>

                        <option
                            value="<?= (int)
                                $group['id']; ?>"
                        >

                            <?= htmlspecialchars(
                                $group[
                                    'nama_kelompok'
                                ]
                            ); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <br>


            <div class="form-group">

                <label>
                    Nama Pilihan
                </label>

                <input
                    type="text"
                    name="nama_pilihan"
                    id="editNamaPilihan"
                    required
                >

            </div>


            <br>


            <div class="form-group">

                <label>
                    Harga Tambahan
                </label>

                <input
                    type="number"
                    name="harga_tambahan"
                    id="editHargaPilihan"
                    min="0"
                    required
                >

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="btn-primary"
                >

                    💾 Simpan Perubahan

                </button>

            </div>


        </form>


    </div>

</div>



<script>


// =====================================================
// TIPE KELOMPOK BARU
// =====================================================

function ubahTipeKelompok()
{

    const tipe =
        document.getElementById(
            'tipeKelompok'
        ).value;


    const min =
        document.getElementById(
            'minPilih'
        );


    const max =
        document.getElementById(
            'maxPilih'
        );


    const wajib =
        document.getElementById(
            'wajib'
        );


    if (
        tipe === 'single'
    ) {

        min.value =
            wajib.checked
            ? 1
            : 0;

        max.value =
            1;

        min.readOnly =
            true;

        max.readOnly =
            true;

    }

    else {

        min.readOnly =
            false;

        max.readOnly =
            false;

    }

}


// =====================================================
// WAJIB
// =====================================================

document
    .getElementById(
        'wajib'
    )
    .addEventListener(
        'change',
        function ()
        {

            const tipe =
                document.getElementById(
                    'tipeKelompok'
                ).value;


            if (
                tipe === 'single'
            ) {

                document.getElementById(
                    'minPilih'
                ).value =
                    this.checked
                    ? 1
                    : 0;

            }

        }
    );


// =====================================================
// EDIT KELOMPOK
// =====================================================

function bukaEditKelompok(
    data
)
{

    document.getElementById(
        'editKelompokId'
    ).value =
        data.id;


    document.getElementById(
        'editNamaKelompok'
    ).value =
        data.nama_kelompok;


    document.getElementById(
        'editTipeKelompok'
    ).value =
        data.tipe_pilihan;


    document.getElementById(
        'editStatusKelompok'
    ).value =
        data.status;


    document.getElementById(
        'editMinPilih'
    ).value =
        data.min_pilih;


    document.getElementById(
        'editMaxPilih'
    ).value =
        data.max_pilih;


    document.getElementById(
        'editUrutan'
    ).value =
        data.urutan;


    document.getElementById(
        'editWajib'
    ).checked =
        Number(
            data.wajib
        ) === 1;


    document
        .getElementById(
            'modalKelompok'
        )
        .classList
        .add(
            'show'
        );

}


// =====================================================
// EDIT TIPE KELOMPOK
// =====================================================

function ubahTipeEdit()
{

    const tipe =
        document.getElementById(
            'editTipeKelompok'
        ).value;


    const min =
        document.getElementById(
            'editMinPilih'
        );


    const max =
        document.getElementById(
            'editMaxPilih'
        );


    if (
        tipe === 'single'
    ) {

        max.value =
            1;

        max.readOnly =
            true;

    }

    else {

        max.readOnly =
            false;

    }

}


// =====================================================
// EDIT PILIHAN
// =====================================================

function bukaEditPilihan(
    data
)
{

    document.getElementById(
        'editPilihanId'
    ).value =
        data.id;


    document.getElementById(
        'editPilihanKelompok'
    ).value =
        data.kelompok_id || '';


    document.getElementById(
        'editNamaPilihan'
    ).value =
        data.nama_pilihan;


    document.getElementById(
        'editHargaPilihan'
    ).value =
        data.harga_tambahan;


    document
        .getElementById(
            'modalPilihan'
        )
        .classList
        .add(
            'show'
        );

}


// =====================================================
// TUTUP MODAL
// =====================================================

function tutupModal(
    id
)
{

    document
        .getElementById(
            id
        )
        .classList
        .remove(
            'show'
        );

}


// =====================================================
// KLIK LUAR MODAL
// =====================================================

document
    .querySelectorAll(
        '.modal'
    )
    .forEach(
        function(modal)
        {

            modal.addEventListener(
                'click',
                function(e)
                {

                    if (
                        e.target === modal
                    ) {

                        modal.classList
                            .remove(
                                'show'
                            );

                    }

                }
            );

        }
    );


// =====================================================
// JALANKAN DEFAULT
// =====================================================

ubahTipeKelompok();

</script>


</body>

</html>