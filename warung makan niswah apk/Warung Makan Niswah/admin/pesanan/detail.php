<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID PESANAN
// =====================================================

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


// =====================================================
// DATA PESANAN + CUSTOMER
// =====================================================

$stmt = $conn->prepare("
    SELECT
        p.*,
        pl.nama,
        pl.no_hp,
        pl.alamat
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    header("Location: index.php");
    exit;
}

$pesanan = $result->fetch_assoc();

$stmt->close();


// =====================================================
// DETAIL PESANAN
// =====================================================

$stmt = $conn->prepare("
    SELECT *
    FROM detail_pesanan
    WHERE pesanan_id = ?
    ORDER BY id ASC
");

$stmt->bind_param("i", $id);
$stmt->execute();

$detailResult = $stmt->get_result();


// =====================================================
// STATUS PESANAN
// =====================================================

$statusSekarang = $pesanan['status_pesanan'];

$nextStatus = null;
$nextLabel = null;
$nextIcon = null;

switch ($statusSekarang) {

    case 'menunggu':

        $nextStatus = 'diterima';
        $nextLabel = 'Terima Pesanan';
        $nextIcon = '🟢';

        break;

    case 'diterima':

        $nextStatus = 'diproses';
        $nextLabel = 'Mulai Proses';
        $nextIcon = '👨‍🍳';

        break;

    case 'diproses':

        $nextStatus = 'siap_diantar';
        $nextLabel = 'Siap Diantar';
        $nextIcon = '🛵';

        break;

    case 'siap_diantar':

        $nextStatus = 'selesai';
        $nextLabel = 'Selesaikan Pesanan';
        $nextIcon = '✅';

        break;
}


// =====================================================
// LABEL STATUS PESANAN
// =====================================================

$statusLabel = ucfirst(
    str_replace(
        '_',
        ' ',
        $statusSekarang
    )
);


// =====================================================
// DATA PEMBAYARAN
// =====================================================

$metodePembayaran = strtolower(
    trim(
        $pesanan['metode_pembayaran'] ?? ''
    )
);

$statusPembayaran = strtolower(
    trim(
        $pesanan['status_pembayaran'] ?? 'belum_bayar'
    )
);

$buktiPembayaran =
    $pesanan['bukti_pembayaran'] ?? '';

$tanggalPembayaran =
    $pesanan['tanggal_pembayaran'] ?? '';


// =====================================================
// LABEL PEMBAYARAN
// =====================================================

$paymentStatusClass = 'payment-belum';
$paymentStatusText = '⏳ Belum Bayar';

switch ($statusPembayaran) {

    case 'menunggu_verifikasi':

        $paymentStatusClass =
            'payment-menunggu';

        $paymentStatusText =
            '🔎 Menunggu Verifikasi';

        break;

    case 'terverifikasi':

        $paymentStatusClass =
            'payment-terverifikasi';

        $paymentStatusText =
            '✅ Pembayaran Terverifikasi';

        break;

    case 'ditolak':

        $paymentStatusClass =
            'payment-ditolak';

        $paymentStatusText =
            '❌ Pembayaran Ditolak';

        break;

    case 'belum_bayar':
    default:

        $paymentStatusClass =
            'payment-belum';

        $paymentStatusText =
            '⏳ Belum Bayar';

        break;
}


// =====================================================
// FORMAT METODE PEMBAYARAN
// =====================================================

if ($metodePembayaran === 'qris') {

    $metodeLabel = 'QRIS';

} elseif (!empty($metodePembayaran)) {

    $metodeLabel = strtoupper(
        str_replace(
            '_',
            ' ',
            $metodePembayaran
        )
    );

} else {

    $metodeLabel = '-';
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
        Detail Pesanan |
        Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        /* =====================================================
           STATUS PESANAN
        ===================================================== */

        .status-box {
            text-align: center;
            padding: 10px 0 5px;
        }

        .current-status-label {
            display: block;
            margin-bottom: 10px;
            color: #888;
            font-size: 12px;
        }

        .status {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 800;
        }

        .status.menunggu {
            background: #fff4d6;
            color: #a86b00;
        }

        .status.diterima {
            background: #e9f4ff;
            color: #1877b7;
        }

        .status.diproses {
            background: #fff0df;
            color: #e87518;
        }

        .status.siap_diantar {
            background: #eee8ff;
            color: #6845b5;
        }

        .status.selesai {
            background: #e6f7ed;
            color: #20834d;
        }

        .status.dibatalkan {
            background: #ffe8e8;
            color: #c62828;
        }


        /* =====================================================
           ACTION BUTTON
        ===================================================== */

        .status-action {
            margin-top: 22px;
        }

        .next-status-button {
            width: 100%;
            border: 0;
            padding: 15px 18px;
            background: #e87518;
            color: white;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: .2s;
        }

        .next-status-button:hover {
            background: #d76510;
            transform: translateY(-1px);
        }

        .cancel-button {
            width: 100%;
            margin-top: 10px;
            padding: 12px;
            border: 1px solid #f0b5b5;
            background: white;
            color: #c62828;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .cancel-button:hover {
            background: #fff5f5;
        }

        .finished-message {
            margin-top: 20px;
            padding: 14px;
            background: #e6f7ed;
            color: #20834d;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
        }

        .cancelled-message {
            margin-top: 20px;
            padding: 14px;
            background: #ffe8e8;
            color: #c62828;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
        }


        /* =====================================================
           INFO CUSTOMER
        ===================================================== */

        .detail-info > div {
            margin-bottom: 16px;
        }

        .detail-info > div:last-child {
            margin-bottom: 0;
        }

        .detail-info span {
            display: block;
            margin-bottom: 5px;
            color: #888;
            font-size: 11px;
        }

        .detail-info strong {
            display: block;
            line-height: 1.5;
        }


        /* =====================================================
           PAYMENT CARD
        ===================================================== */

        .payment-card {
            margin-top: 20px;
        }

        .payment-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .payment-info-item {
            padding: 14px;
            background: #fafafa;
            border-radius: 11px;
        }

        .payment-info-item span {
            display: block;
            margin-bottom: 7px;
            color: #888;
            font-size: 11px;
        }

        .payment-info-item strong {
            font-size: 13px;
        }

        .payment-method {
            display: inline-block;
            padding: 7px 11px;
            background: #f5f5f5;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
        }

        .payment-status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }

        .payment-belum {
            background: #fff4d6;
            color: #a86b00;
        }

        .payment-menunggu {
            background: #fff0df;
            color: #d76510;
        }

        .payment-terverifikasi {
            background: #e6f7ed;
            color: #20834d;
        }

        .payment-ditolak {
            background: #ffe8e8;
            color: #c62828;
        }

        .proof-box {
            margin-top: 18px;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #eee;
            border-radius: 11px;
        }

        .proof-box-title {
            display: block;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 800;
        }

        .proof-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 14px;
            background: #e87518;
            color: white;
            text-decoration: none;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 800;
        }

        .proof-link:hover {
            background: #d76510;
        }

        .proof-date {
            display: block;
            margin-top: 10px;
            color: #888;
            font-size: 11px;
        }

        .payment-note {
            margin-top: 15px;
            padding: 13px 14px;
            border-radius: 10px;
            background: #fff8ef;
            color: #805d39;
            font-size: 11px;
            line-height: 1.6;
        }

        .payment-note strong {
            font-weight: 800;
        }


        /* =====================================================
           ORDER DETAIL
        ===================================================== */

        .order-detail-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }

        .order-detail-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .order-detail-icon {
            width: 45px;
            height: 45px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff3e5;
            border-radius: 11px;
            font-size: 22px;
        }

        .order-detail-main strong {
            display: block;
            font-size: 14px;
        }

        .order-detail-main span {
            display: block;
            margin-top: 4px;
            color: #888;
            font-size: 11px;
        }

        .selected-options {
            margin: 0 0 10px 57px;
            padding: 10px 12px;
            background: #fafafa;
            border-radius: 9px;
        }

        .selected-options > div {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
            font-size: 11px;
        }

        .selected-options span {
            color: #888;
        }

        .selected-options strong {
            text-align: right;
        }


        /* =====================================================
           TOTAL
        ===================================================== */

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 20px 0 5px;
        }

        .order-total span {
            display: block;
            margin-bottom: 5px;
            color: #888;
            font-size: 11px;
        }

        .order-total strong {
            font-size: 17px;
        }

        .order-total > div:last-child {
            text-align: right;
        }

        .order-total > div:last-child strong {
            color: #e87518;
            font-size: 22px;
        }


        /* =====================================================
           NOTE
        ===================================================== */

        .order-note {
            margin-top: 20px;
            padding: 15px;
            background: #fff8ef;
            border-radius: 11px;
        }

        .order-note strong {
            font-size: 12px;
        }

        .order-note p {
            margin: 7px 0 0;
            color: #666;
            font-size: 12px;
            line-height: 1.6;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .detail-grid {
                grid-template-columns: 1fr !important;
            }

            .page-header {
                align-items: flex-start;
                gap: 10px;
            }

            .selected-options {
                margin-left: 0;
            }

            .payment-box {
                grid-template-columns: 1fr;
            }

            .order-detail-item {
                align-items: flex-start;
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


            <a
                href="index.php"
                class="active"
            >

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


            <a href="../laporan/index.php">

                <span>📈</span>

                <span>
                    Laporan
                </span>

            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../logout.php">

                <span>🚪</span>

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


        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="page-header">

            <div>

                <h1>
                    Detail Pesanan
                </h1>

                <p>
                    <?= htmlspecialchars(
                        $pesanan['kode_pesanan']
                    ); ?>
                </p>

            </div>


            <a
                href="index.php"
                class="btn-secondary"
            >
                ← Kembali
            </a>

        </header>



        <!-- =================================================
             CUSTOMER + STATUS
        ================================================== -->

        <div class="detail-grid">


            <!-- =================================================
                 CUSTOMER
            ================================================== -->

            <div class="form-card">

                <h3>
                    👤 Informasi Pelanggan
                </h3>


                <div class="detail-info">


                    <div>

                        <span>
                            Nama
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $pesanan['nama']
                            ); ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Nomor HP
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $pesanan['no_hp']
                            ); ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Alamat / Lokasi
                        </span>

                        <strong>
                            <?= nl2br(
                                htmlspecialchars(
                                    $pesanan['alamat']
                                )
                            ); ?>
                        </strong>

                    </div>


                </div>

            </div>



            <!-- =================================================
                 STATUS PESANAN
            ================================================== -->

            <div class="form-card">

                <h3>
                    📦 Status Pesanan
                </h3>


                <div class="status-box">


                    <span class="current-status-label">
                        Status Saat Ini
                    </span>


                    <span
                        class="status <?= htmlspecialchars(
                            $statusSekarang
                        ); ?>"
                    >
                        <?= htmlspecialchars(
                            $statusLabel
                        ); ?>
                    </span>



                    <!-- =========================================
                         NEXT ACTION
                    ========================================== -->

                    <?php if ($nextStatus !== null): ?>

                        <div class="status-action">

                            <form
                                action="status.php"
                                method="POST"
                                onsubmit="return confirm('Ubah status pesanan menjadi <?= htmlspecialchars($nextLabel); ?>?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $pesanan['id']; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="status"
                                    value="<?= htmlspecialchars($nextStatus); ?>"
                                >

                                <button
                                    type="submit"
                                    class="next-status-button"
                                >

                                    <?= $nextIcon; ?>

                                    <?= htmlspecialchars(
                                        $nextLabel
                                    ); ?>

                                </button>

                            </form>


                            <?php if (
                                $statusSekarang !== 'dibatalkan'
                            ): ?>

                                <form
                                    action="status.php"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $pesanan['id']; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="dibatalkan"
                                    >

                                    <button
                                        type="submit"
                                        class="cancel-button"
                                    >
                                        ❌ Batalkan Pesanan
                                    </button>

                                </form>

                            <?php endif; ?>


                        </div>


                    <?php elseif (
                        $statusSekarang === 'selesai'
                    ): ?>

                        <div class="finished-message">
                            ✅ Pesanan sudah selesai.
                        </div>


                    <?php elseif (
                        $statusSekarang === 'dibatalkan'
                    ): ?>

                        <div class="cancelled-message">
                            ❌ Pesanan ini telah dibatalkan.
                        </div>

                    <?php endif; ?>


                </div>

            </div>


        </div>



        <!-- =================================================
             PEMBAYARAN
        ================================================== -->

        <div
            class="form-card payment-card"
        >

            <h3>
                💳 Informasi Pembayaran
            </h3>


            <div class="payment-box">


                <!-- METODE -->

                <div class="payment-info-item">

                    <span>
                        Metode Pembayaran
                    </span>

                    <strong>

                        <span class="payment-method">
                            <?= htmlspecialchars(
                                $metodeLabel
                            ); ?>
                        </span>

                    </strong>

                </div>


                <!-- STATUS -->

                <div class="payment-info-item">

                    <span>
                        Status Pembayaran
                    </span>

                    <strong>

                        <span
                            class="payment-status <?= htmlspecialchars(
                                $paymentStatusClass
                            ); ?>"
                        >
                            <?= htmlspecialchars(
                                $paymentStatusText
                            ); ?>
                        </span>

                    </strong>

                </div>


            </div>



            <?php if (
                $metodePembayaran === 'qris'
            ): ?>


                <!-- =================================================
                     BUKTI PEMBAYARAN
                ================================================== -->

                <?php if (
                    !empty($buktiPembayaran)
                ): ?>

                    <div class="proof-box">

                        <span class="proof-box-title">
                            📷 Bukti Pembayaran
                        </span>


                        <a
                            href="../../assets/uploads/bukti-pembayaran/<?= htmlspecialchars(
                                $buktiPembayaran
                            ); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="proof-link"
                        >
                            👁️ Lihat Bukti Pembayaran
                        </a>


                        <?php if (
                            !empty($tanggalPembayaran)
                        ): ?>

                            <span class="proof-date">

                                📅
                                Diupload:
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $tanggalPembayaran
                                    )
                                ); ?>

                            </span>

                        <?php endif; ?>


                    </div>


                <?php else: ?>


                    <div class="payment-note">

                        <strong>
                            ℹ️ Belum ada bukti pembayaran.
                        </strong>

                        Customer belum mengupload bukti
                        pembayaran QRIS untuk pesanan ini.

                    </div>


                <?php endif; ?>



                <!-- =================================================
                     CATATAN STATUS PEMBAYARAN
                ================================================== -->

                <?php if (
                    $statusPembayaran ===
                    'menunggu_verifikasi'
                ): ?>

                    <div class="payment-note">

                        <strong>
                            🔎 Menunggu Verifikasi Admin
                        </strong>

                        <br>

                        Bukti pembayaran sudah dikirim
                        oleh customer. Silakan lakukan
                        verifikasi melalui menu
                        <strong>Pembayaran</strong>.

                        <br><br>

                        Status pesanan tetap mengikuti
                        alur pesanan dan tidak otomatis
                        berubah karena verifikasi pembayaran.

                    </div>


                <?php elseif (
                    $statusPembayaran ===
                    'terverifikasi'
                ): ?>

                    <div class="payment-note">

                        <strong>
                            ✅ Pembayaran Terverifikasi
                        </strong>

                        <br>

                        Bukti pembayaran telah diterima
                        dan diverifikasi oleh admin.

                    </div>


                <?php elseif (
                    $statusPembayaran ===
                    'ditolak'
                ): ?>

                    <div class="payment-note">

                        <strong>
                            ❌ Pembayaran Ditolak
                        </strong>

                        <br>

                        Bukti pembayaran sebelumnya
                        ditolak oleh admin. Customer
                        dapat mengirim ulang bukti pembayaran.

                    </div>


                <?php elseif (
                    $statusPembayaran ===
                    'belum_bayar'
                ): ?>

                    <div class="payment-note">

                        <strong>
                            ⏳ Belum Bayar
                        </strong>

                        <br>

                        Customer belum mengirimkan
                        bukti pembayaran QRIS.

                    </div>

                <?php endif; ?>


            <?php endif; ?>


        </div>



        <!-- =================================================
             ISI PESANAN
        ================================================== -->

        <div
            class="table-card"
            style="margin-top:20px;"
        >


            <div class="table-header">

                <div>

                    <h3>
                        🍽️ Isi Pesanan
                    </h3>

                    <p>
                        <?= htmlspecialchars(
                            $pesanan['kode_pesanan']
                        ); ?>
                    </p>

                </div>

            </div>



            <div class="order-detail-list">


                <?php if (
                    $detailResult->num_rows > 0
                ): ?>


                    <?php while (
                        $detail =
                        $detailResult->fetch_assoc()
                    ): ?>


                        <!-- =================================
                             ITEM
                        ================================== -->

                        <div class="order-detail-item">


                            <div class="order-detail-main">


                                <div class="order-detail-icon">
                                    🍛
                                </div>


                                <div>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $detail['nama_menu']
                                        ); ?>
                                    </strong>


                                    <span>

                                        <?= (int) $detail['jumlah']; ?>

                                        ×

                                        Rp<?= number_format(
                                            $detail['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </span>

                                </div>


                            </div>


                            <strong>

                                Rp<?= number_format(
                                    $detail['subtotal'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>


                        </div>



                        <!-- =================================
                             PILIHAN / VARIAN
                        ================================== -->

                        <?php

                        $pilihanStmt = $conn->prepare("
                            SELECT *
                            FROM detail_pilihan_pesanan
                            WHERE detail_pesanan_id = ?
                            ORDER BY id ASC
                        ");

                        $pilihanStmt->bind_param(
                            "i",
                            $detail['id']
                        );

                        $pilihanStmt->execute();

                        $pilihanResult =
                            $pilihanStmt->get_result();

                        ?>


                        <?php if (
                            $pilihanResult->num_rows > 0
                        ): ?>


                            <div class="selected-options">


                                <?php while (
                                    $pilihan =
                                    $pilihanResult->fetch_assoc()
                                ): ?>


                                    <?php

                                    $tipePilihan =
                                        strtolower(
                                            trim(
                                                $pilihan[
                                                    'tipe_pilihan'
                                                ] ?? ''
                                            )
                                        );


                                    if (
                                        $tipePilihan ===
                                        'varian'
                                    ) {

                                        $labelPilihan =
                                            '🍚 Penyajian';

                                    } elseif (
                                        $tipePilihan ===
                                        'multiple'
                                    ) {

                                        $labelPilihan =
                                            '➕ Pilihan';

                                    } else {

                                        $labelPilihan =
                                            ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $tipePilihan
                                                )
                                            );
                                    }

                                    ?>


                                    <div>


                                        <span>
                                            <?= htmlspecialchars(
                                                $labelPilihan
                                            ); ?>
                                        </span>


                                        <strong>

                                            <?= htmlspecialchars(
                                                $pilihan[
                                                    'nama_pilihan'
                                                ]
                                            ); ?>


                                            <?php if (
                                                floatval(
                                                    $pilihan[
                                                        'harga_tambahan'
                                                    ]
                                                ) > 0
                                            ): ?>

                                                +

                                                Rp<?= number_format(
                                                    $pilihan[
                                                        'harga_tambahan'
                                                    ],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            <?php endif; ?>


                                        </strong>


                                    </div>


                                <?php endwhile; ?>


                            </div>


                        <?php endif; ?>


                        <?php

                        $pilihanStmt->close();

                        ?>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">
                            🍽️
                        </div>

                        <h4>
                            Tidak ada detail pesanan
                        </h4>

                    </div>


                <?php endif; ?>


            </div>



            <!-- =================================================
                 TOTAL
            ================================================== -->

            <div class="order-total">


                <div>

                    <span>
                        Metode Pembayaran
                    </span>

                    <strong>

                        <span class="payment-method">

                            <?= htmlspecialchars(
                                $metodeLabel
                            ); ?>

                        </span>

                    </strong>

                </div>


                <div>

                    <span>
                        Total Pesanan
                    </span>

                    <strong>

                        Rp<?= number_format(
                            $pesanan['total_harga'],
                            0,
                            ',',
                            '.'
                        ); ?>

                    </strong>

                </div>


            </div>



            <!-- =================================================
                 CATATAN
            ================================================== -->

            <?php if (
                !empty(
                    $pesanan['catatan']
                )
            ): ?>


                <div class="order-note">

                    <strong>
                        📝 Catatan Customer
                    </strong>

                    <p>

                        <?= nl2br(
                            htmlspecialchars(
                                $pesanan['catatan']
                            )
                        ); ?>

                    </p>

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>

</body>

</html>