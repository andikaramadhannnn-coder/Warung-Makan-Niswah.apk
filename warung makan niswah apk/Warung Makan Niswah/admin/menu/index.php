<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

/* =========================
   AMBIL DATA MENU
========================= */

$query = "
    SELECT
        menu.*,
        kategori.nama_kategori
    FROM menu
    INNER JOIN kategori
        ON menu.kategori_id = kategori.id
    ORDER BY menu.id DESC
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Menu Makanan | Warung Makan Niswah</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        /* =========================
           ACTION BUTTONS
        ========================= */

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-buttons a {
            text-decoration: none;
        }

        .btn-option,
        .btn-variant,
        .btn-edit,
        .btn-delete {

            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            border: none;

            cursor: pointer;

            font-size: 16px;

            transition: all 0.2s ease;

        }

        /* PILIHAN */

        .btn-option {

            background: #fff4e8;
            color: #f27c13;

        }

        .btn-option:hover {

            background: #f27c13;
            color: white;

            transform: translateY(-2px);

        }

        /* VARIAN */

        .btn-variant {

            background: #eef6ff;
            color: #2878d8;

        }

        .btn-variant:hover {

            background: #2878d8;
            color: white;

            transform: translateY(-2px);

        }

        /* EDIT */

        .btn-edit {

            background: #fff8e8;
            color: #e89a00;

        }

        .btn-edit:hover {

            background: #e89a00;
            color: white;

            transform: translateY(-2px);

        }

        /* DELETE */

        .btn-delete {

            background: #fff0f0;
            color: #e34b4b;

        }

        .btn-delete:hover {

            background: #e34b4b;
            color: white;

            transform: translateY(-2px);

        }

        /* =========================
           ACTION LABEL
        ========================= */

        .action-label {

            font-size: 11px;

            color: #777;

            margin-top: 3px;

        }

        /* =========================
           MENU IMAGE
        ========================= */

        .menu-image {

            width: 55px;
            height: 55px;

            object-fit: cover;

            border-radius: 10px;

            border: 1px solid #eee;

        }

        .no-image {

            width: 55px;
            height: 55px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #fff4e8;

            border-radius: 10px;

            font-size: 24px;

        }

        /* =========================
           DESCRIPTION
        ========================= */

        .menu-description {

            display: block;

            margin-top: 4px;

            color: #888;

            font-size: 12px;

            max-width: 250px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

        }

        /* =========================
           EMPTY
        ========================= */

        .empty-table {

            text-align: center;

            padding: 60px 20px;

        }

        .empty-table > div {

            font-size: 50px;

            margin-bottom: 10px;

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">


        <!-- LOGO -->

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


        <!-- MENU SIDEBAR -->

        <nav class="sidebar-menu">


            <a href="../dashboard.php">

                <span>
                    📊
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="index.php"
                class="active"
            >

                <span>
                    🍛
                </span>

                <span>
                    Menu Makanan
                </span>

            </a>


            <a href="../kategori/index.php">

                <span>
                    📂
                </span>

                <span>
                    Kategori
                </span>

            </a>


            <a href="../pesanan/index.php">

                <span>
                    🛒
                </span>

                <span>
                    Pesanan
                </span>

            </a>


            <a href="../pelanggan/index.php">

                <span>
                    👥
                </span>

                <span>
                    Pelanggan
                </span>

            </a>


            <a href="../pembayaran/index.php">

                <span>
                    💰
                </span>

                <span>
                    Pembayaran
                </span>

            </a>


            <a href="../laporan/index.php">

                <span>
                    📈
                </span>

                <span>
                    Laporan
                </span>

            </a>


        </nav>


        <!-- KELUAR -->

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
         MAIN CONTENT
    ====================================================== -->

    <main class="main-content">


        <!-- HEADER -->

        <header class="page-header">


            <div>

                <h1>
                    Menu Makanan
                </h1>

                <p>
                    Kelola daftar makanan dan minuman
                    Warung Makan Niswah.
                </p>

            </div>


            <!-- TAMBAH MENU -->

            <a
                href="tambah.php"
                class="btn-primary"
            >

                + Tambah Menu

            </a>


        </header>



        <!-- =================================================
             TABLE CARD
        ================================================== -->

        <div class="table-card">


            <!-- HEADER TABLE -->

            <div class="table-header">

                <div>

                    <h3>
                        Daftar Menu
                    </h3>

                    <p>
                        Semua makanan dan minuman
                    </p>

                </div>

            </div>



            <!-- TABLE -->

            <div class="table-wrapper">


                <table>


                    <thead>

                        <tr>

                            <th>
                                No
                            </th>

                            <th>
                                Foto
                            </th>

                            <th>
                                Nama Menu
                            </th>

                            <th>
                                Kategori
                            </th>

                            <th>
                                Harga
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php

                    $no = 1;

                    if ($result && $result->num_rows > 0):

                        while ($row = $result->fetch_assoc()):

                    ?>


                        <tr>


                            <!-- NO -->

                            <td>

                                <?= $no++; ?>

                            </td>



                            <!-- FOTO -->

                            <td>


                                <?php if (!empty($row['foto'])): ?>


                                    <img
                                        src="../../assets/uploads/menu/<?= htmlspecialchars($row['foto']); ?>"
                                        class="menu-image"
                                        alt="<?= htmlspecialchars($row['nama_menu']); ?>"
                                    >


                                <?php else: ?>


                                    <div class="no-image">

                                        🍛

                                    </div>


                                <?php endif; ?>


                            </td>



                            <!-- NAMA MENU -->

                            <td>


                                <strong>

                                    <?= htmlspecialchars(
                                        $row['nama_menu']
                                    ); ?>

                                </strong>


                                <?php if (!empty($row['deskripsi'])): ?>


                                    <small class="menu-description">

                                        <?= htmlspecialchars(
                                            $row['deskripsi']
                                        ); ?>

                                    </small>


                                <?php endif; ?>


                            </td>



                            <!-- KATEGORI -->

                            <td>

                                <span class="category-badge">

                                    <?= htmlspecialchars(
                                        $row['nama_kategori']
                                    ); ?>

                                </span>

                            </td>



                            <!-- HARGA -->

                            <td>

                                <strong>

                                    Rp<?= number_format(
                                        $row['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>

                            </td>



                            <!-- STATUS -->

                            <td>


                                <?php if (
                                    $row['status'] === 'tersedia'
                                ): ?>


                                    <span class="status available">

                                        Tersedia

                                    </span>


                                <?php else: ?>


                                    <span class="status sold-out">

                                        Habis

                                    </span>


                                <?php endif; ?>


                            </td>



                            <!-- =================================================
                                 AKSI
                            ================================================== -->

                            <td>


                                <div class="action-buttons">


                                    <!-- =====================================
                                         PILIHAN MENU
                                    ====================================== -->

                                    <a
                                        href="../pilihan/kelompok.php?menu_id=<?= $row['id']; ?>"
                                        class="btn-option"
                                        title="Kelola Pilihan Menu"
                                    >

                                        ⚙️

                                    </a>



                                    <!-- =====================================
                                         VARIAN MENU
                                    ====================================== -->

                                    <a
                                        href="../varian/index.php?menu_id=<?= $row['id']; ?>"
                                        class="btn-variant"
                                        title="Kelola Varian Menu"
                                    >

                                        🍚

                                    </a>



                                    <!-- =====================================
                                         UBAH STATUS
                                    ====================================== -->

                                    <a
                                        href="status.php?id=<?= $row['id']; ?>"
                                        class="btn-status"
                                        title="Ubah status"
                                    >

                                        🔄

                                    </a>



                                    <!-- =====================================
                                         EDIT
                                    ====================================== -->

                                    <a
                                        href="edit.php?id=<?= $row['id']; ?>"
                                        class="btn-edit"
                                        title="Edit menu"
                                    >

                                        ✏️

                                    </a>



                                    <!-- =====================================
                                         HAPUS
                                    ====================================== -->

                                    <a
                                        href="hapus.php?id=<?= $row['id']; ?>"
                                        class="btn-delete"
                                        title="Hapus menu"
                                        onclick="return confirm('Yakin ingin menghapus menu ini?')"
                                    >

                                        🗑️

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php


                        endwhile;


                    else:


                    ?>


                        <!-- EMPTY -->

                        <tr>

                            <td
                                colspan="7"
                                class="empty-table"
                            >


                                <div>
                                    🍛
                                </div>


                                <strong>
                                    Belum ada menu
                                </strong>


                                <p>
                                    Silakan tambahkan menu pertama.
                                </p>


                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>


    </main>


</div>


</body>

</html>