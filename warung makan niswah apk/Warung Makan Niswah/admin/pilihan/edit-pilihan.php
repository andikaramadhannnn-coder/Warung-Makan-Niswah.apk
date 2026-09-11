<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID PILIHAN
// =====================================================

$pilihanId = intval(
    $_GET['id'] ?? 0
);

if ($pilihanId <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA PILIHAN + KELOMPOK + MENU
// =====================================================

$stmt = $conn->prepare("
    SELECT
        pm.id,
        pm.menu_id,
        pm.kelompok_id,
        pm.tipe_pilihan,
        pm.nama_pilihan,
        pm.harga_tambahan,
        pm.status,

        kp.nama_kelompok,

        m.nama_menu

    FROM pilihan_menu pm

    LEFT JOIN kelompok_pilihan kp
        ON kp.id = pm.kelompok_id

    LEFT JOIN menu m
        ON m.id = pm.menu_id

    WHERE pm.id = ?

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $pilihanId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$pilihan = $result->fetch_assoc();

$stmt->close();


// =====================================================
// DATA AWAL
// =====================================================

$nama =
    $pilihan['nama_pilihan'];

$harga =
    floatval(
        $pilihan['harga_tambahan']
    );

$status =
    $pilihan['status'];

$error = "";


// =====================================================
// PROSES UPDATE
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $nama =
        trim(
            $_POST['nama_pilihan'] ?? ''
        );

    $harga =
        floatval(
            $_POST['harga_tambahan'] ?? 0
        );

    $status =
        $_POST['status'] ?? 'tersedia';


    // =================================================
    // VALIDASI
    // =================================================

    if (
        $nama === ''
    ) {

        $error =
            "Nama pilihan wajib diisi.";

    } elseif (
        $harga < 0
    ) {

        $error =
            "Harga tambahan tidak boleh negatif.";

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
            "Status pilihan tidak valid.";

    } else {


        // =============================================
        // UPDATE DATABASE
        // =============================================

        $stmt = $conn->prepare("
            UPDATE pilihan_menu

            SET
                nama_pilihan = ?,
                harga_tambahan = ?,
                status = ?

            WHERE id = ?

            LIMIT 1
        ");


        $stmt->bind_param(
            "sdsi",
            $nama,
            $harga,
            $status,
            $pilihanId
        );


        if (
            $stmt->execute()
        ) {

            $stmt->close();


            // =========================================
            // SELESAI → BALIK KE KELOMPOK
            // =========================================

            header(
                "Location: kelompok.php?menu_id="
                . intval(
                    $pilihan['menu_id']
                )
            );

            exit;

        } else {

            $error =
                "Pilihan gagal diperbarui.";

        }


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
        Edit Pilihan |
        <?= htmlspecialchars(
            $pilihan['nama_pilihan']
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

        .edit-page-header {

            margin-bottom: 25px;

        }


        .edit-page-header h1 {

            margin: 0 0 7px;

        }


        .edit-page-header p {

            margin: 0;

            color: #777;

        }


        /* =====================================================
           BACK
        ===================================================== */

        .btn-back {

            display: inline-block;

            margin-bottom: 15px;

            color: #e87518;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .edit-card {

            background: white;

            border: 1px solid #eee;

            border-radius: 18px;

            padding: 30px;

            max-width: 800px;

        }


        /* =====================================================
           INFO
        ===================================================== */

        .choice-info {

            display: flex;

            align-items: center;

            gap: 15px;

            background: #fffaf5;

            border: 1px solid #f3dfcc;

            border-radius: 14px;

            padding: 18px;

            margin-bottom: 25px;

        }


        .choice-info-icon {

            width: 48px;

            height: 48px;

            border-radius: 12px;

            background: #fff0df;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 23px;

            flex-shrink: 0;

        }


        .choice-info h3 {

            margin: 0 0 5px;

            font-size: 16px;

        }


        .choice-info p {

            margin: 0;

            color: #777;

            font-size: 12px;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 13px;

            font-weight: 800;

        }


        .form-group input,

        .form-group select {

            width: 100%;

            box-sizing: border-box;

            padding: 13px 14px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 14px;

            outline: none;

            background: white;

        }


        .form-group input:focus,

        .form-group select:focus {

            border-color: #e87518;

            box-shadow:
                0 0 0 3px
                rgba(232, 117, 24, .08);

        }


        .form-help {

            margin-top: 7px;

            color: #888;

            font-size: 11px;

        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-box {

            background: #fff0f0;

            border: 1px solid #ffcaca;

            color: #c62828;

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        /* =====================================================
           ACTIONS
        ===================================================== */

        .form-actions {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 10px;

            padding-top: 10px;

            border-top: 1px solid #eee;

            margin-top: 10px;

        }


        .btn-cancel {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 13px 20px;

            border: 1px solid #ddd;

            border-radius: 10px;

            background: white;

            color: #555;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

        }


        .btn-cancel:hover {

            border-color: #bbb;

        }


        .btn-save {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 13px 21px;

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


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (
            max-width: 700px
        ) {

            .edit-card {

                padding: 20px;

            }


            .form-actions {

                flex-direction: column-reverse;

            }


            .btn-cancel,

            .btn-save {

                width: 100%;

                box-sizing: border-box;

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


        <!-- KEMBALI -->

        <a
            href="kelompok.php?menu_id=<?= intval($pilihan['menu_id']); ?>"
            class="btn-back"
        >

            ← Kembali ke
            <?= htmlspecialchars(
                $pilihan['nama_kelompok']
            ); ?>

        </a>


        <!-- HEADER -->

        <header
            class="page-header edit-page-header"
        >

            <h1>
                Edit Pilihan
            </h1>

            <p>
                Ubah informasi pilihan menu.
            </p>

        </header>


        <!-- CARD -->

        <div class="edit-card">


            <!-- =================================================
                 INFO PILIHAN
            ================================================== -->

            <div class="choice-info">


                <div class="choice-info-icon">

                    <?php if (
                        $pilihan['tipe_pilihan']
                        ===
                        'bagian_ayam'
                    ): ?>

                        🍗

                    <?php elseif (
                        $pilihan['tipe_pilihan']
                        ===
                        'sambal'
                    ): ?>

                        🌶️

                    <?php else: ?>

                        ➕

                    <?php endif; ?>

                </div>


                <div>

                    <h3>

                        <?= htmlspecialchars(
                            $pilihan['nama_kelompok']
                        ); ?>

                    </h3>


                    <p>

                        Menu:
                        <strong>
                            <?= htmlspecialchars(
                                $pilihan['nama_menu']
                            ); ?>
                        </strong>

                        · Pilihan:
                        <strong>
                            <?= htmlspecialchars(
                                $pilihan['nama_pilihan']
                            ); ?>
                        </strong>

                    </p>

                </div>


            </div>


            <!-- ERROR -->

            <?php if (
                $error !== ''
            ): ?>

                <div
                    class="error-box"
                >

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
                action=""
            >


                <!-- NAMA -->

                <div
                    class="form-group"
                >

                    <label
                        for="nama_pilihan"
                    >

                        Nama Pilihan
                        <span style="color:#e87518;">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        id="nama_pilihan"
                        name="nama_pilihan"
                        value="<?= htmlspecialchars(
                            $nama
                        ); ?>"
                        placeholder="Contoh: Dada"
                        required
                    >


                    <div
                        class="form-help"
                    >

                        Contoh:
                        Dada, Paha, Sayap,
                        Sambal Merah,
                        Sambal Ijo.

                    </div>

                </div>


                <!-- HARGA -->

                <div
                    class="form-group"
                >

                    <label
                        for="harga_tambahan"
                    >

                        Harga Tambahan

                    </label>


                    <input
                        type="number"
                        id="harga_tambahan"
                        name="harga_tambahan"
                        value="<?= htmlspecialchars(
                            $harga
                        ); ?>"
                        min="0"
                        step="500"
                        placeholder="0"
                    >


                    <div
                        class="form-help"
                    >

                        Isi 0 jika pilihan gratis.

                    </div>

                </div>


                <!-- STATUS -->

                <div
                    class="form-group"
                >

                    <label
                        for="status"
                    >

                        Status

                    </label>


                    <select
                        id="status"
                        name="status"
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


                    <div
                        class="form-help"
                    >

                        Pilihan tidak tersedia
                        tidak akan ditampilkan
                        kepada pelanggan.

                    </div>

                </div>


                <!-- =================================================
                     ACTION
                ================================================== -->

                <div
                    class="form-actions"
                >


                    <a
                        href="kelompok.php?menu_id=<?= intval($pilihan['menu_id']); ?>"
                        class="btn-cancel"
                    >

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="btn-save"
                    >

                        💾
                        Simpan Perubahan

                    </button>


                </div>


            </form>


        </div>


    </main>


</div>


</body>

</html>