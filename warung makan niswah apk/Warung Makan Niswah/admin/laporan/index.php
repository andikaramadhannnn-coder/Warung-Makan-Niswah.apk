<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// FILTER TANGGAL
// =====================================================

$tanggalMulai = $_GET['mulai'] ?? date('Y-m-01');
$tanggalAkhir = $_GET['akhir'] ?? date('Y-m-d');


// Validasi format tanggal sederhana
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai)) {
    $tanggalMulai = date('Y-m-01');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAkhir)) {
    $tanggalAkhir = date('Y-m-d');
}


// =====================================================
// TOTAL PENDAPATAN
// =====================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(total_harga), 0) AS total_pendapatan,
        COUNT(*) AS total_transaksi
    FROM pesanan
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND status_pesanan != 'dibatalkan'
");

$stmt->bind_param(
    "ss",
    $tanggalMulai,
    $tanggalAkhir
);

$stmt->execute();

$summaryResult = $stmt->get_result();
$summary = $summaryResult->fetch_assoc();

$stmt->close();


$totalPendapatan = $summary['total_pendapatan'] ?? 0;
$totalTransaksi = $summary['total_transaksi'] ?? 0;


// =====================================================
// TOTAL MENU TERJUAL
// =====================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(dp.jumlah), 0) AS total_menu
    FROM detail_pesanan dp
    INNER JOIN pesanan p
        ON dp.pesanan_id = p.id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    AND p.status_pesanan != 'dibatalkan'
");

$stmt->bind_param(
    "ss",
    $tanggalMulai,
    $tanggalAkhir
);

$stmt->execute();

$menuResult = $stmt->get_result();
$menuData = $menuResult->fetch_assoc();

$stmt->close();

$totalMenu = $menuData['total_menu'] ?? 0;


// =====================================================
// TOTAL PELANGGAN
// =====================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(DISTINCT pelanggan_id) AS total_pelanggan
    FROM pesanan
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND status_pesanan != 'dibatalkan'
");

$stmt->bind_param(
    "ss",
    $tanggalMulai,
    $tanggalAkhir
);

$stmt->execute();

$pelangganResult = $stmt->get_result();
$pelangganData = $pelangganResult->fetch_assoc();

$stmt->close();

$totalPelanggan = $pelangganData['total_pelanggan'] ?? 0;


// =====================================================
// PESANAN PER STATUS
// =====================================================

$statusData = [];

$statusQuery = "
    SELECT
        status_pesanan,
        COUNT(*) AS jumlah
    FROM pesanan
    WHERE DATE(created_at) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'
    GROUP BY status_pesanan
";

$statusResult = $conn->query($statusQuery);

if ($statusResult) {

    while ($row = $statusResult->fetch_assoc()) {

        $statusData[$row['status_pesanan']] =
            $row['jumlah'];

    }

}


// =====================================================
// MENU TERLARIS
// =====================================================

$stmt = $conn->prepare("
    SELECT
        dp.nama_menu,
        SUM(dp.jumlah) AS jumlah_terjual,
        SUM(dp.subtotal) AS total_penjualan
    FROM detail_pesanan dp
    INNER JOIN pesanan p
        ON dp.pesanan_id = p.id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    AND p.status_pesanan != 'dibatalkan'
    GROUP BY dp.menu_id, dp.nama_menu
    ORDER BY jumlah_terjual DESC
    LIMIT 5
");

$stmt->bind_param(
    "ss",
    $tanggalMulai,
    $tanggalAkhir
);

$stmt->execute();

$terlarisResult = $stmt->get_result();

$stmt->close();


// =====================================================
// DAFTAR TRANSAKSI
// =====================================================

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.kode_pesanan,
        p.total_harga,
        p.metode_pembayaran,
        p.status_pesanan,
        p.created_at,
        pl.nama
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    WHERE DATE(p.created_at) BETWEEN ? AND ?
    ORDER BY p.created_at DESC
");

$stmt->bind_param(
    "ss",
    $tanggalMulai,
    $tanggalAkhir
);

$stmt->execute();

$transaksiResult = $stmt->get_result();

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
        Laporan | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        /* =================================================
           FILTER
        ================================================= */

        .report-filter {

            display: flex;

            align-items: flex-end;

            gap: 12px;

            flex-wrap: wrap;

        }


        .report-filter-group {

            display: flex;

            flex-direction: column;

            gap: 5px;

        }


        .report-filter-group label {

            font-size: 11px;

            color: #777;

            font-weight: 700;

        }


        .report-filter-group input {

            padding: 10px 12px;

            border: 1px solid #ddd;

            border-radius: 9px;

            font-family: inherit;

            font-size: 12px;

            outline: none;

        }


        .report-filter-group input:focus {

            border-color: #e87518;

        }


        .report-button {

            border: 0;

            padding: 10px 16px;

            border-radius: 9px;

            background: #e87518;

            color: white;

            font-size: 12px;

            font-weight: 800;

            cursor: pointer;

        }


        .report-button:hover {

            background: #d76510;

        }


        .print-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 10px 16px;

            border-radius: 9px;

            background: #333;

            color: white;

            text-decoration: none;

            font-size: 12px;

            font-weight: 800;

            cursor: pointer;

            border: 0;

        }


        .print-button:hover {

            background: #222;

        }


        /* =================================================
           SUMMARY
        ================================================= */

        .report-summary {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 16px;

            margin-bottom: 20px;

        }


        .report-card {

            background: white;

            border-radius: 14px;

            padding: 20px;

            box-shadow:
                0 4px 18px rgba(0,0,0,.05);

        }


        .report-card-icon {

            width: 42px;

            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #fff0df;

            border-radius: 11px;

            font-size: 20px;

            margin-bottom: 12px;

        }


        .report-card span {

            display: block;

            color: #888;

            font-size: 11px;

            margin-bottom: 5px;

        }


        .report-card strong {

            display: block;

            font-size: 20px;

        }


        /* =================================================
           REPORT GRID
        ================================================= */

        .report-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;

            margin-bottom: 20px;

        }


        .report-box {

            background: white;

            border-radius: 14px;

            padding: 20px;

            box-shadow:
                0 4px 18px rgba(0,0,0,.05);

        }


        .report-box h3 {

            margin: 0 0 4px;

            font-size: 15px;

        }


        .report-box > p {

            margin: 0 0 15px;

            color: #888;

            font-size: 11px;

        }


        /* =================================================
           TERLARIS
        ================================================= */

        .best-menu {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            padding: 12px 0;

            border-bottom: 1px solid #eee;

        }


        .best-menu:last-child {

            border-bottom: 0;

        }


        .best-menu-left {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .best-rank {

            width: 30px;

            height: 30px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            background: #fff0df;

            color: #e87518;

            font-size: 12px;

            font-weight: 800;

        }


        .best-menu-name strong {

            display: block;

            font-size: 12px;

        }


        .best-menu-name small {

            display: block;

            margin-top: 3px;

            color: #999;

            font-size: 10px;

        }


        .best-total {

            text-align: right;

        }


        .best-total strong {

            display: block;

            font-size: 12px;

        }


        .best-total small {

            color: #999;

            font-size: 10px;

        }


        /* =================================================
           STATUS
        ================================================= */

        .status-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 10px 0;

            border-bottom: 1px solid #eee;

        }


        .status-row:last-child {

            border-bottom: 0;

        }


        .status-name {

            font-size: 12px;

            color: #555;

        }


        .status-count {

            padding: 5px 10px;

            background: #f5f5f5;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 800;

        }


        /* =================================================
           STATUS COLORS
        ================================================= */

        .status-menunggu {

            color: #a86b00;

        }


        .status-diterima {

            color: #1877b7;

        }


        .status-diproses {

            color: #e87518;

        }


        .status-siap_diantar {

            color: #6845b5;

        }


        .status-selesai {

            color: #20834d;

        }


        .status-dibatalkan {

            color: #c62828;

        }


        /* =================================================
           TRANSACTION
        ================================================= */

        .transaction-code {

            font-weight: 800;

            font-size: 12px;

        }


        .transaction-customer {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .transaction-customer strong {

            font-size: 12px;

        }


        .transaction-customer small {

            color: #999;

            font-size: 10px;

        }


        .transaction-total {

            font-weight: 800;

            white-space: nowrap;

        }


        .transaction-date {

            color: #777;

            font-size: 11px;

            white-space: nowrap;

        }


        .transaction-method {

            display: inline-block;

            padding: 6px 9px;

            background: #f5f5f5;

            border-radius: 7px;

            font-size: 10px;

            font-weight: 800;

        }


        .transaction-status {

            font-size: 10px;

            font-weight: 800;

            white-space: nowrap;

        }


        .empty-report {

            text-align: center;

            padding: 30px 10px;

            color: #888;

            font-size: 12px;

        }


        /* =================================================
           PRINT
        ================================================= */

        @media print {

            body {

                background: white !important;

            }

            .sidebar,
            .page-header > a,
            .report-filter,
            .print-button {

                display: none !important;

            }

            .main-content {

                margin: 0 !important;

                padding: 0 !important;

            }

            .report-card,
            .report-box,
            .table-card {

                box-shadow: none !important;

                border: 1px solid #ddd;

            }

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 900px) {

            .report-summary {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .report-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 600px) {

            .report-summary {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

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


            <a href="../menu/index.php">

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


            <a
                href="index.php"
                class="active"
            >

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



    <!-- =================================================
         MAIN
    ================================================== -->

    <main class="main-content">


        <header class="page-header">

            <div>

                <h1>
                    Laporan Penjualan
                </h1>

                <p>
                    Rekap transaksi dan penjualan
                    Warung Makan Niswah.
                </p>

            </div>


            <button
                type="button"
                class="print-button"
                onclick="window.print()"
            >

                🖨️ Cetak Laporan

            </button>

        </header>



        <!-- =================================================
             FILTER
        ================================================== -->

        <div class="table-card"
             style="margin-bottom:20px;">

            <div class="table-header">

                <div>

                    <h3>
                        📅 Periode Laporan
                    </h3>

                    <p>
                        Pilih periode untuk melihat laporan.
                    </p>

                </div>

            </div>


            <form
                method="GET"
                class="report-filter"
            >

                <div class="report-filter-group">

                    <label>
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"
                        name="mulai"
                        value="<?= htmlspecialchars($tanggalMulai); ?>"
                        required
                    >

                </div>


                <div class="report-filter-group">

                    <label>
                        Tanggal Akhir
                    </label>

                    <input
                        type="date"
                        name="akhir"
                        value="<?= htmlspecialchars($tanggalAkhir); ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="report-button"
                >

                    🔍 Tampilkan

                </button>

            </form>

        </div>



        <!-- =================================================
             SUMMARY
        ================================================== -->

        <div class="report-summary">


            <div class="report-card">

                <div class="report-card-icon">
                    💰
                </div>

                <span>
                    Total Pendapatan
                </span>

                <strong>
                    Rp<?= number_format(
                        $totalPendapatan,
                        0,
                        ',',
                        '.'
                    ); ?>
                </strong>

            </div>


            <div class="report-card">

                <div class="report-card-icon">
                    🧾
                </div>

                <span>
                    Total Transaksi
                </span>

                <strong>
                    <?= number_format(
                        $totalTransaksi
                    ); ?>
                </strong>

            </div>


            <div class="report-card">

                <div class="report-card-icon">
                    🍛
                </div>

                <span>
                    Menu Terjual
                </span>

                <strong>
                    <?= number_format(
                        $totalMenu
                    ); ?>
                </strong>

            </div>


            <div class="report-card">

                <div class="report-card-icon">
                    👥
                </div>

                <span>
                    Pelanggan
                </span>

                <strong>
                    <?= number_format(
                        $totalPelanggan
                    ); ?>
                </strong>

            </div>


        </div>



        <!-- =================================================
             REPORT GRID
        ================================================== -->

        <div class="report-grid">


            <!-- MENU TERLARIS -->

            <div class="report-box">

                <h3>
                    🏆 Menu Terlaris
                </h3>

                <p>
                    5 menu dengan penjualan tertinggi.
                </p>


                <?php if (
                    $terlarisResult &&
                    $terlarisResult->num_rows > 0
                ): ?>


                    <?php

                    $rank = 1;

                    while (
                        $row =
                        $terlarisResult->fetch_assoc()
                    ):

                    ?>


                        <div class="best-menu">


                            <div class="best-menu-left">


                                <div class="best-rank">

                                    <?= $rank++; ?>

                                </div>


                                <div class="best-menu-name">

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['nama_menu']
                                        ); ?>

                                    </strong>

                                    <small>

                                        <?= number_format(
                                            $row['jumlah_terjual']
                                        ); ?>

                                        terjual

                                    </small>

                                </div>


                            </div>


                            <div class="best-total">

                                <strong>

                                    Rp<?= number_format(
                                        $row['total_penjualan'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>

                                <small>
                                    Penjualan
                                </small>

                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="empty-report">

                        🏆 Belum ada data penjualan.

                    </div>


                <?php endif; ?>


            </div>



            <!-- STATUS -->

            <div class="report-box">

                <h3>
                    📦 Status Pesanan
                </h3>

                <p>
                    Ringkasan status pesanan pada periode.
                </p>


                <?php

                $statusList = [

                    'menunggu' =>
                        'Menunggu',

                    'diterima' =>
                        'Diterima',

                    'diproses' =>
                        'Diproses',

                    'siap_diantar' =>
                        'Siap Diantar',

                    'selesai' =>
                        'Selesai',

                    'dibatalkan' =>
                        'Dibatalkan'

                ];


                foreach (
                    $statusList
                    as $key => $label
                ):

                    $jumlahStatus =
                        $statusData[$key] ?? 0;

                ?>


                    <div class="status-row">

                        <span
                            class="status-name status-<?= $key; ?>"
                        >

                            <?= $label; ?>

                        </span>


                        <span class="status-count">

                            <?= number_format(
                                $jumlahStatus
                            ); ?>

                        </span>

                    </div>


                <?php endforeach; ?>


            </div>


        </div>



        <!-- =================================================
             TRANSAKSI
        ================================================== -->

        <div class="table-card">


            <div class="table-header">

                <div>

                    <h3>
                        🧾 Detail Transaksi
                    </h3>

                    <p>

                        Periode:
                        <?= date(
                            'd-m-Y',
                            strtotime($tanggalMulai)
                        ); ?>

                        -
                        <?= date(
                            'd-m-Y',
                            strtotime($tanggalAkhir)
                        ); ?>

                    </p>

                </div>

            </div>


            <div class="table-wrapper">


                <table>


                    <thead>

                        <tr>

                            <th>
                                No
                            </th>

                            <th>
                                Kode
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

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (
                        $transaksiResult &&
                        $transaksiResult->num_rows > 0
                    ):

                        while (
                            $row =
                            $transaksiResult->fetch_assoc()
                        ):

                    ?>


                        <tr>


                            <td>
                                <?= $no++; ?>
                            </td>


                            <td>

                                <span class="transaction-code">

                                    <?= htmlspecialchars(
                                        $row['kode_pesanan']
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <div class="transaction-customer">

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['nama']
                                        ); ?>

                                    </strong>

                                </div>

                            </td>


                            <td>

                                <span class="transaction-total">

                                    Rp<?= number_format(
                                        $row['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <span class="transaction-method">

                                    <?= htmlspecialchars(
                                        strtoupper(
                                            $row['metode_pembayaran']
                                        )
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <span
                                    class="transaction-status status-<?= htmlspecialchars(
                                        $row['status_pesanan']
                                    ); ?>"
                                >

                                    <?= htmlspecialchars(
                                        $statusList[
                                            $row['status_pesanan']
                                        ]
                                        ??
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $row['status_pesanan']
                                            )
                                        )
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <span class="transaction-date">

                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $row['created_at']
                                        )
                                    ); ?>

                                </span>

                            </td>


                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>


                        <tr>

                            <td
                                colspan="7"
                                class="empty-table"
                            >

                                <div>
                                    📈
                                </div>

                                <strong>
                                    Tidak ada transaksi
                                </strong>

                                <p>
                                    Tidak ada data transaksi
                                    pada periode yang dipilih.
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

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const mulai =
            document.querySelector(
                'input[name="mulai"]'
            );

        const akhir =
            document.querySelector(
                'input[name="akhir"]'
            );


        if (!mulai || !akhir) {
            return;
        }


        akhir.addEventListener(
            'change',
            function () {

                if (
                    mulai.value &&
                    akhir.value &&
                    akhir.value < mulai.value
                ) {

                    alert(
                        'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.'
                    );

                    akhir.value =
                        mulai.value;

                }

            }
        );

    }
);

</script>


</body>

</html>