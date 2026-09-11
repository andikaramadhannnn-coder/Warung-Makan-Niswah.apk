<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL MENU ID
// =====================================================

$menuId = intval(
    $_GET['menu_id'] ?? 0
);


// =====================================================
// VALIDASI MENU ID
// =====================================================

if ($menuId <= 0) {

    header("Location: ../menu/index.php");
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
// AMBIL DATA VARIAN
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        nama_varian,
        harga,
        status,
        urutan,
        created_at
    FROM varian_menu
    WHERE menu_id = ?
    ORDER BY urutan ASC, id ASC
");

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$varianResult = $stmt->get_result();


// =====================================================
// SIMPAN DATA VARIAN
// =====================================================

$varianList = [];

$totalVarian = 0;
$varianTersedia = 0;
$varianTidakTersedia = 0;


while ($row = $varianResult->fetch_assoc()) {

    $varianList[] = $row;

    $totalVarian++;

    if ($row['status'] === 'tersedia') {

        $varianTersedia++;

    } else {

        $varianTidakTersedia++;

    }

}

$stmt->close();

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
        Varian <?= htmlspecialchars(
            $menu['nama_menu']
        ); ?>
        | Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <style>

        /* =====================================================
           BACK BUTTON
        ===================================================== */

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


        /* =====================================================
           HEADER
        ===================================================== */

        .variant-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        .variant-title {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        .variant-menu-image {

            width: 70px;

            height: 70px;

            object-fit: cover;

            border-radius: 15px;

            background: #fff0df;

        }


        .variant-no-image {

            width: 70px;

            height: 70px;

            border-radius: 15px;

            background: #fff0df;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

        }


        .variant-title h1 {

            margin: 0 0 5px;

        }


        .variant-title p {

            margin: 0;

            color: #777;

        }


        /* =====================================================
           ADD BUTTON
        ===================================================== */

        .btn-add-variant {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            padding: 13px 20px;

            background: #e87518;

            color: white;

            border-radius: 10px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 800;

            white-space: nowrap;

            transition: .2s;

        }


        .btn-add-variant:hover {

            background: #d76510;

            transform: translateY(-1px);

        }


        /* =====================================================
           SUMMARY
        ===================================================== */

        .variant-summary {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 22px;

        }


        .summary-box {

            background: white;

            border: 1px solid #eee;

            border-radius: 15px;

            padding: 20px;

        }


        .summary-box strong {

            display: block;

            font-size: 25px;

            margin-bottom: 5px;

        }


        .summary-box span {

            color: #777;

            font-size: 13px;

        }


        .summary-box.available {

            border-left: 4px solid #22a06b;

        }


        .summary-box.unavailable {

            border-left: 4px solid #d9534f;

        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .variant-card {

            background: white;

            border: 1px solid #eee;

            border-radius: 18px;

            overflow: hidden;

        }


        .variant-card-header {

            padding: 22px 25px;

            border-bottom: 1px solid #eee;

        }


        .variant-card-header h3 {

            margin: 0 0 5px;

        }


        .variant-card-header p {

            margin: 0;

            color: #888;

            font-size: 13px;

        }


        .variant-table-wrapper {

            overflow-x: auto;

        }


        .variant-table {

            width: 100%;

            border-collapse: collapse;

        }


        .variant-table th {

            text-align: left;

            padding: 15px 20px;

            background: #fafafa;

            color: #666;

            font-size: 12px;

            white-space: nowrap;

        }


        .variant-table td {

            padding: 17px 20px;

            border-top: 1px solid #eee;

            font-size: 14px;

            vertical-align: middle;

        }


        .variant-name {

            font-weight: 800;

        }


        .variant-price {

            font-weight: 800;

            color: #e87518;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .variant-status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

        }


        .variant-status.available {

            background: #e8f7ef;

            color: #168052;

        }


        .variant-status.unavailable {

            background: #ffe9e9;

            color: #c62828;

        }


        /* =====================================================
           ACTION
        ===================================================== */

        .variant-actions {

            display: flex;

            align-items: center;

            gap: 7px;

        }


        .variant-action {

            width: 36px;

            height: 36px;

            border-radius: 9px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            border: 1px solid #eee;

            background: white;

            font-size: 15px;

            transition: .2s;

        }


        .variant-action:hover {

            transform: translateY(-1px);

            background: #fff8ef;

        }


        .variant-action.status {

            background: #f0f8f4;

        }


        .variant-action.edit {

            background: #fff4e7;

        }


        .variant-action.delete {

            background: #fff0f0;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .variant-empty {

            padding: 65px 20px;

            text-align: center;

        }


        .variant-empty-icon {

            font-size: 55px;

            margin-bottom: 12px;

        }


        .variant-empty h3 {

            margin: 0 0 8px;

        }


        .variant-empty p {

            color: #888;

            margin: 0 0 20px;

        }


        /* =====================================================
           INFO
        ===================================================== */

        .variant-info {

            margin-top: 20px;

            padding: 18px 20px;

            background: #fff8ef;

            border: 1px solid #f4dfca;

            border-radius: 13px;

            color: #765;

            font-size: 13px;

            line-height: 1.6;

        }


        .variant-info strong {

            color: #e87518;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 800px) {

            .variant-header {

                flex-direction: column;

                align-items: flex-start;

            }


            .variant-summary {

                grid-template-columns: 1fr;

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



    <!-- =====================================================
         MAIN
    ===================================================== -->

    <main class="main-content">


        <!-- BACK -->

        <a
            href="../menu/index.php"
            class="variant-back"
        >

            ← Kembali ke Menu Makanan

        </a>



        <!-- HEADER -->

        <header class="variant-header">


            <div class="variant-title">


                <?php if (!empty($menu['foto'])): ?>


                    <img
                        src="../../assets/uploads/menu/<?= htmlspecialchars(
                            $menu['foto']
                        ); ?>"
                        class="variant-menu-image"
                        alt="<?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>"
                    >


                <?php else: ?>


                    <div class="variant-no-image">

                        🍛

                    </div>


                <?php endif; ?>


                <div>


                    <h1>

                        Varian
                        <?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>

                    </h1>


                    <p>

                        Kelola pilihan penyajian
                        dan harga menu.

                    </p>


                </div>


            </div>



            <a
                href="tambah.php?menu_id=<?= $menuId; ?>"
                class="btn-add-variant"
            >

                + Tambah Varian

            </a>


        </header>



        <!-- =====================================================
             SUMMARY
        ===================================================== -->

        <div class="variant-summary">


            <div class="summary-box">


                <strong>
                    <?= $totalVarian; ?>
                </strong>


                <span>
                    Total Varian
                </span>


            </div>



            <div class="summary-box available">


                <strong>
                    <?= $varianTersedia; ?>
                </strong>


                <span>
                    Varian Tersedia
                </span>


            </div>



            <div class="summary-box unavailable">


                <strong>
                    <?= $varianTidakTersedia; ?>
                </strong>


                <span>
                    Tidak Tersedia
                </span>


            </div>


        </div>



        <!-- =====================================================
             VARIAN CARD
        ===================================================== -->

        <div class="variant-card">


            <div class="variant-card-header">


                <h3>
                    Daftar Varian
                </h3>


                <p>

                    Atur jenis penyajian
                    dan harga jual
                    <?= htmlspecialchars(
                        $menu['nama_menu']
                    ); ?>.

                </p>


            </div>



            <?php if (
                count($varianList) > 0
            ): ?>


                <div class="variant-table-wrapper">


                    <table class="variant-table">


                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    Nama Varian
                                </th>

                                <th>
                                    Harga
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Urutan
                                </th>

                                <th>
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $no = 1;

                        foreach (
                            $varianList
                            as $row
                        ):

                        ?>


                            <tr>


                                <td>

                                    <?= $no++; ?>

                                </td>


                                <td>

                                    <div class="variant-name">

                                        <?= htmlspecialchars(
                                            $row['nama_varian']
                                        ); ?>

                                    </div>

                                </td>


                                <td>

                                    <span class="variant-price">

                                        Rp<?= number_format(
                                            $row['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </span>

                                </td>


                                <td>


                                    <?php if (
                                        $row['status']
                                        === 'tersedia'
                                    ): ?>


                                        <span
                                            class="variant-status available"
                                        >

                                            🟢 Tersedia

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="variant-status unavailable"
                                        >

                                            🔴 Tidak Tersedia

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <td>

                                    <?= intval(
                                        $row['urutan']
                                    ); ?>

                                </td>


                                <td>


                                    <div class="variant-actions">


                                        <!-- STATUS -->

                                        <a
                                            href="status.php?id=<?= intval($row['id']); ?>&menu_id=<?= $menuId; ?>"
                                            class="variant-action status"
                                            title="Ubah Status"
                                        >

                                            🔄

                                        </a>


                                        <!-- EDIT -->

                                        <a
                                            href="edit.php?id=<?= intval($row['id']); ?>&menu_id=<?= $menuId; ?>"
                                            class="variant-action edit"
                                            title="Edit Varian"
                                        >

                                            ✏️

                                        </a>


                                        <!-- HAPUS -->

                                        <a
                                            href="hapus.php?id=<?= intval($row['id']); ?>&menu_id=<?= $menuId; ?>"
                                            class="variant-action delete"
                                            title="Hapus Varian"
                                            onclick="return confirm('Yakin ingin menghapus varian ini?')"
                                        >

                                            🗑️

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- EMPTY -->

                <div class="variant-empty">


                    <div class="variant-empty-icon">
                        🍚
                    </div>


                    <h3>
                        Belum Ada Varian
                    </h3>


                    <p>

                        Tambahkan varian seperti
                        <strong>Dengan Nasi</strong>
                        atau
                        <strong>Lauk Saja</strong>.

                    </p>


                    <a
                        href="tambah.php?menu_id=<?= $menuId; ?>"
                        class="btn-add-variant"
                    >

                        + Tambah Varian Pertama

                    </a>


                </div>


            <?php endif; ?>


        </div>



        <!-- =====================================================
             INFO
        ===================================================== -->

        <div class="variant-info">


            💡 <strong>Info:</strong>

            Varian digunakan untuk menentukan
            harga akhir menu sebelum pilihan
            seperti bagian ayam, sambal,
            atau tambahan dihitung.

            <br><br>

            Contoh:

            <strong>
                Dengan Nasi — Rp15.000
            </strong>

            dan

            <strong>
                Lauk Saja — Rp12.000
            </strong>.


        </div>


    </main>


</div>


</body>

</html>