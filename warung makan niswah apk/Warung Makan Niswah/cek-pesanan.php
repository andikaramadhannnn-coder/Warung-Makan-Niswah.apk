<?php

session_start();

require_once "includes/config.php";


// =====================================================
// DATA SESSION PESANAN TERAKHIR
// =====================================================

$pesananTerakhir =
    $_SESSION['pesanan_terakhir'] ?? null;


// =====================================================
// DATA HASIL PENCARIAN
// =====================================================

$pesanan = null;

$error = '';


// =====================================================
// JIKA FORM DIKIRIM
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


    if (
        $kode === '' ||
        $noHp === ''
    ) {

        $error =
            'Kode pesanan dan nomor HP wajib diisi.';

    } else {


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

        $pesanan =
            $result->fetch_assoc();

        $stmt->close();


        if (
            !$pesanan
        ) {

            $error =
                'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan nomor HP.';

        }

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
        Cek Pesanan | Warung Makan Niswah
    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        .tracking-page {

            padding:
                45px 0 70px;

        }


        .tracking-header {

            text-align:
                center;

            margin-bottom:
                30px;

        }


        .tracking-header h1 {

            font-size:
                30px;

            margin:
                0;

        }


        .tracking-header p {

            color:
                #888;

            margin-top:
                8px;

        }


        .tracking-grid {

            display:
                grid;

            grid-template-columns:
                1fr;

            max-width:
                650px;

            margin:
                0 auto;

            gap:
                20px;

        }


        .tracking-card {

            background:
                white;

            border:
                1px solid #eee;

            border-radius:
                20px;

            padding:
                25px;

            box-sizing:
                border-box;

        }


        .tracking-card h2 {

            margin:
                0 0 20px;

            font-size:
                19px;

        }


        .form-group {

            margin-bottom:
                16px;

        }


        .form-group label {

            display:
                block;

            margin-bottom:
                7px;

            font-size:
                13px;

            font-weight:
                700;

        }


        .form-group input {

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                13px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            outline:
                none;

            font-family:
                inherit;

        }


        .form-group input:focus {

            border-color:
                #e87518;

        }


        .tracking-button {

            width:
                100%;

            border:
                0;

            padding:
                14px;

            border-radius:
                11px;

            background:
                #e87518;

            color:
                white;

            font-weight:
                800;

            cursor:
                pointer;

        }


        .tracking-error {

            margin-bottom:
                18px;

            padding:
                12px 14px;

            border-radius:
                10px;

            background:
                #fff0f0;

            color:
                #c0392b;

            font-size:
                13px;

        }


        /* =================================================
           DETAIL PESANAN
        ================================================== */

        .order-code-box {

            text-align:
                center;

            padding:
                18px;

            border-radius:
                13px;

            background:
                #fff5e9;

            margin-bottom:
                20px;

        }


        .order-code-box span {

            display:
                block;

            font-size:
                11px;

            color:
                #888;

            margin-bottom:
                6px;

        }


        .order-code-box strong {

            color:
                #e87518;

            font-size:
                21px;

            letter-spacing:
                1px;

        }


        .customer-detail {

            display:
                grid;

            gap:
                10px;

            margin-bottom:
                20px;

        }


        .detail-row {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                15px;

            font-size:
                13px;

        }


        .detail-row span {

            color:
                #888;

        }


        .detail-row strong {

            text-align:
                right;

        }


        /* =================================================
           STATUS
        ================================================== */

        .status-box {

            padding:
                20px;

            border-radius:
                15px;

            background:
                #fff5e9;

            text-align:
                center;

            margin:
                20px 0;

        }


        .status-icon {

            font-size:
                35px;

            margin-bottom:
                7px;

        }


        .status-box strong {

            display:
                block;

            color:
                #d96810;

            font-size:
                17px;

        }


        .status-box span {

            display:
                block;

            color:
                #888;

            font-size:
                12px;

            margin-top:
                5px;

        }


        /* =================================================
           TIMELINE
        ================================================== */

        .tracking-timeline {

            display:
                flex;

            flex-direction:
                column;

            gap:
                0;

            margin-top:
                25px;

        }


        .timeline-item {

            display:
                flex;

            gap:
                13px;

            position:
                relative;

            min-height:
                60px;

        }


        .timeline-dot {

            width:
                28px;

            height:
                28px;

            flex-shrink:
                0;

            border-radius:
                50%;

            background:
                #eee;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                12px;

            z-index:
                2;

        }


        .timeline-item.active
        .timeline-dot {

            background:
                #e87518;

            color:
                white;

        }


        .timeline-line {

            position:
                absolute;

            left:
                13px;

            top:
                28px;

            width:
                2px;

            height:
                35px;

            background:
                #eee;

        }


        .timeline-item.active
        .timeline-line {

            background:
                #e87518;

        }


        .timeline-content strong {

            display:
                block;

            font-size:
                13px;

        }


        .timeline-content span {

            display:
                block;

            color:
                #999;

            font-size:
                11px;

            margin-top:
                3px;

        }


        /* =================================================
           ITEM PESANAN
        ================================================== */

        .order-items {

            border-top:
                1px solid #eee;

            margin-top:
                20px;

            padding-top:
                20px;

        }


        .order-product {

            padding:
                13px 0;

            border-bottom:
                1px solid #eee;

        }


        .order-product:last-child {

            border-bottom:
                0;

        }


        .product-top {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                10px;

        }


        .product-top strong {

            font-size:
                13px;

        }


        .product-options {

            margin-top:
                5px;

            color:
                #888;

            font-size:
                11px;

            line-height:
                1.6;

        }


        .product-price {

            color:
                #e87518;

            font-weight:
                800;

            white-space:
                nowrap;

        }


        .total-box {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            margin-top:
                18px;

            padding-top:
                18px;

            border-top:
                1px solid #eee;

        }


        .total-box strong {

            color:
                #e87518;

            font-size:
                21px;

        }


        .back-button {

            display:
                block;

            margin-top:
                15px;

            text-align:
                center;

            color:
                #e87518;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                700;

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

        </a>

    </div>

</header>

<!-- =====================================================
     TOMBOL KEMBALI
====================================================== -->

<div class="customer-container page-back">

    <a href="index.php">
        ← Kembali ke Beranda
    </a>

</div>

<!-- =====================================================
     MAIN
====================================================== -->

<main
    class="tracking-page"
>

    <div
        class="customer-container"
    >


        <div
            class="tracking-header"
        >

            <h1>

                📦 Cek Pesanan

            </h1>


            <p>

                Masukkan kode pesanan dan nomor HP
                untuk melihat status pesanan.

            </p>

        </div>



        <div
            class="tracking-grid"
        >


            <!-- =================================================
                 FORM
            ================================================== -->

            <div
                class="tracking-card"
            >

                <h2>

                    🔎 Cari Pesanan

                </h2>


                <?php if (
                    $error !== ''
                ): ?>

                    <div
                        class="tracking-error"
                    >

                        ❌

                        <?= htmlspecialchars(
                            $error
                        ); ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action="cek-pesanan.php"
                >


                    <div
                        class="form-group"
                    >

                        <label>

                            Kode Pesanan

                        </label>


                        <input
                            type="text"
                            name="kode_pesanan"
                            placeholder="Contoh: NW-20260829-ABCDE"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['kode_pesanan'] ?? ''
                            ); ?>"
                        >

                    </div>



                    <div
                        class="form-group"
                    >

                        <label>

                            Nomor HP / WhatsApp

                        </label>


                        <input
                            type="tel"
                            name="no_hp"
                            placeholder="08xxxxxxxxxx"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['no_hp'] ?? ''
                            ); ?>"
                        >

                    </div>



                    <button
                        type="submit"
                        class="tracking-button"
                    >

                        🔎 Cek Status Pesanan

                    </button>


                </form>

            </div>



            <?php if (
                $pesanan
            ): ?>


                <!-- =================================================
                     HASIL PESANAN
                ================================================== -->

                <?php

                $status =
                    $pesanan[
                        'status_pesanan'
                    ];


                $statusLabels = [

                    'menunggu' =>
                        'Menunggu Konfirmasi',

                    'diterima' =>
                        'Pesanan Diterima',

                    'diproses' =>
                        'Sedang Diproses',

                    'siap_diantar' =>
                        'Siap Diantar',

                    'selesai' =>
                        'Pesanan Selesai',

                    'dibatalkan' =>
                        'Pesanan Dibatalkan'

                ];


                $statusIcons = [

                    'menunggu' =>
                        '🕐',

                    'diterima' =>
                        '✅',

                    'diproses' =>
                        '👨‍🍳',

                    'siap_diantar' =>
                        '🛵',

                    'selesai' =>
                        '🎉',

                    'dibatalkan' =>
                        '❌'

                ];


                $urutanStatus = [

                    'menunggu',
                    'diterima',
                    'diproses',
                    'siap_diantar',
                    'selesai'

                ];


                $statusIndex =
                    array_search(
                        $status,
                        $urutanStatus
                    );

                ?>


                <div
                    class="tracking-card"
                >


                    <div
                        class="order-code-box"
                    >

                        <span>
                            KODE PESANAN
                        </span>


                        <strong>

                            <?= htmlspecialchars(
                                $pesanan[
                                    'kode_pesanan'
                                ]
                            ); ?>

                        </strong>

                    </div>



                    <!-- STATUS SEKARANG -->

                    <div
                        class="status-box"
                    >

                        <div
                            class="status-icon"
                        >

                            <?= $statusIcons[
                                $status
                            ] ?? '📦'; ?>

                        </div>


                        <strong>

                            <?= htmlspecialchars(
                                $statusLabels[
                                    $status
                                ] ?? ucfirst(
                                    $status
                                )
                            ); ?>

                        </strong>


                        <span>

                            Status pesanan kamu saat ini.

                        </span>

                    </div>



                    <!-- TIMELINE -->

                    <?php if (
                        $status !== 'dibatalkan'
                    ): ?>


                        <div
                            class="tracking-timeline"
                        >


                            <?php foreach (
                                $urutanStatus
                                as $index => $statusItem
                            ): ?>


                                <div
                                    class="timeline-item
                                    <?= (
                                        $statusIndex !== false &&
                                        $index <= $statusIndex
                                    )
                                    ? 'active'
                                    : ''; ?>"
                                >


                                    <div
                                        class="timeline-dot"
                                    >

                                        <?= (
                                            $statusIndex !== false &&
                                            $index <= $statusIndex
                                        )
                                        ? '✓'
                                        : ''; ?>

                                    </div>


                                    <?php if (
                                        $index <
                                        count(
                                            $urutanStatus
                                        ) - 1
                                    ): ?>

                                        <div
                                            class="timeline-line"
                                        ></div>

                                    <?php endif; ?>


                                    <div
                                        class="timeline-content"
                                    >

                                        <strong>

                                            <?= htmlspecialchars(
                                                $statusLabels[
                                                    $statusItem
                                                ]
                                            ); ?>

                                        </strong>


                                    </div>


                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php endif; ?>



                    <!-- DETAIL PELANGGAN -->

                    <div
                        class="customer-detail"
                    >

                        <div
                            class="detail-row"
                        >

                            <span>
                                Pemesan
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $pesanan['nama']
                                ); ?>

                            </strong>

                        </div>


                        <div
                            class="detail-row"
                        >

                            <span>
                                Lokasi
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $pesanan['alamat']
                                ); ?>

                            </strong>

                        </div>


                        <div
                            class="detail-row"
                        >

                            <span>
                                Pembayaran
                            </span>

                            <strong>

                                <?= strtoupper(
                                    $pesanan[
                                        'metode_pembayaran'
                                    ]
                                ); ?>

                            </strong>

                        </div>

                    </div>



                    <!-- =================================================
                         DETAIL PRODUK
                    ================================================== -->

                    <?php

                    $pesananId =
                        intval(
                            $pesanan['id']
                        );


                    $detailQuery =
                        $conn->prepare("
                            SELECT *
                            FROM detail_pesanan
                            WHERE pesanan_id = ?
                            ORDER BY id ASC
                        ");

                    $detailQuery->bind_param(
                        "i",
                        $pesananId
                    );

                    $detailQuery->execute();

                    $detailResult =
                        $detailQuery->get_result();

                    ?>


                    <div
                        class="order-items"
                    >

                        <h2>

                            🛒 Pesanan

                        </h2>


                        <?php while (
                            $detail =
                            $detailResult->fetch_assoc()
                        ): ?>


                            <div
                                class="order-product"
                            >


                                <div
                                    class="product-top"
                                >

                                    <strong>

                                        <?= htmlspecialchars(
                                            $detail[
                                                'nama_menu'
                                            ]
                                        ); ?>

                                        ×

                                        <?= intval(
                                            $detail[
                                                'jumlah'
                                            ]
                                        ); ?>

                                    </strong>


                                    <span
                                        class="product-price"
                                    >

                                        Rp<?= number_format(
                                            $detail[
                                                'subtotal'
                                            ],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </span>

                                </div>



                                <?php

                                $detailId =
                                    intval(
                                        $detail['id']
                                    );


                                $pilihanQuery =
                                    $conn->prepare("
                                        SELECT *
                                        FROM detail_pilihan_pesanan
                                        WHERE detail_pesanan_id = ?
                                        ORDER BY id ASC
                                    ");

                                $pilihanQuery->bind_param(
                                    "i",
                                    $detailId
                                );

                                $pilihanQuery->execute();

                                $pilihanResult =
                                    $pilihanQuery->get_result();

                                ?>


                                <?php if (
                                    $pilihanResult->num_rows > 0
                                ): ?>

                                    <div
                                        class="product-options"
                                    >


                                        <?php while (
                                            $pilihan =
                                            $pilihanResult->fetch_assoc()
                                        ): ?>

                                            <?= htmlspecialchars(
                                                $pilihan[
                                                    'nama_pilihan'
                                                ]
                                            ); ?>


                                            <?php if (
                                                $pilihan[
                                                    'harga_tambahan'
                                                ] > 0
                                            ): ?>

                                                (+Rp<?= number_format(
                                                    $pilihan[
                                                        'harga_tambahan'
                                                    ],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>)

                                            <?php endif; ?>


                                            <br>

                                        <?php endwhile; ?>


                                    </div>

                                <?php endif; ?>


                                <?php

                                $pilihanQuery->close();

                                ?>

                            </div>


                        <?php endwhile; ?>


                        <?php

                        $detailQuery->close();

                        ?>



                        <!-- TOTAL -->

                        <div
                            class="total-box"
                        >

                            <span>

                                Total Pembayaran

                            </span>


                            <strong>

                                Rp<?= number_format(
                                    $pesanan[
                                        'total_harga'
                                    ],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                    </div>


                </div>


            <?php endif; ?>


        </div>

    </div>

</main>



</body>

</html>