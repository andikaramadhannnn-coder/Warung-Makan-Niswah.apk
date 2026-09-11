<?php

session_start();


// =====================================================
// SIAPKAN CART
// =====================================================

if (
    !isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart'])
) {

    $_SESSION['cart'] = [];

}


// =====================================================
// PROSES CART
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $action =
        $_POST['action'] ?? '';

    $cartKey =
        $_POST['cart_key'] ?? '';


    // =================================================
    // CARI ITEM
    // =================================================

    $foundKey = null;

    foreach (
        $_SESSION['cart']
        as $key => $item
    ) {

        if (
            (string)$key ===
            (string)$cartKey
        ) {

            $foundKey = $key;

            break;

        }


        if (
            isset($item['cart_key']) &&
            (string)$item['cart_key'] ===
            (string)$cartKey
        ) {

            $foundKey = $key;

            break;

        }

    }


    // =================================================
    // ITEM DITEMUKAN
    // =================================================

    if (
        $foundKey !== null
    ) {


        // =============================================
        // TAMBAH
        // =============================================

        if (
            $action === 'plus'
        ) {

            $jumlah =
                intval(
                    $_SESSION['cart']
                    [$foundKey]
                    ['jumlah']
                    ?? 1
                );


            $jumlah++;


            $_SESSION['cart']
                [$foundKey]
                ['jumlah'] =
                $jumlah;

        }


        // =============================================
        // KURANG
        // =============================================

        elseif (
            $action === 'minus'
        ) {

            $jumlah =
                intval(
                    $_SESSION['cart']
                    [$foundKey]
                    ['jumlah']
                    ?? 1
                );


            $jumlah--;


            if (
                $jumlah <= 0
            ) {

                unset(
                    $_SESSION['cart']
                    [$foundKey]
                );

            }

            else {

                $_SESSION['cart']
                    [$foundKey]
                    ['jumlah'] =
                    $jumlah;

            }

        }


        // =============================================
        // HAPUS
        // =============================================

        elseif (
            $action === 'remove'
        ) {

            unset(
                $_SESSION['cart']
                [$foundKey]
            );

        }

    }


    // =================================================
    // KEMBALI
    // =================================================

    header(
        "Location: keranjang.php"
    );

    exit;

}


// =====================================================
// AMBIL CART
// =====================================================

$cart =
    $_SESSION['cart'];


// =====================================================
// HITUNG TOTAL
// =====================================================

$total = 0;

$jumlahItem = 0;


// =====================================================
// HTML
// =====================================================

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
        Keranjang | Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        .cart-page {

            padding:
                30px 0 70px;

        }


        .cart-back {

            padding-top:
                18px;

        }


        .cart-back a {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            color:
                #777;

            font-size:
                13px;

            font-weight:
                700;

            text-decoration:
                none;

            transition:
                .2s;

        }


        .cart-back a:hover {

            color:
                #e87518;

            transform:
                translateX(-2px);

        }


        .cart-title {

            margin-bottom:
                25px;

        }


        .cart-title h1 {

            font-size:
                30px;

            margin:
                0;

        }


        .cart-title p {

            color:
                #888;

            margin-top:
                6px;

            font-size:
                13px;

        }


        .cart-layout {

            display:
                grid;

            grid-template-columns:
                1fr 340px;

            gap:
                22px;

        }


        .cart-card,
        .summary-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            overflow:
                hidden;

        }


        .cart-item {

            padding:
                20px;

            display:
                flex;

            gap:
                15px;

            border-bottom:
                1px solid #eee;

        }


        .cart-item:last-child {

            border-bottom:
                0;

        }


        .cart-item-image {

            width:
                90px;

            height:
                90px;

            flex-shrink:
                0;

            background:
                #f6eee5;

            border-radius:
                13px;

            overflow:
                hidden;

        }


        .cart-item-image img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .cart-item-image div {

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
                34px;

        }


        .cart-item-content {

            flex:
                1;

            min-width:
                0;

        }


        .cart-item-content h3 {

            font-size:
                17px;

            margin:
                0;

        }


        .cart-item-price {

            color:
                #e87518;

            font-weight:
                700;

            margin-top:
                5px;

            font-size:
                13px;

        }


        .cart-options {

            margin-top:
                10px;

            color:
                #777;

            font-size:
                12px;

            line-height:
                1.8;

        }


        .cart-option-group {

            margin-bottom:
                3px;

        }


        .cart-option-group:last-child {

            margin-bottom:
                0;

        }


        .cart-option-group strong {

            color:
                #333;

        }


        .cart-option-price {

            color:
                #e87518;

            margin-left:
                4px;

        }


        .cart-note {

            margin-top:
                8px;

            color:
                #999;

            font-size:
                12px;

            line-height:
                1.5;

        }


        .cart-item-right {

            min-width:
                150px;

            display:
                flex;

            flex-direction:
                column;

            align-items:
                flex-end;

        }


        .cart-item-right > strong {

            font-size:
                15px;

        }


        .quantity-control {

            display:
                flex;

            align-items:
                center;

            margin-top:
                10px;

            border:
                1px solid #ddd;

            border-radius:
                9px;

            overflow:
                hidden;

        }


        .quantity-control form {

            margin:
                0;

        }


        .quantity-control button {

            width:
                34px;

            height:
                32px;

            border:
                0;

            background:
                #f7f7f7;

            cursor:
                pointer;

            font-size:
                17px;

            font-weight:
                700;

        }


        .quantity-control button:hover {

            background:
                #fff0df;

            color:
                #e87518;

        }


        .quantity-number {

            min-width:
                38px;

            text-align:
                center;

            font-size:
                13px;

            font-weight:
                700;

        }


        .remove-button {

            margin-top:
                8px;

            padding:
                5px 8px;

            border:
                0;

            background:
                transparent;

            color:
                #dc3545;

            font-size:
                11px;

            cursor:
                pointer;

        }


        .remove-button:hover {

            text-decoration:
                underline;

        }


        .summary-card {

            padding:
                22px;

            height:
                fit-content;

            position:
                sticky;

            top:
                85px;

        }


        .summary-card h3 {

            margin:
                0 0 20px;

        }


        .summary-row {

            display:
                flex;

            justify-content:
                space-between;

            padding:
                9px 0;

            font-size:
                13px;

        }


        .summary-row span {

            color:
                #888;

        }


        .summary-total {

            border-top:
                1px solid #eee;

            margin-top:
                10px;

            padding-top:
                17px;

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

        }


        .summary-total span {

            color:
                #777;

        }


        .summary-total strong {

            font-size:
                21px;

        }


        .checkout-button {

            display:
                block;

            width:
                100%;

            margin-top:
                20px;

            padding:
                14px;

            background:
                #e87518;

            color:
                white;

            text-align:
                center;

            border-radius:
                12px;

            font-weight:
                800;

            text-decoration:
                none;

            box-sizing:
                border-box;

        }


        .checkout-button:hover {

            background:
                #d76510;

        }


        .continue-button {

            display:
                block;

            text-align:
                center;

            margin-top:
                12px;

            color:
                #e87518;

            font-size:
                13px;

            font-weight:
                700;

            text-decoration:
                none;

        }


        .cart-empty {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            text-align:
                center;

            padding:
                70px 20px;

        }


        .cart-empty-icon {

            font-size:
                55px;

        }


        .cart-empty h2 {

            margin-top:
                15px;

        }


        .cart-empty p {

            margin-top:
                7px;

            color:
                #888;

            font-size:
                13px;

        }


        .empty-menu-button {

            display:
                inline-block;

            margin-top:
                20px;

            padding:
                13px 20px;

            background:
                #e87518;

            color:
                white;

            border-radius:
                11px;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                800;

        }


        @media (
            max-width: 800px
        ) {

            .cart-layout {

                grid-template-columns:
                    1fr;

            }


            .summary-card {

                position:
                    static;

            }

        }


        @media (
            max-width: 550px
        ) {

            .cart-item {

                padding:
                    15px;

                flex-wrap:
                    wrap;

            }


            .cart-item-image {

                width:
                    70px;

                height:
                    70px;

            }


            .cart-item-right {

                width:
                    100%;

                min-width:
                    0;

                flex-direction:
                    row;

                align-items:
                    center;

                justify-content:
                    space-between;

                padding-left:
                    85px;

                box-sizing:
                    border-box;

            }

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

        <a
            href="index.php"
            class="customer-logo"
        >

            🍛

            <span>
                Niswah
            </span>

        </a>


        <a
            href="keranjang.php"
            class="cart-button"
        >

            🛒

            <span>
                Keranjang
            </span>

            <b>

                <?php

                $jumlahNavbar = 0;


                foreach (
                    $cart as $item
                ) {

                    $jumlahNavbar +=
                        intval(
                            $item['jumlah']
                            ?? 0
                        );

                }


                echo $jumlahNavbar;

                ?>

            </b>

        </a>

    </div>

</header>


<!-- =====================================================
     BACK
====================================================== -->

<div
    class="customer-container cart-back"
>

    <a
        href="index.php"
    >

        ← Kembali ke Beranda

    </a>

</div>


<!-- =====================================================
     MAIN
====================================================== -->

<main class="cart-page">

    <div
        class="customer-container"
    >


        <div class="cart-title">

            <h1>
                Keranjang
            </h1>

            <p>
                Cek kembali pesanan kamu sebelum checkout.
            </p>

        </div>


        <?php if (
            count($cart) > 0
        ): ?>


            <div class="cart-layout">


                <!-- =================================================
                     ITEM
                ================================================== -->

                <div class="cart-card">


                    <?php foreach (
                        $cart as $key => $item
                    ): ?>


                        <?php

                        // ==========================================
                        // DATA DASAR
                        // ==========================================

                        $cartKey =
                            $item['cart_key']
                            ?? $key;


                        $namaMenu =
                            $item['nama_menu']
                            ?? 'Menu';


                        $jumlah =
                            intval(
                                $item['jumlah']
                                ?? 1
                            );


                        if (
                            $jumlah < 1
                        ) {

                            $jumlah = 1;

                        }


                        // ==========================================
                        // HARGA
                        // ==========================================

                        $hargaSatuan =
                            floatval(
                                $item['harga']
                                ??
                                $item['harga_satuan']
                                ??
                                0
                            );


                        $subtotal =
                            $hargaSatuan *
                            $jumlah;


                        // ==========================================
                        // GAMBAR
                        // ==========================================

                        $gambar =
                            $item['foto']
                            ??
                            $item['gambar']
                            ??
                            '';


                        // ==========================================
                        // CATATAN
                        // ==========================================

                        $catatan =
                            $item['catatan']
                            ??
                            '';


                        // ==========================================
                        // PILIHAN BARU
                        // ==========================================

                        $pilihan =
                            $item['pilihan']
                            ??
                            [];


                        if (
                            !is_array($pilihan)
                        ) {

                            $pilihan = [];

                        }


                        // ==========================================
                        // TOTAL
                        // ==========================================

                        $jumlahItem +=
                            $jumlah;

                        $total +=
                            $subtotal;

                        ?>


                        <div class="cart-item">


                            <!-- =================================
                                 GAMBAR
                            ================================== -->

                            <div
                                class="cart-item-image"
                            >

                                <?php if (
                                    !empty($gambar)
                                ): ?>

                                    <img
                                        src="assets/uploads/menu/<?= htmlspecialchars($gambar); ?>"
                                        alt="<?= htmlspecialchars($namaMenu); ?>"
                                    >

                                <?php else: ?>

                                    <div>
                                        🍛
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- =================================
                                 INFO
                            ================================== -->

                            <div
                                class="cart-item-content"
                            >

                                <h3>

                                    <?= htmlspecialchars(
                                        $namaMenu
                                    ); ?>

                                </h3>


                                <div
                                    class="cart-item-price"
                                >

                                    Rp<?= number_format(
                                        $hargaSatuan,
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                    / item

                                </div>


                                <!-- =================================
                                     VARIAN MENU
                                ================================== -->

                                <?php if (!empty($item['varian'])): ?>

                                    <div
                                        class="cart-options"
                                    >

                                        <div
                                            class="cart-option-group"
                                        >

                                            🍚 Penyajian:

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $item['varian']
                                                ); ?>
                                            </strong>

                                        </div>

                                    </div>

                                <?php endif; ?>


                                <!-- =================================
                                     PILIHAN DINAMIS
                                ================================== -->

                                <?php if (
                                    count($pilihan) > 0
                                ): ?>

                                    <div
                                        class="cart-options"
                                    >

                                        <?php foreach (
                                            $pilihan
                                            as $pilihanItem
                                        ): ?>


                                            <?php

                                            $namaKelompok =
                                                $pilihanItem[
                                                    'kelompok'
                                                ]
                                                ??
                                                'Pilihan';


                                            $namaPilihan =
                                                $pilihanItem[
                                                    'nama_pilihan'
                                                ]
                                                ??
                                                '';


                                            $hargaTambahan =
                                                floatval(
                                                    $pilihanItem[
                                                        'harga_tambahan'
                                                    ]
                                                    ?? 0
                                                );

                                            ?>


                                            <div
                                                class="cart-option-group"
                                            >

                                                •
                                                <?= htmlspecialchars(
                                                    $namaKelompok
                                                ); ?>:

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $namaPilihan
                                                    ); ?>

                                                </strong>


                                                <?php if (
                                                    $hargaTambahan > 0
                                                ): ?>

                                                    <span
                                                        class="cart-option-price"
                                                    >

                                                        +Rp<?= number_format(
                                                            $hargaTambahan,
                                                            0,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    </span>

                                                <?php endif; ?>

                                            </div>


                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>


                                <!-- =================================
                                     CATATAN
                                ================================== -->

                                <?php if (
                                    $catatan !== ''
                                ): ?>

                                    <div
                                        class="cart-note"
                                    >

                                        📝

                                        <?= htmlspecialchars(
                                            $catatan
                                        ); ?>

                                    </div>

                                <?php endif; ?>


                            </div>


                            <!-- =================================
                                 KANAN
                            ================================== -->

                            <div
                                class="cart-item-right"
                            >


                                <strong>

                                    Rp<?= number_format(
                                        $subtotal,
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>


                                <!-- =================================
                                     QUANTITY
                                ================================== -->

                                <div
                                    class="quantity-control"
                                >


                                    <!-- MINUS -->

                                    <form
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="minus"
                                        >

                                        <input
                                            type="hidden"
                                            name="cart_key"
                                            value="<?= htmlspecialchars($cartKey); ?>"
                                        >

                                        <button
                                            type="submit"
                                            title="Kurangi"
                                        >

                                            −

                                        </button>

                                    </form>


                                    <span
                                        class="quantity-number"
                                    >

                                        <?= $jumlah; ?>

                                    </span>


                                    <!-- PLUS -->

                                    <form
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="plus"
                                        >

                                        <input
                                            type="hidden"
                                            name="cart_key"
                                            value="<?= htmlspecialchars($cartKey); ?>"
                                        >

                                        <button
                                            type="submit"
                                            title="Tambah"
                                        >

                                            +

                                        </button>

                                    </form>


                                </div>


                                <!-- =================================
                                     HAPUS
                                ================================== -->

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Hapus menu ini dari keranjang?');"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="remove"
                                    >

                                    <input
                                        type="hidden"
                                        name="cart_key"
                                        value="<?= htmlspecialchars($cartKey); ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="remove-button"
                                    >

                                        🗑️ Hapus

                                    </button>

                                </form>


                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


                <!-- =================================================
                     RINGKASAN
                ================================================== -->

                <div
                    class="summary-card"
                >

                    <h3>
                        Ringkasan Pesanan
                    </h3>


                    <div
                        class="summary-row"
                    >

                        <span>
                            Jumlah Item
                        </span>

                        <strong>
                            <?= $jumlahItem; ?>
                        </strong>

                    </div>


                    <div
                        class="summary-total"
                    >

                        <span>
                            Total
                        </span>

                        <strong>

                            Rp<?= number_format(
                                $total,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </strong>

                    </div>


                    <a
                        href="checkout.php"
                        class="checkout-button"
                    >

                        Lanjut Checkout →

                    </a>


                    <a
                        href="menu.php"
                        class="continue-button"
                    >

                        ← Tambah Menu Lagi

                    </a>


                </div>


            </div>


        <?php else: ?>


            <!-- =================================================
                 CART KOSONG
            ================================================== -->

            <div
                class="cart-empty"
            >

                <div
                    class="cart-empty-icon"
                >
                    🛒
                </div>


                <h2>
                    Keranjang Masih Kosong
                </h2>


                <p>
                    Yuk pilih makanan favorit kamu.
                </p>


                <a
                    href="menu.php"
                    class="empty-menu-button"
                >

                    Lihat Menu

                </a>


            </div>


        <?php endif; ?>


    </div>

</main>


</body>

</html>