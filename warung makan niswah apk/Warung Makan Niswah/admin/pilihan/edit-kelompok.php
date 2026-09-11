<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID
// =====================================================

$id = intval(
    $_GET['id'] ?? $_POST['id'] ?? 0
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
        kp.*,
        m.nama_menu,
        m.harga AS harga_menu,
        m.foto
    FROM kelompok_pilihan kp
    INNER JOIN menu m
        ON m.id = kp.menu_id
    WHERE kp.id = ?
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

$kelompok = $result->fetch_assoc();

$stmt->close();

$menuId = intval(
    $kelompok['menu_id']
);


// =====================================================
// DEFAULT FORM
// =====================================================

$error = '';

$namaKelompok =
    $kelompok['nama_kelompok'];

$tipePilihan =
    $kelompok['tipe_pilihan'];

$wajib =
    intval(
        $kelompok['wajib']
    );

$minPilih =
    intval(
        $kelompok['min_pilih']
    );

$maxPilih =
    intval(
        $kelompok['max_pilih']
    );

$urutan =
    intval(
        $kelompok['urutan']
    );

$status =
    $kelompok['status'];


// =====================================================
// PROSES UPDATE
// =====================================================

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $namaKelompok =
        trim(
            $_POST['nama_kelompok']
            ?? ''
        );

    $tipePilihan =
        $_POST['tipe_pilihan']
        ?? 'single';

    $wajib =
        isset(
            $_POST['wajib']
        )
            ? 1
            : 0;

    $minPilih =
        intval(
            $_POST['min_pilih']
            ?? 0
        );

    $maxPilih =
        intval(
            $_POST['max_pilih']
            ?? 0
        );

    $urutan =
        intval(
            $_POST['urutan']
            ?? 1
        );

    $status =
        $_POST['status']
        ?? 'tersedia';


    // =================================================
    // VALIDASI
    // =================================================

    if (
        $namaKelompok === ''
    ) {

        $error =
            'Nama kelompok wajib diisi.';

    } elseif (
        !in_array(
            $tipePilihan,
            [
                'single',
                'multiple'
            ],
            true
        )
    ) {

        $error =
            'Tipe pilihan tidak valid.';

    } elseif (
        !in_array(
            $status,
            [
                'tersedia',
                'tidak_tersedia'
            ],
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

    }


    // =================================================
    // ATUR MIN / MAX
    // =================================================

    if (
        $error === ''
    ) {

        if (
            $tipePilihan === 'single'
        ) {

            $minPilih =
                $wajib
                    ? 1
                    : 0;

            $maxPilih = 1;

        } else {

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

                $error =
                    'Minimal pilihan tidak boleh lebih besar dari maksimal pilihan.';

            }

        }

    }


    // =================================================
    // UPDATE
    // =================================================

    if (
        $error === ''
    ) {

        $stmt = $conn->prepare("
            UPDATE kelompok_pilihan
            SET
                nama_kelompok = ?,
                tipe_pilihan = ?,
                wajib = ?,
                min_pilih = ?,
                max_pilih = ?,
                urutan = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssiiii si",
            $namaKelompok,
            $tipePilihan,
            $wajib,
            $minPilih,
            $maxPilih,
            $urutan,
            $status,
            $id
        );

        /*
         * Hapus spasi dari format bind type
         * supaya aman.
         */

        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE kelompok_pilihan
            SET
                nama_kelompok = ?,
                tipe_pilihan = ?,
                wajib = ?,
                min_pilih = ?,
                max_pilih = ?,
                urutan = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssiiii si",
            $namaKelompok,
            $tipePilihan,
            $wajib,
            $minPilih,
            $maxPilih,
            $urutan,
            $status,
            $id
        );

        /*
         * Karena bind_param harus tanpa spasi,
         * gunakan statement final di bawah.
         */

        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE kelompok_pilihan
            SET
                nama_kelompok = ?,
                tipe_pilihan = ?,
                wajib = ?,
                min_pilih = ?,
                max_pilih = ?,
                urutan = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssiiii si",
            $namaKelompok,
            $tipePilihan,
            $wajib,
            $minPilih,
            $maxPilih,
            $urutan,
            $status,
            $id
        );

    }


    // =================================================
    // FINAL UPDATE
    // =================================================

    if (
        $error === ''
    ) {

        /*
         * Pakai query update sederhana
         * dengan tipe:
         *
         * s s i i i i s i
         *
         * = 8 parameter
         */

        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE kelompok_pilihan
            SET
                nama_kelompok = ?,
                tipe_pilihan = ?,
                wajib = ?,
                min_pilih = ?,
                max_pilih = ?,
                urutan = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssiiiisi",
            $namaKelompok,
            $tipePilihan,
            $wajib,
            $minPilih,
            $maxPilih,
            $urutan,
            $status,
            $id
        );

        if (
            $stmt->execute()
        ) {

            $stmt->close();

            header(
                "Location: kelompok.php?menu_id="
                . $menuId
            );

            exit;

        } else {

            $error =
                'Gagal mengubah kelompok: '
                . $stmt->error;

            $stmt->close();

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
        Edit Kelompok |
        <?= htmlspecialchars(
            $kelompok['nama_menu']
        ); ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        .form-page-header {
            margin-bottom: 25px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #e87518;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .form-page-header h1 {
            margin: 0 0 7px;
        }

        .form-page-header p {
            margin: 0;
            color: #777;
        }

        .choice-form-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 18px;
            padding: 28px;
            max-width: 900px;
        }

        .menu-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #fff8ef;
            border: 1px solid #f4dfca;
            border-radius: 14px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .menu-info-image {
            width: 55px;
            height: 55px;
            border-radius: 11px;
            overflow: hidden;
            background: #f7eee5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            flex-shrink: 0;
        }

        .menu-info-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-info strong {
            display: block;
            margin-bottom: 4px;
        }

        .menu-info span {
            color: #777;
            font-size: 12px;
        }

        .error-box {
            margin-bottom: 20px;
            padding: 13px 15px;
            background: #ffe8e8;
            color: #c62828;
            border: 1px solid #ffcaca;
            border-radius: 10px;
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(
                2,
                minmax(0, 1fr)
            );
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 800;
        }

        .required {
            color: #e87518;
        }

        .form-control {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            outline: none;
        }

        .form-control:focus {
            border-color: #e87518;
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
            display: block;
            margin-top: 6px;
            color: #888;
            font-size: 11px;
        }

        .checkbox-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 10px;
        }

        .checkbox-box input {
            width: 17px;
            height: 17px;
            accent-color: #e87518;
        }

        .checkbox-box label {
            margin: 0;
            cursor: pointer;
        }

        .multiple-settings {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #eee;
            padding-top: 22px;
            margin-top: 5px;
        }

        .btn-cancel {
            padding: 12px 18px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            color: #555;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .btn-save {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            background: #e87518;
            color: white;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #d76510;
        }

        @media (
            max-width: 700px
        ) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .multiple-settings {
                grid-template-columns: 1fr;
            }

            .choice-form-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn-cancel,
            .btn-save {
                width: 100%;
                box-sizing: border-box;
                text-align: center;
            }

        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    <!-- SIDEBAR -->

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

            <a href="../menu/index.php">
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

            <a
                href="index.php"
                class="active"
            >
                <span>⚙️</span>
                <span>Pilihan Menu</span>
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


    <!-- MAIN -->

    <main class="main-content">

        <div class="form-page-header">

            <a
                href="kelompok.php?menu_id=<?= $menuId; ?>"
                class="back-link"
            >

                ← Kembali ke
                <?= htmlspecialchars(
                    $kelompok['nama_menu']
                ); ?>

            </a>


            <h1>
                Edit Kelompok Pilihan
            </h1>


            <p>
                Ubah aturan kelompok pilihan
                untuk menu ini.
            </p>

        </div>


        <div class="choice-form-card">


            <!-- MENU INFO -->

            <div class="menu-info">

                <div class="menu-info-image">

                    <?php if (
                        !empty(
                            $kelompok['foto']
                        )
                    ): ?>

                        <img
                            src="../../assets/uploads/menu/<?= htmlspecialchars(
                                $kelompok['foto']
                            ); ?>"
                            alt="<?= htmlspecialchars(
                                $kelompok['nama_menu']
                            ); ?>"
                        >

                    <?php else: ?>

                        🍛

                    <?php endif; ?>

                </div>


                <div>

                    <strong>

                        <?= htmlspecialchars(
                            $kelompok['nama_menu']
                        ); ?>

                    </strong>


                    <span>

                        Harga dasar:
                        Rp<?= number_format(
                            floatval(
                                $kelompok['harga_menu']
                            ),
                            0,
                            ',',
                            '.'
                        ); ?>

                    </span>

                </div>

            </div>


            <!-- ERROR -->

            <?php if (
                $error !== ''
            ): ?>

                <div class="error-box">

                    ⚠️
                    <?= htmlspecialchars(
                        $error
                    ); ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $id; ?>"
                >


                <div class="form-grid">


                    <!-- NAMA -->

                    <div class="form-group full">

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
                            value="<?= htmlspecialchars(
                                $namaKelompok
                            ); ?>"
                            required
                        >


                        <small class="form-help">

                            Contoh:
                            Bagian Ayam, Sambal,
                            Tambahan, Ukuran.

                        </small>

                    </div>


                    <!-- TIPE -->

                    <div class="form-group">

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
                                Single — pilih satu
                            </option>


                            <option
                                value="multiple"
                                <?= $tipePilihan === 'multiple'
                                    ? 'selected'
                                    : ''; ?>
                            >
                                Multiple — bisa pilih banyak
                            </option>

                        </select>

                    </div>


                    <!-- STATUS -->

                    <div class="form-group">

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


                    <!-- WAJIB -->

                    <div class="form-group">

                        <label>
                            Aturan Pilihan
                        </label>


                        <div class="checkbox-box">

                            <input
                                type="checkbox"
                                id="wajib"
                                name="wajib"
                                value="1"
                                <?= $wajib
                                    ? 'checked'
                                    : ''; ?>
                            >


                            <label for="wajib">

                                Pilihan wajib diisi
                                pelanggan

                            </label>

                        </div>

                    </div>


                    <!-- URUTAN -->

                    <div class="form-group">

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

                    </div>


                    <!-- MIN MAX -->

                    <div
                        class="form-group full"
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

                                <small class="form-help">
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

                                <small class="form-help">
                                    Maksimal pilihan
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ACTION -->

                <div class="form-actions">

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
                        💾 Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>


<script>

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


tipePilihan.addEventListener(
    'change',
    updatePilihanForm
);


wajib.addEventListener(
    'change',
    updatePilihanForm
);


updatePilihanForm();

</script>

</body>

</html>