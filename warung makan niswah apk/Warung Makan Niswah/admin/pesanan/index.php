<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// FILTER STATUS
// =====================================================

$filterStatus =
    $_GET['status'] ?? '';


$statusValid = [

    'menunggu',

    'diterima',

    'diproses',

    'siap_diantar',

    'selesai',

    'dibatalkan'

];


// =====================================================
// QUERY PESANAN
// =====================================================

$query = "

    SELECT

        p.id,

        p.kode_pesanan,

        p.total_harga,

        p.metode_pembayaran,

        p.status_pesanan,

        p.created_at,

        pl.nama,

        pl.no_hp,

        pl.alamat

    FROM pesanan p

    INNER JOIN pelanggan pl

        ON p.pelanggan_id = pl.id

";


if (

    in_array(

        $filterStatus,

        $statusValid,

        true

    )

) {

    $query .= "

        WHERE p.status_pesanan = ?

    ";

}


$query .= "

    ORDER BY p.created_at DESC

";


if (

    in_array(

        $filterStatus,

        $statusValid,

        true

    )

) {

    $stmt =

        $conn->prepare(

            $query

        );


    $stmt->bind_param(

        "s",

        $filterStatus

    );


    $stmt->execute();


    $result =

        $stmt->get_result();

} else {

    $result =

        $conn->query(

            $query

        );

}


// =====================================================
// HITUNG JUMLAH PESANAN
// =====================================================

$totalPesanan =

    $result

    ? $result->num_rows

    : 0;


// =====================================================
// RINGKASAN STATUS
// =====================================================

$jumlahStatus = [

    'menunggu' => 0,

    'diterima' => 0,

    'diproses' => 0,

    'siap_diantar' => 0,

    'selesai' => 0,

    'dibatalkan' => 0

];


$statusQuery = $conn->query("

    SELECT

        status_pesanan,

        COUNT(*) AS jumlah

    FROM pesanan

    GROUP BY status_pesanan

");


if ($statusQuery) {

    while (

        $statusRow =

            $statusQuery->fetch_assoc()

    ) {

        $status =

            $statusRow[

                'status_pesanan'

            ];


        if (

            isset(

                $jumlahStatus[

                    $status

                ]

            )

        ) {

            $jumlahStatus[

                $status

            ] =

                intval(

                    $statusRow[

                        'jumlah'

                    ]

                );

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

        Pesanan | Warung Makan Niswah

    </title>


    <link

        rel="stylesheet"

        href="../../assets/css/admin.css"

    >


    <style>


        /* =====================================================
           HEADER
        ===================================================== */

        .page-header {

            display:

                flex;

            justify-content:

                space-between;

            align-items:

                center;

            gap:

                20px;

        }


        .order-count {

            background:

                #fff3e5;

            color:

                #e87518;

            border:

                1px solid #ffd8b2;

            border-radius:

                12px;

            padding:

                10px 15px;

            text-align:

                center;

            min-width:

                90px;

        }


        .order-count strong {

            display:

                block;

            font-size:

                20px;

        }


        .order-count span {

            display:

                block;

            margin-top:

                2px;

            font-size:

                11px;

            color:

                #888;

        }



        /* =====================================================
           STATUS SUMMARY
        ===================================================== */

        .status-summary {

            display:

                grid;

            grid-template-columns:

                repeat(5, 1fr);

            gap:

                12px;

            margin:

                22px 0;

        }


        .status-summary-card {

            display:

                block;

            background:

                white;

            border:

                1px solid #eee;

            border-radius:

                14px;

            padding:

                16px;

            text-decoration:

                none;

            transition:

                .2s;

        }


        .status-summary-card:hover {

            transform:

                translateY(-2px);

            border-color:

                #e87518;

        }


        .status-summary-icon {

            font-size:

                22px;

            margin-bottom:

                8px;

        }


        .status-summary-card strong {

            display:

                block;

            font-size:

                22px;

            color:

                #222;

        }


        .status-summary-card span {

            display:

                block;

            margin-top:

                3px;

            color:

                #888;

            font-size:

                11px;

            font-weight:

                700;

        }


        .summary-menunggu {

            border-left:

                4px solid #f0ad00;

        }


        .summary-diterima {

            border-left:

                4px solid #3498db;

        }


        .summary-diproses {

            border-left:

                4px solid #e87518;

        }


        .summary-diantar {

            border-left:

                4px solid #6845b5;

        }


        .summary-selesai {

            border-left:

                4px solid #20834d;

        }



        /* =====================================================
           FILTER
        ===================================================== */

        .order-filter {

            display:

                flex;

            align-items:

                center;

            gap:

                8px;

            flex-wrap:

                wrap;

            padding:

                15px 20px;

            border-bottom:

                1px solid #eee;

        }


        .filter-title {

            font-size:

                13px;

            font-weight:

                700;

            color:

                #555;

            margin-right:

                5px;

        }


        .filter-button {

            display:

                inline-flex;

            align-items:

                center;

            justify-content:

                center;

            padding:

                8px 12px;

            border:

                1px solid #ddd;

            border-radius:

                9px;

            background:

                white;

            color:

                #666;

            text-decoration:

                none;

            font-size:

                11px;

            font-weight:

                700;

            transition:

                .2s;

        }


        .filter-button:hover {

            border-color:

                #e87518;

            color:

                #e87518;

        }


        .filter-button.active {

            background:

                #e87518;

            border-color:

                #e87518;

            color:

                white;

        }



        /* =====================================================
           TABLE
        ===================================================== */

        .order-code {

            color:

                #e87518;

        }


        .customer-info strong {

            display:

                block;

        }


        .customer-info small {

            display:

                block;

            margin-top:

                4px;

            color:

                #888;

        }


        .payment-badge {

            display:

                inline-block;

            padding:

                6px 9px;

            border-radius:

                8px;

            background:

                #f5f5f5;

            color:

                #555;

            font-size:

                10px;

            font-weight:

                800;

        }



        /* =====================================================
           STATUS
        ===================================================== */

        .order-status {

            display:

                inline-block;

            padding:

                6px 10px;

            border-radius:

                20px;

            font-size:

                10px;

            font-weight:

                800;

            white-space:

                nowrap;

        }


        .status-menunggu {

            background:

                #fff4d6;

            color:

                #a86b00;

        }


        .status-diterima {

            background:

                #e9f4ff;

            color:

                #1877b7;

        }


        .status-diproses {

            background:

                #fff0df;

            color:

                #e87518;

        }


        .status-siap_diantar {

            background:

                #eee8ff;

            color:

                #6845b5;

        }


        .status-selesai {

            background:

                #e6f7ed;

            color:

                #20834d;

        }


        .status-dibatalkan {

            background:

                #ffe8e8;

            color:

                #c62828;

        }



        /* =====================================================
           DETAIL BUTTON
        ===================================================== */

        .detail-button {

            display:

                inline-flex;

            align-items:

                center;

            justify-content:

                center;

            gap:

                5px;

            padding:

                8px 11px;

            background:

                #e87518;

            color:

                white;

            border-radius:

                8px;

            text-decoration:

                none;

            font-size:

                11px;

            font-weight:

                800;

            white-space:

                nowrap;

        }


        .detail-button:hover {

            background:

                #d76510;

        }



        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-table {

            text-align:

                center;

            padding:

                60px 20px !important;

        }


        .empty-table div {

            font-size:

                45px;

            margin-bottom:

                10px;

        }


        .empty-table strong {

            display:

                block;

            font-size:

                15px;

        }


        .empty-table p {

            margin-top:

                6px;

            color:

                #888;

            font-size:

                12px;

        }



        /* =====================================================
           MOBILE
        ===================================================== */

        @media (

            max-width: 900px

        ) {

            .status-summary {

                grid-template-columns:

                    repeat(3, 1fr);

            }

        }


        @media (

            max-width: 700px

        ) {

            .page-header {

                align-items:

                    flex-start;

            }


            .order-count {

                min-width:

                    70px;

            }


            .order-filter {

                padding:

                    12px;

            }


            .filter-title {

                width:

                    100%;

            }


            .status-summary {

                grid-template-columns:

                    repeat(2, 1fr);

                gap:

                    9px;

            }


            .status-summary-card {

                padding:

                    13px;

            }


            .status-summary-icon {

                font-size:

                    19px;

            }


            .status-summary-card strong {

                font-size:

                    19px;

            }

        }

        /* =====================================================
   NOTIFIKASI PESANAN BARU
===================================================== */

.new-order-notification {

    position: fixed;

    top: 25px;

    right: 25px;

    width: 350px;

    max-width:
        calc(100vw - 40px);

    background: white;

    border:
        1px solid #eee;

    border-left:
        5px solid #e87518;

    border-radius:
        16px;

    padding:
        17px;

    box-shadow:
        0 12px 35px rgba(0,0,0,.15);

    z-index:
        99999;

    transform:
        translateX(420px);

    opacity:
        0;

    pointer-events:
        none;

    transition:
        .35s ease;

}


.new-order-notification.show {

    transform:
        translateX(0);

    opacity:
        1;

    pointer-events:
        auto;

}


.new-order-notification-header {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


.new-order-notification-icon {

    width:
        40px;

    height:
        40px;

    flex-shrink:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #fff0df;

    border-radius:
        11px;

    font-size:
        21px;

}


.new-order-notification-header strong {

    display:
        block;

    font-size:
        14px;

}


.new-order-notification-header span {

    display:
        block;

    margin-top:
        3px;

    color:
        #888;

    font-size:
        11px;

}


.new-order-notification-body {

    margin-top:
        14px;

    padding:
        12px;

    background:
        #fafafa;

    border-radius:
        10px;

}


.new-order-notification-code {

    color:
        #e87518;

    font-weight:
        800;

    font-size:
        13px;

}


.new-order-notification-customer {

    margin-top:
        4px;

    color:
        #555;

    font-size:
        12px;

}


.new-order-notification-total {

    margin-top:
        6px;

    font-size:
        14px;

    font-weight:
        800;

}


.new-order-notification-actions {

    display:
        flex;

    gap:
        8px;

    margin-top:
        12px;

}


.new-order-notification-actions a {

    flex:
        1;

    padding:
        10px;

    text-align:
        center;

    border-radius:
        9px;

    text-decoration:
        none;

    font-size:
        11px;

    font-weight:
        800;

}


.new-order-view {

    background:
        #e87518;

    color:
        white;

}


.new-order-close {

    background:
        #f5f5f5;

    color:
        #555;

    cursor:
        pointer;

}


@media (
    max-width: 600px
) {

    .new-order-notification {

        top:
            15px;

        right:
            15px;

        width:
            calc(100vw - 30px);

    }

}


    </style>

</head>


<body>

<!-- =====================================================
     NOTIFIKASI PESANAN BARU
===================================================== -->

<div
    id="newOrderNotification"
    class="new-order-notification"
>

    <div
        class="new-order-notification-header"
    >

        <div
            class="new-order-notification-icon"
        >
            🔔
        </div>


        <div>

            <strong>
                Pesanan Baru!
            </strong>

            <span>
                Ada pesanan yang perlu diproses
            </span>

        </div>

    </div>


    <div
        class="new-order-notification-body"
    >

        <div
            id="newOrderCode"
            class="new-order-notification-code"
        >
            -
        </div>


        <div
            id="newOrderCustomer"
            class="new-order-notification-customer"
        >
            -
        </div>


        <div
            id="newOrderTotal"
            class="new-order-notification-total"
        >
            -
        </div>

    </div>


    <div
        class="new-order-notification-actions"
    >

        <a
            id="newOrderView"
            href="#"
            class="new-order-view"
        >
            👁️ Lihat Pesanan
        </a>


        <a
            href="#"
            class="new-order-close"
            onclick="tutupNotifikasi(); return false;"
        >
            Tutup
        </a>

    </div>

</div>


<div class="admin-wrapper">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

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

                <span>

                    📊

                </span>

                <span>

                    Dashboard

                </span>

            </a>



            <a href="../menu/index.php">

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



            <a
                href="index.php"
                class="active"
            >

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
    ====================================================== -->

    <main class="main-content">


        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="page-header">


            <div>

                <h1>

                    Pesanan

                </h1>


                <p>

                    Kelola pesanan online

                    Warung Makan Niswah.

                </p>

            </div>


            <div class="order-count">


                <strong>

                    <?= $totalPesanan; ?>

                </strong>


                <span>

                    Pesanan

                </span>


            </div>


        </header>



        <!-- =================================================
             STATUS SUMMARY
        ================================================== -->

        <div class="status-summary">


            <a
                href="?status=menunggu"
                class="status-summary-card summary-menunggu"
            >

                <div class="status-summary-icon">

                    🟡

                </div>


                <strong>

                    <?= $jumlahStatus['menunggu']; ?>

                </strong>


                <span>

                    Menunggu

                </span>

            </a>



            <a
                href="?status=diterima"
                class="status-summary-card summary-diterima"
            >

                <div class="status-summary-icon">

                    🔵

                </div>


                <strong>

                    <?= $jumlahStatus['diterima']; ?>

                </strong>


                <span>

                    Diterima

                </span>

            </a>



            <a
                href="?status=diproses"
                class="status-summary-card summary-diproses"
            >

                <div class="status-summary-icon">

                    👨‍🍳

                </div>


                <strong>

                    <?= $jumlahStatus['diproses']; ?>

                </strong>


                <span>

                    Diproses

                </span>

            </a>



            <a
                href="?status=siap_diantar"
                class="status-summary-card summary-diantar"
            >

                <div class="status-summary-icon">

                    🛵

                </div>


                <strong>

                    <?= $jumlahStatus['siap_diantar']; ?>

                </strong>


                <span>

                    Siap Diantar

                </span>

            </a>



            <a
                href="?status=selesai"
                class="status-summary-card summary-selesai"
            >

                <div class="status-summary-icon">

                    ✅

                </div>


                <strong>

                    <?= $jumlahStatus['selesai']; ?>

                </strong>


                <span>

                    Selesai

                </span>

            </a>


        </div>



        <!-- =================================================
             TABLE CARD
        ================================================== -->

        <div class="table-card">


            <!-- FILTER -->

            <div class="order-filter">


                <span class="filter-title">

                    Filter Status:

                </span>



                <a
                    href="index.php"
                    class="filter-button
                    <?= $filterStatus === ''
                        ? 'active'
                        : ''; ?>"
                >

                    Semua

                </a>



                <a
                    href="?status=menunggu"
                    class="filter-button
                    <?= $filterStatus === 'menunggu'
                        ? 'active'
                        : ''; ?>"
                >

                    Menunggu

                </a>



                <a
                    href="?status=diterima"
                    class="filter-button
                    <?= $filterStatus === 'diterima'
                        ? 'active'
                        : ''; ?>"
                >

                    Diterima

                </a>



                <a
                    href="?status=diproses"
                    class="filter-button
                    <?= $filterStatus === 'diproses'
                        ? 'active'
                        : ''; ?>"
                >

                    Diproses

                </a>



                <a
                    href="?status=siap_diantar"
                    class="filter-button
                    <?= $filterStatus === 'siap_diantar'
                        ? 'active'
                        : ''; ?>"
                >

                    Diantar

                </a>



                <a
                    href="?status=selesai"
                    class="filter-button
                    <?= $filterStatus === 'selesai'
                        ? 'active'
                        : ''; ?>"
                >

                    Selesai

                </a>



                <a
                    href="?status=dibatalkan"
                    class="filter-button
                    <?= $filterStatus === 'dibatalkan'
                        ? 'active'
                        : ''; ?>"
                >

                    Dibatalkan

                </a>


            </div>



            <!-- TABLE HEADER -->

            <div class="table-header">


                <div>

                    <h3>

                        Semua Pesanan

                    </h3>


                    <p>


                        <?php if (

                            $filterStatus !== ''

                        ): ?>


                            Menampilkan pesanan dengan

                            status:


                            <strong>

                                <?= ucfirst(

                                    str_replace(

                                        '_',

                                        ' ',

                                        $filterStatus

                                    )

                                ); ?>

                            </strong>


                        <?php else: ?>


                            Pesanan terbaru

                            berada di bagian atas.


                        <?php endif; ?>


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

                                Kode Pesanan

                            </th>


                            <th>

                                Pelanggan

                            </th>


                            <th>

                                Total

                            </th>


                            <th>

                                Pembayaran

                            </th>


                            <th>

                                Status

                            </th>


                            <th>

                                Tanggal

                            </th>


                            <th>

                                Aksi

                            </th>


                        </tr>


                    </thead>



                    <tbody>


                    <?php

                    $no = 1;


                    if (

                        $result &&

                        $result->num_rows > 0

                    ):

                    ?>


                        <?php while (

                            $row =

                            $result->fetch_assoc()

                        ): ?>


                            <?php

                            $status =

                                $row[

                                    'status_pesanan'

                                ];

                            ?>


                            <tr>


                                <td>

                                    <?= $no++; ?>

                                </td>



                                <td>

                                    <strong
                                        class="order-code"
                                    >

                                        <?= htmlspecialchars(

                                            $row[

                                                'kode_pesanan'

                                            ]

                                        ); ?>

                                    </strong>

                                </td>



                                <td>

                                    <div
                                        class="customer-info"
                                    >

                                        <strong>

                                            <?= htmlspecialchars(

                                                $row[

                                                    'nama'

                                                ]

                                            ); ?>

                                        </strong>


                                        <small>

                                            <?= htmlspecialchars(

                                                $row[

                                                    'no_hp'

                                                ]

                                            ); ?>

                                        </small>

                                    </div>

                                </td>



                                <td>

                                    <strong>

                                        Rp<?= number_format(

                                            $row[

                                                'total_harga'

                                            ],

                                            0,

                                            ',',

                                            '.'

                                        ); ?>

                                    </strong>

                                </td>



                                <td>

                                    <span
                                        class="payment-badge"
                                    >

                                        <?= strtoupper(

                                            htmlspecialchars(

                                                $row[

                                                    'metode_pembayaran'

                                                ]

                                            )

                                        ); ?>

                                    </span>

                                </td>



                                <td>

                                    <span
                                        class="order-status
                                        status-<?= htmlspecialchars(

                                            $status

                                        ); ?>"
                                    >

                                        <?= ucfirst(

                                            str_replace(

                                                '_',

                                                ' ',

                                                $status

                                            )

                                        ); ?>

                                    </span>

                                </td>



                                <td>

                                    <?= date(

                                        'd-m-Y H:i',

                                        strtotime(

                                            $row[

                                                'created_at'

                                            ]

                                        )

                                    ); ?>

                                </td>



                                <td>

                                    <a
                                        href="detail.php?id=<?= $row['id']; ?>"
                                        class="detail-button"
                                    >

                                        👁️ Detail

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>


                            <td
                                colspan="8"
                                class="empty-table"
                            >

                                <div>

                                    🛒

                                </div>


                                <strong>


                                    <?php if (

                                        $filterStatus !== ''

                                    ): ?>


                                        Tidak ada pesanan

                                        dengan status ini.


                                    <?php else: ?>


                                        Belum ada pesanan


                                    <?php endif; ?>


                                </strong>


                                <p>


                                    <?php if (

                                        $filterStatus !== ''

                                    ): ?>


                                        Coba pilih filter

                                        status lainnya.


                                    <?php else: ?>


                                        Pesanan pelanggan

                                        akan muncul di sini.


                                    <?php endif; ?>


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

<script>


// =====================================================
// NOTIFIKASI PESANAN BARU
// =====================================================

let pesananTerakhir =
    localStorage.getItem(
        'niswah_pesanan_terakhir'
    );


// =====================================================
// FORMAT RUPIAH
// =====================================================

function formatRupiah(
    angka
) {

    return 'Rp' +
        Number(
            angka
        ).toLocaleString(
            'id-ID'
        );

}


// =====================================================
// CEK PESANAN BARU
// =====================================================

async function cekPesananBaru() {

    try {

        const response =
            await fetch(
                'cek-baru.php?_=' +
                Date.now()
            );


        const data =
            await response.json();


        if (
            !data.success ||
            !data.pesanan
        ) {

            return;

        }


        const pesanan =
            data.pesanan;


        const id =
            String(
                pesanan.id
            );


        // =================================================
        // PESANAN BARU
        // =================================================

        if (
            pesananTerakhir !== id
        ) {

            // Simpan ID terbaru

            localStorage.setItem(
                'niswah_pesanan_terakhir',
                id
            );


            pesananTerakhir =
                id;


            tampilkanNotifikasi(
                pesanan
            );

        }

    }

    catch (error) {

        console.log(
            'Gagal mengecek pesanan:',
            error
        );

    }

}


// =====================================================
// TAMPILKAN NOTIFIKASI
// =====================================================

function tampilkanNotifikasi(
    pesanan
) {

    const notification =
        document.getElementById(
            'newOrderNotification'
        );


    const code =
        document.getElementById(
            'newOrderCode'
        );


    const customer =
        document.getElementById(
            'newOrderCustomer'
        );


    const total =
        document.getElementById(
            'newOrderTotal'
        );


    const view =
        document.getElementById(
            'newOrderView'
        );


    code.textContent =
        pesanan.kode_pesanan;


    customer.textContent =
        pesanan.nama;


    total.textContent =
        formatRupiah(
            pesanan.total_harga
        );


    view.href =
        'detail.php?id=' +
        pesanan.id;


    notification.classList.add(
        'show'
    );


    // =================================================
    // BUNYI
    // =================================================

    bunyiNotifikasi();


    // =================================================
    // AUTO HILANG 10 DETIK
    // =================================================

    setTimeout(
        function () {

            tutupNotifikasi();

        },

        10000

    );

}


// =====================================================
// TUTUP
// =====================================================

function tutupNotifikasi() {

    const notification =
        document.getElementById(
            'newOrderNotification'
        );


    notification.classList.remove(
        'show'
    );

}


// =====================================================
// BUNYI NOTIFIKASI
// =====================================================

function bunyiNotifikasi() {

    try {

        const AudioContext =
            window.AudioContext ||
            window.webkitAudioContext;


        if (!AudioContext) {

            return;

        }


        const audio =
            new AudioContext();


        const oscillator =
            audio.createOscillator();


        const gain =
            audio.createGain();


        oscillator.connect(
            gain
        );


        gain.connect(
            audio.destination
        );


        oscillator.frequency.value =
            880;


        gain.gain.value =
            0.08;


        oscillator.start();


        oscillator.stop(
            audio.currentTime +
            0.18
        );

    }

    catch (error) {

        console.log(
            'Audio tidak tersedia.'
        );

    }

}


// =====================================================
// CEK PERTAMA
// =====================================================

cekPesananBaru();


// =====================================================
// CEK SETIAP 5 DETIK
// =====================================================

setInterval(
    cekPesananBaru,
    5000
);


</script>

</body>

</html>