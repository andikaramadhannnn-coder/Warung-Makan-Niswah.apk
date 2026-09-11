<?php

session_start();


// =====================================================
// AMBIL KERANJANG
// =====================================================

$cart = $_SESSION['cart'] ?? [];


// =====================================================
// KALAU KERANJANG KOSONG
// =====================================================

if (empty($cart)) {

    header("Location: menu.php");

    exit;

}


// =====================================================
// HITUNG TOTAL
// =====================================================

$jumlahCart = 0;

$totalHarga = 0;


foreach ($cart as $item) {

    $jumlah =
        intval(
            $item['jumlah'] ?? 0
        );

    $harga =
        floatval(
            $item['harga'] ?? 0
        );


    $jumlahCart +=
        $jumlah;


    $totalHarga +=
        $harga * $jumlah;

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
        Checkout | Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        /* =====================================================
           CHECKOUT
        ===================================================== */

        .checkout-page {

            padding:
                30px 0 70px;

        }


        .checkout-back {

            margin-bottom:
                18px;

        }


        .checkout-back a {

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


        .checkout-back a:hover {

            color:
                #e87518;

            transform:
                translateX(-2px);

        }


        .checkout-header {

            margin-bottom:
                28px;

        }


        .checkout-header h1 {

            font-size:
                32px;

            margin:
                0;

        }


        .checkout-header p {

            margin-top:
                7px;

            color:
                #888;

        }


        .checkout-grid {

            display:
                grid;

            grid-template-columns:
                1.5fr 1fr;

            gap:
                25px;

            align-items:
                start;

        }


        .checkout-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                18px;

            padding:
                25px;

        }


        .checkout-card + .checkout-card {

            margin-top:
                20px;

        }


        .checkout-card h2 {

            font-size:
                19px;

            margin:
                0 0 20px;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {

            margin-bottom:
                18px;

        }


        .form-group:last-child {

            margin-bottom:
                0;

        }


        .form-group label {

            display:
                block;

            font-size:
                13px;

            font-weight:
                700;

            margin-bottom:
                7px;

        }


        .form-group input,
        .form-group textarea,
        .form-group select {

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                12px 13px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            outline:
                none;

            font-family:
                inherit;

            font-size:
                14px;

        }


        .form-group textarea {

            min-height:
                90px;

            resize:
                vertical;

        }


        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {

            border-color:
                #e87518;

        }


        .form-help {

            display:
                block;

            margin-top:
                5px;

            color:
                #999;

            font-size:
                11px;

        }


        /* =====================================================
           PAYMENT
        ===================================================== */

        .payment-list {

            display:
                flex;

            flex-direction:
                column;

            gap:
                10px;

        }


        .payment-option {

            position:
                relative;

        }


        .payment-option input {

            position:
                absolute;

            opacity:
                0;

        }


        .payment-label {

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                13px;

            border:
                1px solid #ddd;

            border-radius:
                11px;

            cursor:
                pointer;

            transition:
                .2s;

        }


        .payment-label:hover {

            border-color:
                #e87518;

        }


        .payment-option
        input:checked
        + .payment-label {

            border-color:
                #e87518;

            background:
                #fff3e5;

        }


        .payment-icon {

            width:
                40px;

            height:
                40px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                9px;

            background:
                #f7f7f7;

            font-size:
                20px;

        }


        .payment-text strong {

            display:
                block;

            font-size:
                13px;

        }


        .payment-text span {

            display:
                block;

            margin-top:
                3px;

            color:
                #888;

            font-size:
                11px;

        }


        /* =====================================================
           ORDER SUMMARY
        ===================================================== */

        .order-list {

            display:
                flex;

            flex-direction:
                column;

            gap:
                14px;

        }


        .order-item {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                15px;

            padding-bottom:
                14px;

            border-bottom:
                1px solid #eee;

        }


        .order-item:last-child {

            border-bottom:
                0;

            padding-bottom:
                0;

        }


        .order-item-name {

            font-weight:
                700;

            font-size:
                13px;

        }


        .order-item-detail {

            margin-top:
                6px;

            color:
                #888;

            font-size:
                11px;

            line-height:
                1.7;

        }


        .order-item-detail .option-line {

            display:
                block;

        }


        .order-item-detail .option-name {

            color:
                #444;

            font-weight:
                600;

        }


        .order-item-detail .option-price {

            color:
                #e87518;

        }


        .order-item-detail .variant-line {

            display:
                block;

            margin-bottom:
                4px;

        }


        .order-item-detail .note-line {

            display:
                block;

            margin-top:
                5px;

            color:
                #999;

        }


        .order-item-price {

            white-space:
                nowrap;

            font-size:
                13px;

            font-weight:
                800;

        }


        /* =====================================================
           TOTAL
        ===================================================== */

        .summary-divider {

            height:
                1px;

            background:
                #eee;

            margin:
                20px 0;

        }


        .summary-row {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            margin-bottom:
                10px;

        }


        .summary-row span {

            color:
                #777;

            font-size:
                13px;

        }


        .summary-row strong {

            font-size:
                14px;

        }


        .summary-total {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            margin-top:
                15px;

        }


        .summary-total span {

            font-weight:
                700;

        }


        .summary-total strong {

            color:
                #e87518;

            font-size:
                22px;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .submit-order {

            width:
                100%;

            border:
                0;

            margin-top:
                20px;

            padding:
                15px;

            border-radius:
                12px;

            background:
                #e87518;

            color:
                white;

            font-size:
                15px;

            font-weight:
                800;

            cursor:
                pointer;

        }


        .submit-order:hover {

            background:
                #d76510;

        }


        .back-cart {

            display:
                block;

            margin-top:
                12px;

            text-align:
                center;

            color:
                #e87518;

            font-size:
                13px;

            font-weight:
                700;

            text-decoration:
                none;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (
            max-width: 800px
        ) {

            .checkout-grid {

                grid-template-columns:
                    1fr;

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

                <?= $jumlahCart; ?>

            </b>

        </a>


    </div>

</header>



<!-- =====================================================
     CHECKOUT
====================================================== -->

<main
    class="checkout-page"
>

    <div
        class="customer-container"
    >


        <!-- BACK -->

        <div
            class="checkout-back"
        >

            <a
                href="keranjang.php"
            >

                ← Kembali ke Keranjang

            </a>

        </div>


        <!-- HEADER -->

        <div
            class="checkout-header"
        >

            <h1>
                Checkout
            </h1>


            <p>
                Isi data pengantaran dan
                pilih metode pembayaran.
            </p>

        </div>



        <form
            action="proses-checkout.php"
            method="POST"
        >


            <div
                class="checkout-grid"
            >


                <!-- =================================================
                     KOLOM KIRI
                ================================================== -->

                <div>


                    <!-- DATA PELANGGAN -->

                    <div
                        class="checkout-card"
                    >

                        <h2>
                            👤 Data Pemesan
                        </h2>


                        <div
                            class="form-group"
                        >

                            <label
                                for="nama"
                            >
                                Nama
                            </label>


                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                placeholder="Contoh: Andika"
                                required
                            >

                        </div>



                        <div
                            class="form-group"
                        >

                            <label
                                for="no_hp"
                            >
                                Nomor HP / WhatsApp
                            </label>


                            <input
                                type="tel"
                                id="no_hp"
                                name="no_hp"
                                placeholder="08xxxxxxxxxx"
                                required
                            >


                            <span
                                class="form-help"
                            >
                                Nomor ini digunakan
                                untuk menghubungi kamu
                                mengenai pesanan.
                            </span>

                        </div>


                    </div>



                    <!-- LOKASI -->

                    <div
                        class="checkout-card"
                    >

                        <h2>
                            📍 Lokasi Pengantaran
                        </h2>


                        <div
                            class="form-group"
                        >

                            <label
                                for="lokasi"
                            >
                                Lokasi / Nama Kantor
                            </label>


                            <input
                                type="text"
                                id="lokasi"
                                name="lokasi"
                                placeholder="Contoh: Kantor PT ABC"
                                required
                            >

                        </div>



                        <div
                            class="form-group"
                        >

                            <label
                                for="alamat"
                            >
                                Alamat / Detail Lokasi
                            </label>


                            <textarea
                                id="alamat"
                                name="alamat"
                                placeholder="Contoh: Gedung A lantai 3, ruang 302..."
                                required
                            ></textarea>


                            <span
                                class="form-help"
                            >
                                Semakin detail,
                                semakin mudah pesanan
                                ditemukan.
                            </span>

                        </div>


                        <div
                            class="form-group"
                        >

                            <label
                                for="catatan_pengantaran"
                            >
                                Catatan Pengantaran
                            </label>


                            <textarea
                                id="catatan_pengantaran"
                                name="catatan_pengantaran"
                                placeholder="Contoh: Titip ke resepsionis jika saya tidak ada."
                            ></textarea>

                        </div>


                    </div>



                    <!-- PEMBAYARAN -->

                    <div
                        class="checkout-card"
                    >

                        <h2>
                            💳 Metode Pembayaran
                        </h2>


                        <div
                            class="payment-list"
                        >


                            <!-- COD -->

                            <label
                                class="payment-option"
                            >

                                <input
                                    type="radio"
                                    name="metode_pembayaran"
                                    value="cod"
                                    checked
                                >


                                <span
                                    class="payment-label"
                                >

                                    <span
                                        class="payment-icon"
                                    >
                                        💵
                                    </span>


                                    <span
                                        class="payment-text"
                                    >

                                        <strong>
                                            Bayar di Tempat
                                        </strong>

                                        <span>
                                            Bayar saat pesanan diterima
                                        </span>

                                    </span>

                                </span>

                            </label>



                            <!-- QRIS -->

                            <label
                                class="payment-option"
                            >

                                <input
                                    type="radio"
                                    name="metode_pembayaran"
                                    value="qris"
                                >


                                <span
                                    class="payment-label"
                                >

                                    <span
                                        class="payment-icon"
                                    >
                                        📱
                                    </span>


                                    <span
                                        class="payment-text"
                                    >

                                        <strong>
                                            QRIS
                                        </strong>

                                        <span>
                                            Bayar menggunakan QRIS
                                        </span>

                                    </span>

                                </span>

                            </label>


                        </div>


                    </div>


                </div>



                <!-- =================================================
                     KOLOM KANAN
                ================================================== -->

                <div>


                    <div
                        class="checkout-card"
                    >

                        <h2>
                            🛒 Ringkasan Pesanan
                        </h2>



                        <div
                            class="order-list"
                        >


                            <?php foreach (
                                $cart as $item
                            ): ?>


                                <?php

                                $jumlah =
                                    intval(
                                        $item['jumlah']
                                        ?? 1
                                    );


                                $harga =
                                    floatval(
                                        $item['harga']
                                        ?? 0
                                    );


                                $subtotal =
                                    $harga *
                                    $jumlah;


                                $pilihan =
                                    $item['pilihan']
                                    ?? [];


                                if (
                                    !is_array(
                                        $pilihan
                                    )
                                ) {

                                    $pilihan = [];

                                }


                                // ==========================================
                                // VARIAN MENU
                                // ==========================================

                                $namaVarian =
                                    trim(
                                        $item['varian']
                                        ?? ''
                                    );


                                $hargaVarian =
                                    floatval(
                                        $item['harga_varian']
                                        ?? 0
                                    );


                                $catatan =
                                    trim(
                                        $item['catatan']
                                        ?? ''
                                    );

                                ?>


                                <div
                                    class="order-item"
                                >


                                    <div>


                                        <div
                                            class="order-item-name"
                                        >

                                            <?= htmlspecialchars(
                                                $item[
                                                    'nama_menu'
                                                ]
                                                ?? 'Menu'
                                            ); ?>

                                            ×

                                            <?= $jumlah; ?>

                                        </div>



                                        <!-- DETAIL VARIAN + PILIHAN -->

                                        <div
                                            class="order-item-detail"
                                        >

                                            <?php if ($namaVarian !== ''): ?>

                                                <span
                                                    class="option-line"
                                                >

                                                    🍚 Penyajian:

                                                    <span
                                                        class="option-name"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $namaVarian
                                                        ); ?>

                                                    </span>

                                                </span>

                                            <?php endif; ?>


                                            <!-- PILIHAN DINAMIS -->


                                            <?php if (
                                                count(
                                                    $pilihan
                                                ) > 0
                                            ): ?>


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


                                                    <span
                                                        class="option-line"
                                                    >

                                                        •
                                                        <?= htmlspecialchars(
                                                            $namaKelompok
                                                        ); ?>:

                                                        <span
                                                            class="option-name"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $namaPilihan
                                                            ); ?>

                                                        </span>


                                                        <?php if (
                                                            $hargaTambahan > 0
                                                        ): ?>

                                                            <span
                                                                class="option-price"
                                                            >

                                                                +Rp<?= number_format(
                                                                    $hargaTambahan,
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                ); ?>

                                                            </span>

                                                        <?php endif; ?>

                                                    </span>


                                                <?php endforeach; ?>


                                            <?php endif; ?>


                                            <?php if (
                                                $catatan !== ''
                                            ): ?>

                                                <span
                                                    class="note-line"
                                                >

                                                    📝
                                                    <?= htmlspecialchars(
                                                        $catatan
                                                    ); ?>

                                                </span>

                                            <?php endif; ?>


                                        </div>


                                    </div>



                                    <div
                                        class="order-item-price"
                                    >

                                        Rp<?= number_format(
                                            $subtotal,
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </div>


                                </div>


                            <?php endforeach; ?>


                        </div>



                        <!-- DIVIDER -->

                        <div
                            class="summary-divider"
                        ></div>



                        <!-- JUMLAH ITEM -->

                        <div
                            class="summary-row"
                        >

                            <span>
                                Jumlah Item
                            </span>


                            <strong>

                                <?= $jumlahCart; ?>

                                item

                            </strong>

                        </div>



                        <!-- TOTAL -->

                        <div
                            class="summary-total"
                        >

                            <span>
                                Total Pembayaran
                            </span>


                            <strong>

                                Rp<?= number_format(
                                    $totalHarga,
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>



                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            class="submit-order"
                        >

                            🛍️ Kirim Pesanan

                        </button>



                        <a
                            href="keranjang.php"
                            class="back-cart"
                        >

                            ← Kembali ke Keranjang

                        </a>


                    </div>


                </div>


            </div>


        </form>


    </div>

</main>


</body>

</html>