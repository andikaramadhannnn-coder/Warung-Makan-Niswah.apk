<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL SEMUA MENU
// =====================================================

$query = "
    SELECT
        m.id,
        m.nama_menu,
        m.harga,
        m.foto,
        m.status,
        k.nama_kategori,

        (
            SELECT COUNT(*)
            FROM kelompok_pilihan kp
            WHERE kp.menu_id = m.id
              AND kp.status = 'tersedia'
        ) AS jumlah_kelompok,

        (
            SELECT COUNT(*)
            FROM pilihan_menu pm
            WHERE pm.menu_id = m.id
              AND pm.status = 'tersedia'
        ) AS jumlah_pilihan

    FROM menu m

    LEFT JOIN kategori k
        ON m.kategori_id = k.id

    ORDER BY m.id DESC
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

    <title>
        Pilihan Menu | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        /* =====================================================
           HEADER
        ===================================================== */

        .choice-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                20px;

            margin-bottom:
                25px;

        }


        .choice-header h1 {

            margin:
                0 0 6px;

        }


        .choice-header p {

            margin:
                0;

            color:
                #777;

        }


        /* =====================================================
           GRID
        ===================================================== */

        .menu-choice-grid {

            display:
                grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(
                        280px,
                        1fr
                    )
                );

            gap:
                20px;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .menu-choice-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            overflow:
                hidden;

            transition:
                .2s;

        }


        .menu-choice-card:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 10px 30px
                rgba(
                    0,
                    0,
                    0,
                    .08
                );

        }


        /* =====================================================
           FOTO
        ===================================================== */

        .menu-choice-image {

            height:
                190px;

            background:
                #f7eee5;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            overflow:
                hidden;

        }


        .menu-choice-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .menu-choice-no-image {

            font-size:
                65px;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .menu-choice-content {

            padding:
                20px;

        }


        .menu-choice-content h3 {

            margin:
                0 0 7px;

            font-size:
                18px;

        }


        .menu-choice-category {

            display:
                inline-block;

            padding:
                5px 9px;

            background:
                #fff0df;

            color:
                #e87518;

            border-radius:
                8px;

            font-size:
                11px;

            font-weight:
                700;

            margin-bottom:
                12px;

        }


        .menu-choice-price {

            font-size:
                15px;

            font-weight:
                800;

            margin-bottom:
                15px;

        }


        /* =====================================================
           INFO
        ===================================================== */

        .choice-info {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                10px;

            margin-bottom:
                17px;

        }


        .choice-info-box {

            background:
                #f8f8f8;

            border-radius:
                10px;

            padding:
                11px;

            text-align:
                center;

        }


        .choice-info-box strong {

            display:
                block;

            font-size:
                18px;

        }


        .choice-info-box span {

            display:
                block;

            color:
                #888;

            font-size:
                11px;

            margin-top:
                3px;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .btn-manage-choice {

            display:
                block;

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                12px;

            background:
                #e87518;

            color:
                white;

            text-align:
                center;

            border-radius:
                11px;

            font-size:
                13px;

            font-weight:
                800;

            text-decoration:
                none;

        }


        .btn-manage-choice:hover {

            background:
                #d76510;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .menu-status {

            display:
                inline-block;

            padding:
                5px 9px;

            border-radius:
                20px;

            font-size:
                10px;

            font-weight:
                700;

            margin-left:
                5px;

        }


        .menu-status.available {

            background:
                #e8f7ed;

            color:
                #218838;

        }


        .menu-status.unavailable {

            background:
                #ffe8e8;

            color:
                #c62828;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-choice {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            padding:
                60px 20px;

            text-align:
                center;

            color:
                #777;

        }


        .empty-choice-icon {

            font-size:
                60px;

            margin-bottom:
                15px;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (
            max-width: 700px
        ) {

            .choice-header {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .menu-choice-grid {

                grid-template-columns:
                    1fr;

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


            <a
                href="../dashboard.php"
            >

                <span>
                    📊
                </span>

                <span>
                    Dashboard
                </span>

            </a>



            <a
                href="../menu/index.php"
            >

                <span>
                    🍛
                </span>

                <span>
                    Menu Makanan
                </span>

            </a>



            <a
                href="../kategori/index.php"
            >

                <span>
                    📂
                </span>

                <span>
                    Kategori
                </span>

            </a>



            <a
                href="../pesanan/index.php"
            >

                <span>
                    🛒
                </span>

                <span>
                    Pesanan
                </span>

            </a>



            <a
                href="../pelanggan/index.php"
            >

                <span>
                    👥
                </span>

                <span>
                    Pelanggan
                </span>

            </a>



            <a
                href="../pembayaran/index.php"
            >

                <span>
                    💰
                </span>

                <span>
                    Pembayaran
                </span>

            </a>



            <a
                href="../laporan/index.php"
            >

                <span>
                    📈
                </span>

                <span>
                    Laporan
                </span>

            </a>



            <a
                href="index.php"
                class="active"
            >

                <span>
                    ⚙️
                </span>

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


        <header class="page-header choice-header">


            <div>

                <h1>
                    Pilihan Menu
                </h1>

                <p>
                    Kelola bagian ayam, sambal,
                    topping, ukuran, dan pilihan
                    lainnya untuk setiap menu.
                </p>

            </div>


        </header>



        <!-- =================================================
             MENU LIST
        ================================================= -->

        <?php if (
            $result &&
            $result->num_rows > 0
        ): ?>


            <div
                class="menu-choice-grid"
            >


                <?php while (
                    $row =
                    $result->fetch_assoc()
                ): ?>


                    <div
                        class="menu-choice-card"
                    >


                        <!-- FOTO -->

                        <div
                            class="menu-choice-image"
                        >

                            <?php if (
                                !empty(
                                    $row['foto']
                                )
                            ): ?>

                                <img
                                    src="../../assets/uploads/menu/<?= htmlspecialchars(
                                        $row['foto']
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $row['nama_menu']
                                    ); ?>"
                                >

                            <?php else: ?>

                                <div
                                    class="menu-choice-no-image"
                                >
                                    🍛
                                </div>

                            <?php endif; ?>

                        </div>



                        <!-- CONTENT -->

                        <div
                            class="menu-choice-content"
                        >


                            <span
                                class="menu-choice-category"
                            >

                                <?= htmlspecialchars(
                                    $row['nama_kategori']
                                    ?? 'Tanpa Kategori'
                                ); ?>

                            </span>



                            <h3>

                                <?= htmlspecialchars(
                                    $row['nama_menu']
                                ); ?>


                                <?php if (
                                    $row['status']
                                    ===
                                    'tersedia'
                                ): ?>

                                    <span
                                        class="menu-status available"
                                    >
                                        Tersedia
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="menu-status unavailable"
                                    >
                                        Habis
                                    </span>

                                <?php endif; ?>

                            </h3>



                            <div
                                class="menu-choice-price"
                            >

                                Rp<?= number_format(
                                    floatval(
                                        $row['harga']
                                    ),
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>



                            <!-- INFO -->

                            <div
                                class="choice-info"
                            >


                                <div
                                    class="choice-info-box"
                                >

                                    <strong>

                                        <?= intval(
                                            $row[
                                                'jumlah_kelompok'
                                            ]
                                        ); ?>

                                    </strong>

                                    <span>
                                        Kelompok
                                    </span>

                                </div>



                                <div
                                    class="choice-info-box"
                                >

                                    <strong>

                                        <?= intval(
                                            $row[
                                                'jumlah_pilihan'
                                            ]
                                        ); ?>

                                    </strong>

                                    <span>
                                        Pilihan
                                    </span>

                                </div>


                            </div>



                            <!-- BUTTON -->

                            <a
                                href="kelompok.php?menu_id=<?= intval(
                                    $row['id']
                                ); ?>"
                                class="btn-manage-choice"
                            >

                                ⚙️ Kelola Pilihan

                            </a>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div
                class="empty-choice"
            >

                <div
                    class="empty-choice-icon"
                >
                    🍛
                </div>


                <strong>
                    Belum Ada Menu
                </strong>


                <p>
                    Tambahkan menu terlebih dahulu
                    sebelum mengatur pilihan menu.
                </p>


            </div>


        <?php endif; ?>


    </main>


</div>


</body>

</html>