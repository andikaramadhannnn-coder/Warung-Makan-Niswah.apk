<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID VARIAN & MENU ID
// =====================================================

$varianId = intval(
    $_GET['id']
    ?? $_POST['id']
    ?? 0
);

$menuId = intval(
    $_GET['menu_id']
    ?? $_POST['menu_id']
    ?? 0
);


// =====================================================
// VALIDASI ID
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
        harga,
        status,
        urutan
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
// MENU ID DARI DATABASE
// =====================================================

$menuId = intval(
    $varian['menu_id']
);


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

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: ../menu/index.php");
    exit;

}


$menu = $result->fetch_assoc();

$stmt->close();


// =====================================================
// NILAI FORM
// =====================================================

$namaVarian =
    $varian['nama_varian'];

$harga =
    intval($varian['harga']);

$status =
    $varian['status'];

$urutan =
    intval($varian['urutan']);

$error = '';


// =====================================================
// PROSES UPDATE
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $namaVarian = trim(
        $_POST['nama_varian']
        ?? ''
    );


    $harga = intval(
        $_POST['harga']
        ?? 0
    );


    $status =
        $_POST['status']
        ?? 'tersedia';


    $urutan = intval(
        $_POST['urutan']
        ?? 1
    );


    // =================================================
    // VALIDASI NAMA
    // =================================================

    if ($namaVarian === '') {

        $error =
            'Nama varian wajib diisi.';

    }


    // =================================================
    // VALIDASI HARGA
    // =================================================

    elseif ($harga < 0) {

        $error =
            'Harga tidak boleh kurang dari 0.';

    }


    // =================================================
    // VALIDASI STATUS
    // =================================================

    elseif (
        $status !== 'tersedia'
        &&
        $status !== 'tidak_tersedia'
    ) {

        $error =
            'Status varian tidak valid.';

    }


    // =================================================
    // VALIDASI URUTAN
    // =================================================

    elseif ($urutan < 1) {

        $error =
            'Urutan minimal adalah 1.';

    }


    // =================================================
    // CEK DUPLIKAT
    // =================================================

    else {


        $stmt = $conn->prepare("
            SELECT
                id
            FROM varian_menu
            WHERE menu_id = ?
              AND LOWER(nama_varian)
                  = LOWER(?)
              AND id != ?
            LIMIT 1
        ");


        $stmt->bind_param(
            "isi",
            $menuId,
            $namaVarian,
            $varianId
        );


        $stmt->execute();

        $result =
            $stmt->get_result();


        if (
            $result->num_rows > 0
        ) {

            $error =
                'Nama varian tersebut sudah digunakan oleh menu ini.';

        }


        $stmt->close();

    }


    // =================================================
    // UPDATE DATABASE
    // =================================================

    if ($error === '') {


        $stmt = $conn->prepare("
            UPDATE varian_menu
            SET
                nama_varian = ?,
                harga = ?,
                status = ?,
                urutan = ?,
                updated_at = NOW()
            WHERE id = ?
              AND menu_id = ?
        ");


        $stmt->bind_param(
            "sisiii",
            $namaVarian,
            $harga,
            $status,
            $urutan,
            $varianId,
            $menuId
        );


        if ($stmt->execute()) {

            $stmt->close();


            header(
                "Location: index.php?menu_id="
                . $menuId
            );

            exit;

        }


        $error =
            'Gagal mengubah varian: '
            . $stmt->error;


        $stmt->close();

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

        Edit Varian |
        <?= htmlspecialchars(
            $menu['nama_menu']
        ); ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <style>

        .variant-form-page {

            max-width: 900px;

        }


        .variant-back {

            display: inline-block;

            margin-bottom: 18px;

            color: #e87518;

            text-decoration: none;

            font-size: 13px;

            font-weight: 800;

        }


        .variant-back:hover {

            text-decoration: underline;

        }


        .form-page-header {

            margin-bottom: 25px;

        }


        .form-page-header h1 {

            margin: 0 0 7px;

        }


        .form-page-header p {

            margin: 0;

            color: #777;

        }


        .menu-info-card {

            display: flex;

            align-items: center;

            gap: 15px;

            background: #fff8ef;

            border: 1px solid #f4dfca;

            border-radius: 16px;

            padding: 18px 20px;

            margin-bottom: 20px;

        }


        .menu-info-image {

            width: 65px;

            height: 65px;

            object-fit: cover;

            border-radius: 13px;

        }


        .menu-info-no-image {

            width: 65px;

            height: 65px;

            border-radius: 13px;

            background: #fff0df;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;

        }


        .menu-info-text h3 {

            margin: 0 0 5px;

        }


        .menu-info-text p {

            margin: 0;

            color: #777;

            font-size: 13px;

        }


        .form-card {

            background: white;

            border: 1px solid #eee;

            border-radius: 18px;

            padding: 28px;

        }


        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

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

            border-radius: 11px;

            font-size: 14px;

            outline: none;

            background: white;

        }


        .form-control:focus {

            border-color: #e87518;

        }


        .form-help {

            display: block;

            margin-top: 7px;

            color: #888;

            font-size: 12px;

            line-height: 1.5;

        }


        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;

        }


        .error-box {

            margin-bottom: 20px;

            padding: 14px 16px;

            border-radius: 11px;

            background: #ffeaea;

            border: 1px solid #ffcaca;

            color: #c62828;

            font-size: 13px;

            font-weight: 600;

        }


        .preview-card {

            margin-top: 25px;

            padding: 20px;

            background: #fafafa;

            border: 1px solid #eee;

            border-radius: 14px;

        }


        .preview-title {

            color: #888;

            font-size: 12px;

            margin-bottom: 10px;

        }


        .preview-row {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .preview-name {

            font-size: 17px;

            font-weight: 800;

        }


        .preview-price {

            color: #e87518;

            font-size: 17px;

            font-weight: 800;

        }


        .form-actions {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 28px;

            padding-top: 22px;

            border-top: 1px solid #eee;

        }


        .btn-cancel {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 18px;

            border-radius: 10px;

            background: white;

            border: 1px solid #ddd;

            color: #555;

            text-decoration: none;

            font-size: 13px;

            font-weight: 800;

        }


        .btn-save {

            border: none;

            padding: 12px 20px;

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


        @media (max-width: 700px) {

            .form-grid {

                grid-template-columns: 1fr;

            }


            .form-card {

                padding: 20px;

            }


            .form-actions {

                flex-direction: column;

            }


            .btn-cancel,
            .btn-save {

                width: 100%;

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

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="../menu/index.php"
                class="active"
            >

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


        </nav>


        <div class="sidebar-bottom">


            <a
                href="../logout.php"
                class="logout"
            >

                <span>🚪</span>

                <span>
                    Keluar
                </span>

            </a>


        </div>


    </aside>



    <!-- MAIN -->

    <main class="main-content">


        <div class="variant-form-page">


            <!-- KEMBALI -->

            <a
                href="index.php?menu_id=<?= $menuId; ?>"
                class="variant-back"
            >

                ← Kembali ke Varian

            </a>



            <!-- HEADER -->

            <header class="form-page-header">

                <h1>
                    Edit Varian
                </h1>

                <p>
                    Ubah informasi varian
                    menu ini.
                </p>

            </header>



            <!-- INFO MENU -->

            <div class="menu-info-card">


                <?php if (
                    !empty($menu['foto'])
                ): ?>


                    <img
                        src="../../assets/uploads/menu/<?= htmlspecialchars(
                            $menu['foto']
                        ); ?>"
                        class="menu-info-image"
                        alt="<?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>"
                    >


                <?php else: ?>


                    <div class="menu-info-no-image">

                        🍛

                    </div>


                <?php endif; ?>


                <div class="menu-info-text">


                    <h3>

                        <?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>

                    </h3>


                    <p>

                        Harga dasar:

                        Rp<?= number_format(
                            $menu['harga'],
                            0,
                            ',',
                            '.'
                        ); ?>

                    </p>


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

            <div class="form-card">


                <form
                    method="POST"
                    action=""
                >


                    <input
                        type="hidden"
                        name="id"
                        value="<?= $varianId; ?>"
                    >


                    <input
                        type="hidden"
                        name="menu_id"
                        value="<?= $menuId; ?>"
                    >



                    <!-- NAMA -->

                    <div class="form-group">


                        <label>

                            Nama Varian

                            <span class="required">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="nama_varian"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $namaVarian
                            ); ?>"
                            maxlength="100"
                            required
                        >


                        <small class="form-help">

                            Contoh:
                            Dengan Nasi,
                            Lauk Saja,
                            atau varian lainnya.

                        </small>


                    </div>



                    <!-- HARGA + URUTAN -->

                    <div class="form-grid">


                        <div class="form-group">


                            <label>

                                Harga Varian

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="number"
                                name="harga"
                                class="form-control"
                                min="0"
                                step="100"
                                value="<?= intval(
                                    $harga
                                ); ?>"
                                required
                            >


                            <small class="form-help">

                                Harga akhir
                                untuk varian ini.

                            </small>


                        </div>



                        <div class="form-group">


                            <label>
                                Urutan
                            </label>


                            <input
                                type="number"
                                name="urutan"
                                class="form-control"
                                min="1"
                                value="<?= intval(
                                    $urutan
                                ); ?>"
                            >


                            <small class="form-help">

                                Semakin kecil,
                                semakin awal ditampilkan.

                            </small>


                        </div>


                    </div>



                    <!-- STATUS -->

                    <div class="form-group">


                        <label>

                            Status

                            <span class="required">
                                *
                            </span>

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



                    <!-- PREVIEW -->

                    <div class="preview-card">


                        <div class="preview-title">

                            Preview

                        </div>


                        <div class="preview-row">


                            <div
                                class="preview-name"
                                id="previewName"
                            >

                                <?= htmlspecialchars(
                                    $namaVarian
                                ); ?>

                            </div>


                            <div
                                class="preview-price"
                                id="previewPrice"
                            >

                                Rp<?= number_format(
                                    $harga,
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>


                        </div>


                    </div>



                    <!-- BUTTON -->

                    <div class="form-actions">


                        <a
                            href="index.php?menu_id=<?= $menuId; ?>"
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


        </div>


    </main>


</div>



<script>

const namaInput =
    document.querySelector(
        'input[name="nama_varian"]'
    );

const hargaInput =
    document.querySelector(
        'input[name="harga"]'
    );

const previewName =
    document.getElementById(
        'previewName'
    );

const previewPrice =
    document.getElementById(
        'previewPrice'
    );


function updatePreview() {

    const nama =
        namaInput.value.trim();


    const harga =
        parseInt(
            hargaInput.value || 0
        );


    previewName.textContent =
        nama !== ''
            ? nama
            : 'Nama Varian';


    previewPrice.textContent =
        'Rp' +
        harga.toLocaleString(
            'id-ID'
        );

}


namaInput.addEventListener(
    'input',
    updatePreview
);


hargaInput.addEventListener(
    'input',
    updatePreview
);

</script>


</body>

</html>