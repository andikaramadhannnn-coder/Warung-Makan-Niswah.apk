<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL MENU ID
// =====================================================

$menuId = intval($_GET['menu_id'] ?? $_POST['menu_id'] ?? 0);

if ($menuId <= 0) {
    header("Location: index.php");
    exit;
}


// =====================================================
// AMBIL DATA MENU
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        nama_menu,
        harga,
        foto,
        status
    FROM menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $menuId);
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
// DEFAULT
// =====================================================

$error = '';

$namaKelompok = '';
$tipePilihan = 'single';
$wajib = 1;
$minPilih = 1;
$maxPilih = 1;
$urutan = 1;
$status = 'tersedia';


// =====================================================
// PROSES TAMBAH
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaKelompok = trim(
        $_POST['nama_kelompok'] ?? ''
    );

    $tipePilihan = $_POST['tipe_pilihan'] ?? 'single';

    $wajib = isset(
        $_POST['wajib']
    ) ? 1 : 0;

    $minPilih = intval(
        $_POST['min_pilih'] ?? 0
    );

    $maxPilih = intval(
        $_POST['max_pilih'] ?? 0
    );

    $urutan = intval(
        $_POST['urutan'] ?? 1
    );

    $status = $_POST['status'] ?? 'tersedia';


    // =================================================
    // VALIDASI
    // =================================================

    if ($namaKelompok === '') {

        $error =
            'Nama kelompok wajib diisi.';

    } elseif (
        !in_array(
            $tipePilihan,
            ['single', 'multiple'],
            true
        )
    ) {

        $error =
            'Tipe pilihan tidak valid.';

    } elseif (
        !in_array(
            $status,
            ['tersedia', 'tidak_tersedia'],
            true
        )
    ) {

        $error =
            'Status tidak valid.';

    } elseif (
        $urutan < 1
    ) {

        $error =
            'Urutan minimal 1.';

    } else {


        // =============================================
        // ATUR MIN / MAX
        // =============================================

        if ($tipePilihan === 'single') {

            /*
             * Single hanya boleh memilih satu.
             */

            $minPilih = $wajib ? 1 : 0;
            $maxPilih = 1;

        } else {

            /*
             * Multiple.
             */

            if ($minPilih < 0) {
                $minPilih = 0;
            }

            if ($maxPilih < 1) {
                $maxPilih = 1;
            }

            if ($minPilih > $maxPilih) {

                $error =
                    'Minimal pilih tidak boleh lebih besar dari maksimal pilih.';

            }

        }


        // =============================================
        // SIMPAN
        // =============================================

        if ($error === '') {

            $stmt = $conn->prepare("
                INSERT INTO kelompok_pilihan (
                    menu_id,
                    nama_kelompok,
                    tipe_pilihan,
                    wajib,
                    min_pilih,
                    max_pilih,
                    urutan,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isiiiiis",
                $menuId,
                $namaKelompok,
                $tipePilihan,
                $wajib,
                $minPilih,
                $maxPilih,
                $urutan,
                $status
            );


            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: kelompok.php?menu_id=" .
                    $menuId
                );

                exit;

            } else {

                $error =
                    'Gagal menyimpan kelompok: ' .
                    $stmt->error;

                $stmt->close();

            }

        }

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
        Tambah Kelompok |
        <?= htmlspecialchars(
            $menu['nama_menu']
        ); ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <style>

        /* =====================================================
           HEADER
        ===================================================== */

        .form-page-header {

            margin-bottom:
                25px;

        }


        .back-link {

            display:
                inline-block;

            margin-bottom:
                15px;

            color:
                #e87518;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                700;

        }


        .form-page-header h1 {

            margin:
                0 0 7px;

        }


        .form-page-header p {

            margin:
                0;

            color:
                #777;

        }


        /* =====================================================
           FORM CARD
        ===================================================== */

        .choice-form-card {

            background:
                #fff;

            border:
                1px solid #eee;

            border-radius:
                18px;

            padding:
                28px;

            max-width:
                900px;

        }


        /* =====================================================
           MENU INFO
        ===================================================== */

        .menu-info {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;

            background:
                #fff8ef;

            border:
                1px solid #f4dfca;

            border-radius:
                14px;

            padding:
                15px;

            margin-bottom:
                25px;

        }


        .menu-info-image {

            width:
                55px;

            height:
                55px;

            border-radius:
                11px;

            overflow:
                hidden;

            background:
                #f7eee5;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                25px;

            flex-shrink:
                0;

        }


        .menu-info-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .menu-info strong {

            display:
                block;

            margin-bottom:
                4px;

        }


        .menu-info span {

            color:
                #777;

            font-size:
                12px;

        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-box {

            background:
                #ffe8e8;

            color:
                #c62828;

            border:
                1px solid #ffcaca;

            padding:
                13px 15px;

            border-radius:
                10px;

            margin-bottom:
                20px;

            font-size:
                13px;

        }


        /* =====================================================
           FORM GRID
        ===================================================== */

        .form-grid {

            display:
                grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(
                        0,
                        1fr
                    )
                );

            gap:
                20px;

        }


        .form-group {

            margin-bottom:
                20px;

        }


        .form-group.full {

            grid-column:
                1 / -1;

        }


        .form-group label {

            display:
                block;

            margin-bottom:
                8px;

            font-size:
                13px;

            font-weight:
                800;

        }


        .required {

            color:
                #e87518;

        }


        .form-control {

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                13px 14px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            font-size:
                14px;

            outline:
                none;

            background:
                #fff;

        }


        .form-control:focus {

            border-color:
                #e87518;

            box-shadow:
                0 0 0 3px
                rgba(
                    232,
                    117,
                    24,
                    .10
                );

        }


        .form-help {

            display:
                block;

            margin-top:
                6px;

            color:
                #888;

            font-size:
                11px;

        }


        /* =====================================================
           CHECKBOX
        ===================================================== */

        .checkbox-box {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            padding:
                14px;

            background:
                #fafafa;

            border:
                1px solid #eee;

            border-radius:
                10px;

        }


        .checkbox-box input {

            width:
                17px;

            height:
                17px;

            accent-color:
                #e87518;

        }


        .checkbox-box label {

            margin:
                0;

            cursor:
                pointer;

        }


        /* =====================================================
           MULTIPLE SETTINGS
        ===================================================== */

        .multiple-settings {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                15px;

        }


        /* =====================================================
           ACTION
        ===================================================== */

        .form-actions {

            display:
                flex;

            justify-content:
                flex-end;

            gap:
                10px;

            border-top:
                1px solid #eee;

            padding-top:
                22px;

            margin-top:
                5px;

        }


        .btn-cancel {

            padding:
                12px 18px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            background:
                #fff;

            color:
                #555;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                700;

        }


        .btn-save {

            padding:
                12px 20px;

            border:
                none;

            border-radius:
                10px;

            background:
                #e87518;

            color:
                white;

            font-size:
                13px;

            font-weight:
                800;

            cursor:
                pointer;

        }


        .btn-save:hover {

            background:
                #d76510;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (
            max-width: 700px
        ) {

            .form-grid {

                grid-template-columns:
                    1fr;

            }


            .form-group.full {

                grid-column:
                    auto;

            }


            .multiple-settings {

                grid-template-columns:
                    1fr;

            }


            .choice-form-card {

                padding:
                    20px;

            }


            .form-actions {

                flex-direction:
                    column-reverse;

            }


            .btn-cancel,
            .btn-save {

                width:
                    100%;

                box-sizing:
                    border-box;

                text-align:
                    center;

            }

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =====================================================
         SIDEBAR
    ===================================================== -->

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

                <span>
                    Dashboard
                </span>

            </a>


            <a href="../menu/index.php">

                <span>🍛</span>

                <span>
                    Menu Makanan
                </span>

            </a>


            <a href="../kategori/index.php">

                <span>📂</span>

                <span>
                    Kategori
                </span>

            </a>


            <a href="../pesanan/index.php">

                <span>🛒</span>

                <span>
                    Pesanan
                </span>

            </a>


            <a href="../pelanggan/index.php">

                <span>👥</span>

                <span>
                    Pelanggan
                </span>

            </a>


            <a href="../pembayaran/index.php">

                <span>💰</span>

                <span>
                    Pembayaran
                </span>

            </a>


            <a href="../laporan/index.php">

                <span>📈</span>

                <span>
                    Laporan
                </span>

            </a>


            <a
                href="index.php"
                class="active"
            >

                <span>⚙️</span>

                <span>
                    Pilihan Menu
                </span>

            </a>


        </nav>


        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="logout"
            >

                <span>
                    🚪
                </span>

                <span>
                    Keluar
                </span>

            </a>

        </div>


    </aside>



    <!-- =====================================================
         MAIN
    ===================================================== -->

    <main class="main-content">


        <div class="form-page-header">


            <a
                href="kelompok.php?menu_id=<?= $menuId; ?>"
                class="back-link"
            >

                ← Kembali ke
                <?= htmlspecialchars(
                    $menu['nama_menu']
                ); ?>

            </a>


            <h1>
                Tambah Kelompok Pilihan
            </h1>


            <p>
                Buat aturan pilihan yang akan
                muncul kepada pelanggan.
            </p>


        </div>



        <div
            class="choice-form-card"
        >


            <!-- =================================================
                 MENU INFO
            ================================================== -->

            <div
                class="menu-info"
            >


                <div
                    class="menu-info-image"
                >

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

                    <strong>
                        <?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>
                    </strong>

                    <span>

                        Harga dasar:
                        Rp<?= number_format(
                            floatval(
                                $menu['harga']
                            ),
                            0,
                            ',',
                            '.'
                        ); ?>

                    </span>

                </div>


            </div>



            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if (
                $error !== ''
            ): ?>

                <div
                    class="error-box"
                >

                    ⚠️
                    <?= htmlspecialchars(
                        $error
                    ); ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
            >


                <input
                    type="hidden"
                    name="menu_id"
                    value="<?= $menuId; ?>"
                >



                <div
                    class="form-grid"
                >


                    <!-- =========================================
                         NAMA KELOMPOK
                    ========================================== -->

                    <div
                        class="form-group full"
                    >

                        <label>
                            Nama Kelompok
                            <span class="required">
                                *
                            </span>
                        </label>


                        <input
                            type="text"
                            name="nama_kelompok"
                            class="form-control"
                            placeholder="Contoh: Bagian Ayam"
                            value="<?= htmlspecialchars(
                                $namaKelompok
                            ); ?>"
                            required
                        >


                        <small
                            class="form-help"
                        >

                            Contoh:
                            Bagian Ayam, Sambal,
                            Tambahan, Ukuran,
                            Level Pedas.

                        </small>

                    </div>



                    <!-- =========================================
                         TIPE
                    ========================================== -->

                    <div
                        class="form-group"
                    >

                        <label>
                            Tipe Pilihan
                            <span class="required">
                                *
                            </span>
                        </label>


                        <select
                            name="tipe_pilihan"
                            id="tipePilihan"
                            class="form-control"
                        >


                            <option
                                value="single"
                                <?= $tipePilihan === 'single'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Single
                                — pilih satu

                            </option>


                            <option
                                value="multiple"
                                <?= $tipePilihan === 'multiple'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Multiple
                                — bisa pilih banyak

                            </option>


                        </select>


                        <small
                            class="form-help"
                        >

                            Single cocok untuk
                            Bagian Ayam atau Sambal.

                            Multiple cocok untuk
                            Tambahan.

                        </small>

                    </div>



                    <!-- =========================================
                         STATUS
                    ========================================== -->

                    <div
                        class="form-group"
                    >

                        <label>
                            Status
                        </label>


                        <select
                            name="status"
                            class="form-control"
                        >


                            <option
                                value="tersedia"
                                <?= $status === 'tersedia'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Tersedia

                            </option>


                            <option
                                value="tidak_tersedia"
                                <?= $status === 'tidak_tersedia'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Tidak Tersedia

                            </option>


                        </select>

                    </div>



                    <!-- =========================================
                         WAJIB
                    ========================================== -->

                    <div
                        class="form-group"
                    >

                        <label>
                            Aturan Pilihan
                        </label>


                        <div
                            class="checkbox-box"
                        >

                            <input
                                type="checkbox"
                                id="wajib"
                                name="wajib"
                                value="1"
                                <?= $wajib
                                    ? 'checked'
                                    : ''; ?>
                            >


                            <label
                                for="wajib"
                            >

                                Pilihan wajib diisi
                                pelanggan

                            </label>

                        </div>

                    </div>



                    <!-- =========================================
                         URUTAN
                    ========================================== -->

                    <div
                        class="form-group"
                    >

                        <label>
                            Urutan
                        </label>


                        <input
                            type="number"
                            name="urutan"
                            class="form-control"
                            min="1"
                            value="<?= $urutan; ?>"
                        >


                        <small
                            class="form-help"
                        >

                            Urutan menentukan
                            posisi kelompok di halaman menu.

                        </small>

                    </div>



                    <!-- =========================================
                         MIN / MAX
                    ========================================== -->

                    <div
                        class="form-group full"
                        id="multipleSettings"
                    >

                        <label>
                            Jumlah Pilihan
                        </label>


                        <div
                            class="multiple-settings"
                        >


                            <div>

                                <input
                                    type="number"
                                    name="min_pilih"
                                    id="minPilih"
                                    class="form-control"
                                    min="0"
                                    value="<?= $minPilih; ?>"
                                >


                                <small
                                    class="form-help"
                                >

                                    Minimal pilihan

                                </small>

                            </div>


                            <div>

                                <input
                                    type="number"
                                    name="max_pilih"
                                    id="maxPilih"
                                    class="form-control"
                                    min="1"
                                    value="<?= $maxPilih; ?>"
                                >


                                <small
                                    class="form-help"
                                >

                                    Maksimal pilihan

                                </small>

                            </div>


                        </div>

                    </div>


                </div>



                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div
                    class="form-actions"
                >


                    <a
                        href="kelompok.php?menu_id=<?= $menuId; ?>"
                        class="btn-cancel"
                    >

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="btn-save"
                    >

                        💾 Simpan Kelompok

                    </button>


                </div>


            </form>


        </div>


    </main>


</div>



<script>


// =====================================================
// ATUR TAMPILAN MIN / MAX
// =====================================================

const tipePilihan =
    document.getElementById(
        'tipePilihan'
    );

const wajib =
    document.getElementById(
        'wajib'
    );

const minPilih =
    document.getElementById(
        'minPilih'
    );

const maxPilih =
    document.getElementById(
        'maxPilih'
    );


// =====================================================
// UPDATE FORM
// =====================================================

function updatePilihanForm() {

    if (
        tipePilihan.value ===
        'single'
    ) {

        minPilih.value =
            wajib.checked
                ? 1
                : 0;

        maxPilih.value = 1;

        minPilih.disabled = true;

        maxPilih.disabled = true;

    } else {

        minPilih.disabled = false;

        maxPilih.disabled = false;

    }

}


// =====================================================
// EVENT
// =====================================================

tipePilihan.addEventListener(
    'change',
    updatePilihanForm
);


wajib.addEventListener(
    'change',
    updatePilihanForm
);


// Jalankan pertama kali

updatePilihanForm();

</script>


</body>

</html>