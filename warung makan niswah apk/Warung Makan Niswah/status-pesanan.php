<?php

session_start();

require_once "includes/config.php";


// =====================================================
// VARIABEL
// =====================================================

$hasil = null;

$detailPesanan = [];

$error = '';


// =====================================================
// PROSES CEK PESANAN
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $kode =
        trim(
            $_POST['kode_pesanan'] ?? ''
        );

    $noHp =
        trim(
            $_POST['no_hp'] ?? ''
        );


    // =================================================
    // VALIDASI
    // =================================================

    if (
        $kode === '' ||
        $noHp === ''
    ) {

        $error =
            'Kode pesanan dan nomor HP wajib diisi.';

    }

    else {


        // =============================================
        // CARI PESANAN
        // =============================================

        $stmt =
            $conn->prepare("
                SELECT
                    p.*,
                    pl.nama,
                    pl.no_hp,
                    pl.alamat

                FROM pesanan p

                INNER JOIN pelanggan pl
                    ON p.pelanggan_id = pl.id

                WHERE p.kode_pesanan = ?
                  AND pl.no_hp = ?

                LIMIT 1
            ");


        $stmt->bind_param(
            "ss",
            $kode,
            $noHp
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result->num_rows > 0
        ) {

            $hasil =
                $result->fetch_assoc();


            // =============================================
            // AMBIL DETAIL PESANAN
            // =============================================

            $stmtDetail =
                $conn->prepare("
                    SELECT
                        dp.id,
                        dp.nama_menu,
                        dp.harga,
                        dp.jumlah,
                        dp.subtotal
                    FROM detail_pesanan dp
                    WHERE dp.pesanan_id = ?
                    ORDER BY dp.id ASC
                ");

            $stmtDetail->bind_param(
                "i",
                $hasil['id']
            );

            $stmtDetail->execute();

            $resultDetail =
                $stmtDetail->get_result();


            while (
                $detail = $resultDetail->fetch_assoc()
            ) {

                $detail['pilihan'] = [];

                $stmtPilihan =
                    $conn->prepare("
                        SELECT
                            tipe_pilihan,
                            nama_pilihan,
                            harga_tambahan
                        FROM detail_pilihan_pesanan
                        WHERE detail_pesanan_id = ?
                        ORDER BY id ASC
                    ");

                $stmtPilihan->bind_param(
                    "i",
                    $detail['id']
                );

                $stmtPilihan->execute();

                $resultPilihan =
                    $stmtPilihan->get_result();


                while (
                    $pilihan =
                        $resultPilihan->fetch_assoc()
                ) {

                    $detail['pilihan'][] =
                        $pilihan;

                }


                $stmtPilihan->close();

                $detailPesanan[] =
                    $detail;

            }


            $stmtDetail->close();

        }

        else {

            $error =
                'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan nomor HP.';

        }


        $stmt->close();

    }

}


// =====================================================
// JUMLAH CART
// =====================================================

$cart =
    $_SESSION['cart']
    ?? [];


$jumlahCart = 0;


foreach (
    $cart as $item
) {

    $jumlahCart +=
        intval(
            $item['jumlah']
            ?? 0
        );

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
        Cek Pesanan | Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        .status-page {

            min-height:
                80vh;

            padding:
                25px 20px 70px;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .status-card {

            width:
                100%;

            max-width:
                600px;

            margin:
                0 auto;

            background:
                #fff;

            border:
                1px solid #eee;

            border-radius:
                20px;

            padding:
                30px;

            box-sizing:
                border-box;

        }


        .status-card h1 {

            margin:
                0 0 8px;

        }


        .status-card > p {

            color:
                #777;

            margin:
                0 0 25px;

            line-height:
                1.6;

            font-size:
                14px;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {

            margin-bottom:
                18px;

        }


        .form-group label {

            display:
                block;

            margin-bottom:
                7px;

            font-weight:
                700;

            font-size:
                13px;

        }


        .form-group input {

            width:
                100%;

            padding:
                13px 14px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            font-size:
                15px;

            box-sizing:
                border-box;

            outline:
                none;

        }


        .form-group input:focus {

            border-color:
                #e87518;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .status-button {

            width:
                100%;

            border:
                none;

            padding:
                14px;

            background:
                #e87518;

            color:
                white;

            border-radius:
                11px;

            font-size:
                15px;

            font-weight:
                800;

            cursor:
                pointer;

        }


        .status-button:hover {

            background:
                #d76510;

        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-box {

            margin-bottom:
                20px;

            padding:
                13px;

            background:
                #ffe8e8;

            color:
                #c62828;

            border-radius:
                10px;

            font-size:
                14px;

            line-height:
                1.5;

        }


        /* =====================================================
           RESULT
        ===================================================== */

        .result-box {

            margin-top:
                30px;

            padding:
                22px;

            background:
                #fff8ef;

            border-radius:
                15px;

        }


        .result-title {

            margin-bottom:
                15px;

            font-size:
                16px;

            font-weight:
                800;

        }


        .result-row {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                20px;

            padding:
                11px 0;

            border-bottom:
                1px solid #eee;

        }


        .result-row:last-child {

            border-bottom:
                none;

        }


        .result-label {

            color:
                #777;

            font-size:
                13px;

        }


        .result-value {

            font-weight:
                700;

            text-align:
                right;

            font-size:
                13px;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status-badge {

            display:
                inline-block;

            padding:
                7px 12px;

            border-radius:
                20px;

            background:
                #fff0df;

            color:
                #e87518;

            font-size:
                13px;

        }


        /* =====================================================
           STATUS INFO
        ===================================================== */

        .order-items {

            margin-top:
                20px;

        }


        .order-item {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                13px;

            padding:
                15px;

            margin-bottom:
                10px;

        }


        .order-item-header {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                15px;

            font-size:
                14px;

            font-weight:
                800;

        }


        .order-item-meta {

            margin-top:
                6px;

            color:
                #777;

            font-size:
                12px;

        }


        .order-item-subtotal {

            margin-top:
                8px;

            color:
                #e87518;

            font-size:
                13px;

            font-weight:
                800;

            text-align:
                right;

        }


        .order-item-choice {

            margin-top:
                6px;

            color:
                #666;

            font-size:
                12px;

        }


        .order-item-choice strong {

            color:
                #333;

        }


        .status-info {

            margin-top:
                20px;

            padding:
                15px;

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                12px;

            text-align:
                center;

        }


        .status-info strong {

            display:
                block;

            color:
                #e87518;

            font-size:
                15px;

        }


        .status-info span {

            display:
                block;

            margin-top:
                5px;

            color:
                #888;

            font-size:
                12px;

        }


        /* =====================================================
           BOTTOM ACTION
        ===================================================== */

        .status-actions {

            display:
                flex;

            flex-direction:
                column;

            gap:
                10px;

            margin-top:
                25px;

        }


        .action-button {

            display:
                block;

            width:
                100%;

            padding:
                13px;

            box-sizing:
                border-box;

            border-radius:
                11px;

            text-align:
                center;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                800;

        }


        .home-button {

            background:
                #e87518;

            color:
                white;

        }


        .home-button:hover {

            background:
                #d76510;

        }


        .cart-action-button {

            background:
                white;

            color:
                #e87518;

            border:
                1px solid #e87518;

        }


        .cart-action-button:hover {

            background:
                #fff8ef;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (
            max-width: 550px
        ) {

            .status-page {

                padding:
                    20px 15px 60px;

            }


            .status-card {

                padding:
                    22px 18px;

            }


            .result-row {

                align-items:
                    flex-start;

            }


            .result-value {

                max-width:
                    55%;

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


        <!-- CART -->

        <a
            href="keranjang.php"
            class="cart-button"
        >

            🛒

            <span>
                Keranjang
            </span>


            <?php if (
                $jumlahCart > 0
            ): ?>

                <b>

                    <?= $jumlahCart; ?>

                </b>

            <?php endif; ?>


        </a>


    </div>

</header>



<!-- =====================================================
     MAIN
====================================================== -->

<main
    class="status-page"
>


    <!-- =================================================
         CARD
    ================================================== -->

    <div
        class="status-card"
    >


        <h1>
            🔎 Cek Status Pesanan
        </h1>


        <p>

            Masukkan kode pesanan dan
            nomor HP yang digunakan
            saat memesan.

        </p>



        <!-- =================================================
             ERROR
        ================================================== -->

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
        >


            <div
                class="form-group"
            >

                <label
                    for="kode_pesanan"
                >

                    Kode Pesanan

                </label>


                <input
                    type="text"
                    id="kode_pesanan"
                    name="kode_pesanan"
                    placeholder="Contoh: NW-20260829-ABCDE"
                    value="<?= htmlspecialchars(
                        $_POST['kode_pesanan']
                        ?? ''
                    ); ?>"
                    required
                >

            </div>



            <div
                class="form-group"
            >

                <label
                    for="no_hp"
                >

                    Nomor HP

                </label>


                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    placeholder="Masukkan nomor HP"
                    value="<?= htmlspecialchars(
                        $_POST['no_hp']
                        ?? ''
                    ); ?>"
                    required
                >

            </div>



            <button
                type="submit"
                class="status-button"
            >

                🔎 Cek Pesanan

            </button>


        </form>



        <!-- =================================================
             HASIL
        ================================================== -->

        <?php if (
            $hasil
        ): ?>


            <div
                class="result-box"
            >


                <div
                    class="result-title"
                >

                    📦 Detail Pesanan

                </div>



                <!-- ITEM PESANAN -->

                <?php if (
                    count($detailPesanan) > 0
                ): ?>

                    <div
                        class="order-items"
                    >

                        <?php foreach (
                            $detailPesanan as $detail
                        ): ?>

                            <div
                                class="order-item"
                            >

                                <div
                                    class="order-item-header"
                                >

                                    <span>
                                        <?= htmlspecialchars(
                                            $detail['nama_menu']
                                        ); ?>
                                    </span>

                                    <span>
                                        × <?= intval(
                                            $detail['jumlah']
                                        ); ?>
                                    </span>

                                </div>


                                <div
                                    class="order-item-meta"
                                >

                                    Rp<?= number_format(
                                        $detail['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                    / item

                                </div>


                                <?php if (
                                    !empty(
                                        $detail['pilihan']
                                    )
                                ): ?>

                                    <?php foreach (
                                        $detail['pilihan']
                                        as $pilihan
                                    ): ?>

                                        <div
                                            class="order-item-choice"
                                        >

                                            <?php
                                            $tipePilihan =
                                                $pilihan[
                                                    'tipe_pilihan'
                                                ];

                                            $labelPilihan =
                                                $tipePilihan === 'varian'
                                                    ? 'Penyajian'
                                                    : ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $tipePilihan
                                                        )
                                                    );
                                            ?>

                                            <?= htmlspecialchars(
                                                $labelPilihan
                                            ); ?>:

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $pilihan[
                                                        'nama_pilihan'
                                                    ]
                                                ); ?>
                                            </strong>

                                        </div>

                                    <?php endforeach; ?>

                                <?php endif; ?>


                                <div
                                    class="order-item-subtotal"
                                >

                                    Subtotal:

                                    Rp<?= number_format(
                                        $detail['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>


                <!-- KODE -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Kode Pesanan

                    </span>


                    <span
                        class="result-value"
                    >

                        <?= htmlspecialchars(
                            $hasil[
                                'kode_pesanan'
                            ]
                        ); ?>

                    </span>

                </div>



                <!-- NAMA -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Nama

                    </span>


                    <span
                        class="result-value"
                    >

                        <?= htmlspecialchars(
                            $hasil[
                                'nama'
                            ]
                        ); ?>

                    </span>

                </div>



                <!-- TOTAL -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Total

                    </span>


                    <span
                        class="result-value"
                    >

                        Rp<?= number_format(
                            $hasil[
                                'total_harga'
                            ],
                            0,
                            ',',
                            '.'
                        ); ?>

                    </span>

                </div>



                <!-- PEMBAYARAN -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Pembayaran

                    </span>


                    <span
                        class="result-value"
                    >

                        <?= strtoupper(
                            htmlspecialchars(
                                $hasil[
                                    'metode_pembayaran'
                                ]
                            )
                        ); ?>

                    </span>

                </div>



                <!-- STATUS -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Status

                    </span>


                    <span
                        class="result-value"
                    >


                        <span
                            class="status-badge"
                        >

                            <?= ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $hasil[
                                        'status_pesanan'
                                    ]
                                )
                            ); ?>

                        </span>


                    </span>

                </div>



                <!-- TANGGAL -->

                <div
                    class="result-row"
                >

                    <span
                        class="result-label"
                    >

                        Tanggal

                    </span>


                    <span
                        class="result-value"
                    >

                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $hasil[
                                    'created_at'
                                ]
                            )
                        ); ?>

                    </span>

                </div>



                <!-- INFO STATUS -->

                <div
                    class="status-info"
                >

                    <?php

                    $status =
                        $hasil[
                            'status_pesanan'
                        ];

                    ?>


                    <?php if (
                        $status === 'menunggu'
                    ): ?>

                        <strong>
                            ⏳ Pesanan Menunggu
                        </strong>

                        <span>
                            Pesanan kamu sudah diterima
                            dan sedang menunggu diproses.
                        </span>


                    <?php elseif (
                        $status === 'diterima'
                    ): ?>

                        <strong>
                            ✅ Pesanan Diterima
                        </strong>

                        <span>
                            Pesanan sudah dikonfirmasi
                            oleh pihak warung.
                        </span>


                    <?php elseif (
                        $status === 'diproses'
                    ): ?>

                        <strong>
                            👨‍🍳 Sedang Diproses
                        </strong>

                        <span>
                            Pesanan kamu sedang
                            disiapkan oleh warung.
                        </span>


                    <?php elseif (
                        $status === 'siap_diantar'
                    ): ?>

                        <strong>
                            🛵 Sedang Diantar
                        </strong>

                        <span>
                            Pesanan sudah siap dan
                            sedang dalam proses pengantaran.
                        </span>


                    <?php elseif (
                        $status === 'selesai'
                    ): ?>

                        <strong>
                            🎉 Pesanan Selesai
                        </strong>

                        <span>
                            Pesanan kamu sudah selesai.
                            Terima kasih sudah memesan.
                        </span>


                    <?php elseif (
                        $status === 'dibatalkan'
                    ): ?>

                        <strong>
                            ❌ Pesanan Dibatalkan
                        </strong>

                        <span>
                            Pesanan ini telah dibatalkan.
                        </span>


                    <?php else: ?>

                        <strong>
                            📦 Status Pesanan
                        </strong>

                        <span>
                            Status pesanan sedang diperbarui.
                        </span>

                    <?php endif; ?>


                </div>


            </div>


        <?php endif; ?>



        <!-- =================================================
             ACTION
        ================================================== -->

        <div
            class="status-actions"
        >


            <a
                href="index.php"
                class="action-button home-button"
            >

                🍛 Kembali ke Beranda

            </a>


            <a
                href="keranjang.php"
                class="action-button cart-action-button"
            >

                🛒 Buka Keranjang

            </a>


        </div>


    </div>


</main>


</body>

</html>