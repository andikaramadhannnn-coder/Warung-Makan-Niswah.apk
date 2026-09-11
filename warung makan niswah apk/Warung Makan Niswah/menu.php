<?php

session_start();

require_once "includes/config.php";


// =====================================================
// HITUNG JUMLAH ITEM DI KERANJANG
// =====================================================

$cart =
    $_SESSION['cart'] ?? [];

$jumlahCart = 0;

foreach (
    $cart as $item
) {

    $jumlahCart +=
        intval(
            $item['jumlah'] ?? 0
        );

}


// =====================================================
// AMBIL SEMUA MENU
// =====================================================

$query = "
    SELECT *
    FROM menu
    WHERE status = 'tersedia'
    ORDER BY id DESC
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
        Menu | Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        /* =================================================
           MENU PAGE
        ================================================= */

        .menu-page {

            padding:
                40px 0 70px;

        }


        .menu-page-header {

            margin-bottom:
                30px;

        }


        .menu-page-header span {

            color:
                #e87518;

            font-size:
                13px;

            font-weight:
                700;

        }


        .menu-page-header h1 {

            margin-top:
                7px;

            font-size:
                34px;

        }


        .menu-page-header p {

            margin-top:
                8px;

            color:
                #888;

            font-size:
                14px;

        }


        /* =================================================
           FILTER
        ================================================= */

        .menu-filter {

            display:
                flex;

            gap:
                10px;

            flex-wrap:
                wrap;

            margin-bottom:
                25px;

        }


        .filter-button {

            padding:
                9px 15px;

            border:
                1px solid #ddd;

            background:
                white;

            border-radius:
                10px;

            font-size:
                13px;

            cursor:
                pointer;

        }


        .filter-button.active {

            background:
                #e87518;

            border-color:
                #e87518;

            color:
                white;

        }


        /* =================================================
           MENU GRID
        ================================================= */

        .menu-grid-full {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                22px;

        }


        .customer-menu-card {

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


        .customer-menu-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 12px 30px
                rgba(0,0,0,.08);

        }


        /* =================================================
           IMAGE
        ================================================= */

        .customer-menu-image {

            width:
                100%;

            height:
                220px;

            background:
                #f6eee5;

        }


        .customer-menu-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .customer-no-image {

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


        /* =================================================
           BODY
        ================================================= */

        .customer-menu-body {

            padding:
                18px;

        }


        .customer-menu-body h3 {

            font-size:
                18px;

        }


        .customer-menu-body p {

            margin-top:
                7px;

            min-height:
                40px;

            color:
                #888;

            font-size:
                13px;

            line-height:
                1.5;

        }


        /* =================================================
           BOTTOM
        ================================================= */

        .customer-menu-bottom {

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


        .customer-menu-price {

            font-size:
                17px;

            font-weight:
                800;

        }


        .customer-menu-button {

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


        .customer-menu-button:hover {

            background:
                #d76510;

        }


        /* =================================================
           EMPTY
        ================================================= */

        .customer-empty-menu {

            grid-column:
                1 / -1;

            padding:
                70px 20px;

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            text-align:
                center;

        }


        .customer-empty-menu div {

            font-size:
                55px;

        }


        .customer-empty-menu h3 {

            margin-top:
                12px;

        }


        .customer-empty-menu p {

            margin-top:
                7px;

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

            .menu-grid-full {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (
            max-width: 550px
        ) {

            .menu-page-header h1 {

                font-size:
                    28px;

            }


            .menu-grid-full {

                grid-template-columns:
                    1fr;

            }


            .customer-menu-image {

                height:
                    230px;

            }

        }
        /* =================================================
   TOMBOL KEMBALI
================================================= */

.page-back {
    padding-top: 18px;
}

.page-back a {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    color: #777;
    font-size: 13px;
    font-weight: 700;

    text-decoration: none;

    transition: .2s;
}

.page-back a:hover {
    color: #e87518;
    transform: translateX(-2px);
}

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
====================================================== -->

<header class="customer-navbar">

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



        <!-- KERANJANG -->

        <a
            href="keranjang.php"
            class="cart-button"
        >

            🛒

            <span>
                Keranjang
            </span>

            <b id="cartCount">

                <?= $jumlahCart; ?>

            </b>

        </a>


    </div>

</header>

<!-- =====================================================
     TOMBOL KEMBALI
===================================================== -->

<div class="customer-container page-back">

    <a href="index.php">
        ← Kembali ke Beranda
    </a>

</div>


<!-- =====================================================
     MAIN MENU
====================================================== -->

<main class="menu-page">


    <div
        class="customer-container"
    >


        <!-- HEADER -->

        <div
            class="menu-page-header"
        >

            <span>

                🍽️ WARUNG MAKAN NISWAH

            </span>


            <h1>

                Semua Menu

            </h1>


            <p>

                Pilih makanan favorit kamu,
                lalu lanjutkan ke checkout.

            </p>

        </div>



        <!-- =================================================
             FILTER KATEGORI
        ================================================== -->

        <div
            class="menu-filter"
        >


            <!-- SEMUA -->

            <button
                type="button"
                class="filter-button active"
                onclick="
                    filterMenu(
                        'semua',
                        this
                    )
                "
            >

                Semua

            </button>



            <?php

            $kategoriResult =
                $conn->query("
                    SELECT *
                    FROM kategori
                    ORDER BY nama_kategori ASC
                ");

            ?>


            <?php if (
                $kategoriResult &&
                $kategoriResult->num_rows > 0
            ): ?>


                <?php while (
                    $kategori =
                    $kategoriResult->fetch_assoc()
                ): ?>


                    <button
                        type="button"
                        class="filter-button"
                        onclick="
                            filterMenu(
                                'kategori-<?= (int) $kategori['id']; ?>',
                                this
                            )
                        "
                    >

                        <?= htmlspecialchars(
                            $kategori[
                                'nama_kategori'
                            ]
                        ); ?>

                    </button>


                <?php endwhile; ?>


            <?php endif; ?>


        </div>



        <!-- =================================================
             GRID MENU
        ================================================== -->

        <div
            class="menu-grid-full"
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
                        class="customer-menu-card"
                        data-kategori="
                            kategori-<?= (int) $menu['kategori_id']; ?>
                        "
                    >


                        <!-- GAMBAR -->

                        <div
                            class="customer-menu-image"
                        >


                            <?php if (
                                !empty(
                                    $menu['foto']
                                )
                            ): ?>


                                <img
                                    src="
                                        assets/uploads/menu/<?= htmlspecialchars(
                                            $menu['foto']
                                        ); ?>
                                    "
                                    alt="
                                        <?= htmlspecialchars(
                                            $menu['nama_menu']
                                        ); ?>
                                    "
                                >


                            <?php else: ?>


                                <div
                                    class="customer-no-image"
                                >

                                    🍛

                                </div>


                            <?php endif; ?>


                        </div>



                        <!-- CONTENT -->

                        <div
                            class="customer-menu-body"
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



                            <div
                                class="customer-menu-bottom"
                            >


                                <span
                                    class="customer-menu-price"
                                >

                                    Rp<?= number_format(
                                        $menu[
                                            'harga'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </span>



                                <a
                                    href="
                                        detail-menu.php?id=<?= (int) $menu['id']; ?>
                                    "
                                    class="customer-menu-button"
                                >

                                    + Pesan

                                </a>


                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- MENU KOSONG -->

                <div
                    class="customer-empty-menu"
                >

                    <div>
                        🍛
                    </div>


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

</main>



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



<!-- =====================================================
     JAVASCRIPT FILTER
====================================================== -->

<script>

function filterMenu(
    kategori,
    button
) {


    // =========================
    // RESET BUTTON
    // =========================

    document
        .querySelectorAll(
            '.filter-button'
        )
        .forEach(
            function(item) {

                item.classList.remove(
                    'active'
                );

            }
        );


    // =========================
    // ACTIVE BUTTON
    // =========================

    button.classList.add(
        'active'
    );


    // =========================
    // FILTER MENU
    // =========================

    document
        .querySelectorAll(
            '.customer-menu-card'
        )
        .forEach(
            function(card) {


                if (
                    kategori === 'semua'
                    ||
                    card.dataset.kategori.trim()
                        === kategori
                ) {

                    card.style.display =
                        '';

                } else {

                    card.style.display =
                        'none';

                }

            }
        );

}

</script>


</body>

</html>