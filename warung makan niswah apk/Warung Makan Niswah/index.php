<?php

session_start();

require_once "includes/config.php";


// =====================================================
// HITUNG JUMLAH ITEM KERANJANG
// =====================================================

$cart =
    $_SESSION['cart'] ?? [];

$jumlahCart = 0;

foreach ($cart as $item) {

    $jumlahCart +=
        intval(
            $item['jumlah'] ?? 0
        );

}


// =====================================================
// MENU UNGGULAN
// =====================================================

$query = "
    SELECT *
    FROM menu
    WHERE status = 'tersedia'
    ORDER BY id DESC
    LIMIT 6
";

$result =
    $conn->query($query);

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
        Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        /* =================================================
           NAVBAR TAMBAHAN
        ================================================= */

        .navbar-inner {

            gap:
                10px;

        }


        .history-button {

            text-decoration:
                none;

        }


        @media (
            max-width: 700px
        ) {

            .history-button span {

                display:
                    none;

            }

        }


        /* =================================================
           MENU UNGGULAN
        ================================================= */

        .menu-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                22px;

        }


        .menu-card {

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


        .menu-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 12px 30px
                rgba(0,0,0,.08);

        }


        .menu-image {

            width:
                100%;

            height:
                220px;

            background:
                #f6eee5;

        }


        .menu-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .no-image {

            width:
                100%;

            height:
                100%;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                60px;

        }


        .menu-card-body {

            padding:
                18px;

        }


        .menu-card-body h3 {

            font-size:
                18px;

            margin:
                0;

        }


        .menu-card-body p {

            color:
                #888;

            font-size:
                13px;

            line-height:
                1.5;

            margin:
                7px 0 0;

            min-height:
                40px;

        }


        .menu-card-bottom {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                10px;

            margin-top:
                18px;

        }


        .menu-card-bottom strong {

            font-size:
                17px;

        }


        .menu-order-button {

            padding:
                10px 14px;

            background:
                #e87518;

            color:
                white;

            border-radius:
                10px;

            font-size:
                12px;

            font-weight:
                800;

            text-decoration:
                none;

        }


        .menu-order-button:hover {

            background:
                #d76510;

        }


        /* =================================================
           EMPTY MENU
        ================================================= */

        .customer-empty {

            grid-column:
                1 / -1;

            text-align:
                center;

            padding:
                60px 20px;

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

        }


        .customer-empty:first-letter {

            font-size:
                45px;

        }


        .customer-empty h3 {

            margin-top:
                10px;

        }


        .customer-empty p {

            color:
                #888;

            font-size:
                13px;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (
            max-width: 800px
        ) {

            .menu-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (
            max-width: 550px
        ) {

            .menu-grid {

                grid-template-columns:
                    1fr;

            }


            .menu-image {

                height:
                    230px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
====================================================== -->

<header
    class="customer-navbar"
>

    <div
        class="customer-container navbar-inner"
    >


        <!-- LOGO -->

        <a
            href="index.php"
            class="customer-logo"
        >

            🍛

            <span>
                Niswah
            </span>

        </a>



        <!-- RIWAYAT PESANAN -->

        <a
            href="riwayat-pesanan.php"
            class="cart-button history-button"
        >

            📜

            <span>
                Riwayat Pesanan
            </span>

        </a>


        <!-- KERANJANG -->

        <a
            href="keranjang.php"
            class="cart-button"
        >

            🛒

            <span>
                Keranjang
            </span>


            <!--
                ANGKA DIAMBIL DARI SESSION
            -->

            <b id="cartCount">

                <?= $jumlahCart; ?>

            </b>

        </a>


    </div>

</header>



<!-- =====================================================
     HERO
====================================================== -->

<section
    class="customer-hero"
>

    <div
        class="customer-container hero-content"
    >


        <span
            class="hero-label"
        >

            🍽️ Warung Makan Niswah

        </span>



        <h1>

            Makan Enak,

            <br>

            <span>
                Tinggal Pesan.
            </span>

        </h1>



        <p>

            Pesan makanan dari mana saja.
            Nggak perlu antre dan nggak perlu
            datang langsung ke kantin.

        </p>



        <div class="hero-buttons">

    <a 
        href="menu.php" 
        class="hero-button"
    >

        🍛 Pesan Sekarang →

    </a>


    <a 
        href="status-pesanan.php" 
        class="hero-status-button"
    >

        📦 Cek Status Pesanan

    </a>

</div>


    </div>

</section>



<!-- =====================================================
     MENU UNGGULAN
====================================================== -->

<section
    class="customer-section"
>

    <div
        class="customer-container"
    >


        <!-- HEADER -->

        <div
            class="section-heading"
        >


            <div>

                <span>

                    🍴 Pilihan Hari Ini

                </span>


                <h2>

                    Menu Favorit

                </h2>

            </div>



            <a
                href="menu.php"
            >

                Lihat Semua →

            </a>


        </div>



        <!-- GRID -->

        <div
            class="menu-grid"
        >


            <?php if (
                $result &&
                $result->num_rows > 0
            ): ?>


                <?php while (
                    $menu =
                    $result->fetch_assoc()
                ): ?>


                    <div
                        class="menu-card"
                    >


                        <!-- =========================
                             GAMBAR
                        ========================== -->

                        <div
                            class="menu-image"
                        >


                            <?php if (
                                !empty(
                                    $menu['foto']
                                )
                            ): ?>


                                <img
                                    src="assets/uploads/menu/<?= htmlspecialchars(
                                        $menu['foto']
                                    ); ?>"
                                    alt="<?= htmlspecialchars(
                                        $menu['nama_menu']
                                    ); ?>"
                                >


                            <?php else: ?>


                                <div
                                    class="no-image"
                                >

                                    🍛

                                </div>


                            <?php endif; ?>


                        </div>



                        <!-- =========================
                             CONTENT
                        ========================== -->

                        <div
                            class="menu-card-body"
                        >


                            <h3>

                                <?= htmlspecialchars(
                                    $menu[
                                        'nama_menu'
                                    ]
                                ); ?>

                            </h3>



                            <p>

                                <?= htmlspecialchars(
                                    $menu[
                                        'deskripsi'
                                    ] ?? ''
                                ); ?>

                            </p>



                            <!-- =========================
                                 BOTTOM
                            ========================== -->

                            <div
                                class="menu-card-bottom"
                            >


                                <strong>

                                    Rp<?= number_format(
                                        $menu[
                                            'harga'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>



                                <a
                                    href="detail-menu.php?id=<?= (int) $menu['id']; ?>"
                                    class="menu-order-button"
                                >

                                    + Pesan

                                </a>


                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- =========================
                     MENU KOSONG
                ========================== -->

                <div
                    class="customer-empty"
                >

                    🍛


                    <h3>

                        Menu belum tersedia

                    </h3>


                    <p>

                        Silakan kembali lagi nanti.

                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>

</section>



<!-- =====================================================
     INFO
====================================================== -->

<section
    class="customer-info"
>

    <div
        class="customer-container info-grid"
    >


        <!-- PESAN CEPAT -->

        <div>

            <span>
                ⚡
            </span>


            <h3>

                Pesan Cepat

            </h3>


            <p>

                Pilih makanan dan langsung
                kirim pesanan.

            </p>

        </div>



        <!-- ANTAR -->

        <div>

            <span>
                📍
            </span>


            <h3>

                Antar ke Lokasi

            </h3>


            <p>

                Masukkan lokasi kantor atau
                tempat tujuan.

            </p>

        </div>



        <!-- PANTAU -->

        <div>

            <span>
                📱
            </span>


            <h3>

                Pantau Pesanan

            </h3>


            <p>

                Lihat status pesanan setelah
                melakukan checkout.

            </p>

        </div>


    </div>

</section>



<!-- =====================================================
     FOOTER
====================================================== -->

<footer
    class="customer-footer"
>

    <div
        class="customer-container"
    >


        <strong>

            🍛 Warung Makan Niswah

        </strong>


        <p>

            Makan enak tanpa perlu antre.

        </p>


    </div>

</footer>


</body>

</html>