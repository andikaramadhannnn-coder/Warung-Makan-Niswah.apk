<?php

session_start();

require_once "includes/config.php";


/* =====================================================
   CEK DATA PESANAN
===================================================== */

if (!isset($_SESSION['pesanan_berhasil'])) {
    header("Location: index.php");
    exit;
}


$data = $_SESSION['pesanan_berhasil'];

$pesananId = (int) ($data['id'] ?? 0);

if ($pesananId <= 0) {
    header("Location: index.php");
    exit;
}


/* =====================================================
   AMBIL DATA TERBARU DARI DATABASE
   DATABASE JADI SUMBER STATUS UTAMA
===================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        kode_pesanan,
        total_harga,
        metode_pembayaran,
        status_pembayaran,
        bukti_pembayaran,
        tanggal_pembayaran
    FROM pesanan
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $pesananId);
$stmt->execute();

$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();

    header("Location: index.php");
    exit;
}

$pesanan = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   DATA PESANAN
===================================================== */

$kode = $pesanan['kode_pesanan'];

$total = (float) $pesanan['total_harga'];

$metode = strtolower(
    trim($pesanan['metode_pembayaran'])
);

$statusPembayaran =
    $pesanan['status_pembayaran']
    ?: 'belum_bayar';


/* =====================================================
   FLASH MESSAGE
===================================================== */

$uploadSuccess =
    $_SESSION['upload_success'] ?? '';

$uploadError =
    $_SESSION['upload_error'] ?? '';

unset($_SESSION['upload_success']);
unset($_SESSION['upload_error']);


/* =====================================================
   TENTUKAN TAMPILAN CUSTOMER
===================================================== */

$judul = 'Pesanan Berhasil Dibuat!';

$icon = '💳';

$pesan = 'Silakan lakukan pembayaran sesuai metode yang dipilih.';

if ($metode === 'qris') {

    /* ================================
       BELUM BAYAR
    ================================= */

    if ($statusPembayaran === 'belum_bayar') {

        $icon = '💳';

        $judul = 'Pesanan Berhasil Dibuat!';

        $pesan =
            'Silakan scan QRIS di bawah dan upload bukti pembayaran.';

    }


    /* ================================
       SUDAH UPLOAD
       CUSTOMER LANGSUNG DIANGGAP
       PESANAN BERHASIL
    ================================= */

    elseif (
        $statusPembayaran ===
        'menunggu_verifikasi'
    ) {

        $icon = '🎉';

        $judul = 'Pesanan Berhasil!';

        $pesan =
            'Bukti pembayaran sudah berhasil dikirim. Pesanan kamu sedang diproses.';

    }


    /* ================================
       SUDAH DIVERIFIKASI ADMIN
    ================================= */

    elseif (
        $statusPembayaran ===
        'terverifikasi'
    ) {

        $icon = '🎉';

        $judul = 'Pesanan Berhasil!';

        $pesan =
            'Pembayaran sudah terverifikasi. Pesanan kamu sedang diproses.';

    }


    /* ================================
       DITOLAK
    ================================= */

    elseif (
        $statusPembayaran ===
        'ditolak'
    ) {

        $icon = '❌';

        $judul = 'Pembayaran Ditolak';

        $pesan =
            'Bukti pembayaran belum dapat diverifikasi. Silakan upload bukti pembayaran kembali.';

    }

}


/* =====================================================
   NON QRIS
===================================================== */

else {

    $icon = '✅';

    $judul = 'Pesanan Berhasil!';

    $pesan =
        'Pesanan kamu berhasil dibuat. Silakan cek status pesanan secara berkala.';

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
        <?= htmlspecialchars($judul); ?>
        | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >

    <style>

        .success-page {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px 20px;

        }


        .success-card {

            width: 100%;

            max-width: 520px;

            background: #fff;

            border-radius: 20px;

            padding: 35px 28px;

            text-align: center;

            box-shadow:
                0 10px 40px
                rgba(0,0,0,.08);

        }


        .success-icon {

            width: 80px;

            height: 80px;

            margin: 0 auto 18px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #fff4e8;

            border-radius: 50%;

            font-size: 38px;

        }


        .success-card h1 {

            margin: 0 0 10px;

            font-size: 27px;

            color: #222;

        }


        .success-message {

            margin: 0 auto 25px;

            max-width: 420px;

            color: #777;

            font-size: 14px;

            line-height: 1.7;

        }


        .order-code {

            background: #f8f8f8;

            border-radius: 12px;

            padding: 14px;

            margin-bottom: 20px;

        }


        .order-code small {

            display: block;

            color: #999;

            font-size: 11px;

            margin-bottom: 5px;

        }


        .order-code strong {

            font-size: 18px;

            letter-spacing: 1px;

        }


        .payment-box {

            background: #fffaf5;

            border: 1px solid #f2dfcc;

            border-radius: 15px;

            padding: 20px;

            margin-bottom: 20px;

        }


        .payment-box h3 {

            margin: 0 0 15px;

            font-size: 16px;

        }


        .qris-image {

            width: 100%;

            max-width: 280px;

            border-radius: 12px;

            display: block;

            margin: 0 auto 15px;

        }


        .total-payment {

            font-size: 21px;

            font-weight: 800;

            color: #e87518;

            margin: 12px 0;

        }


        .payment-status {

            display: inline-block;

            padding: 8px 13px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 800;

        }


        .status-belum {

            background: #f1f1f1;

            color: #777;

        }


        .status-menunggu {

            background: #fff4d6;

            color: #a86b00;

        }


        .status-terverifikasi {

            background: #e6f7ed;

            color: #20834d;

        }


        .status-ditolak {

            background: #ffe8e8;

            color: #c62828;

        }


        .upload-box {

            margin-top: 18px;

            padding-top: 18px;

            border-top: 1px solid #eadfd5;

            text-align: left;

        }


        .upload-box label {

            display: block;

            margin-bottom: 8px;

            font-size: 12px;

            font-weight: 800;

            color: #555;

        }


        .upload-box input[type="file"] {

            width: 100%;

            box-sizing: border-box;

            padding: 10px;

            border: 1px solid #ddd;

            border-radius: 9px;

            background: #fff;

            font-size: 12px;

        }


        .upload-button {

            width: 100%;

            border: none;

            background: #e87518;

            color: #fff;

            padding: 12px 15px;

            border-radius: 10px;

            margin-top: 10px;

            font-size: 13px;

            font-weight: 800;

            cursor: pointer;

        }


        .upload-button:hover {

            opacity: .9;

        }


        .flash {

            padding: 12px 15px;

            border-radius: 10px;

            margin-bottom: 18px;

            font-size: 12px;

            font-weight: 700;

        }


        .flash-success {

            background: #e6f7ed;

            color: #20834d;

        }


        .flash-error {

            background: #ffe8e8;

            color: #c62828;

        }


        .buttons {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            justify-content: center;

        }


        .buttons a {

            text-decoration: none;

            padding: 12px 17px;

            border-radius: 10px;

            font-size: 12px;

            font-weight: 800;

        }


        .btn-primary {

            background: #e87518;

            color: #fff;

        }


        .btn-secondary {

            background: #f3f3f3;

            color: #555;

        }


        @media (max-width: 500px) {

            .success-card {

                padding: 28px 18px;

            }


            .success-card h1 {

                font-size: 23px;

            }

        }

    </style>

</head>


<body>


<div class="success-page">


    <div class="success-card">


        <!-- ICON -->

        <div class="success-icon">

            <?= $icon; ?>

        </div>


        <!-- TITLE -->

        <h1>

            <?= htmlspecialchars($judul); ?>

        </h1>


        <!-- MESSAGE -->

        <p class="success-message">

            <?= htmlspecialchars($pesan); ?>

        </p>


        <!-- FLASH SUCCESS -->

        <?php if ($uploadSuccess): ?>

            <div class="flash flash-success">

                ✅
                <?= htmlspecialchars($uploadSuccess); ?>

            </div>

        <?php endif; ?>


        <!-- FLASH ERROR -->

        <?php if ($uploadError): ?>

            <div class="flash flash-error">

                ❌
                <?= htmlspecialchars($uploadError); ?>

            </div>

        <?php endif; ?>


        <!-- ORDER CODE -->

        <div class="order-code">

            <small>
                Kode Pesanan
            </small>

            <strong>
                <?= htmlspecialchars($kode); ?>
            </strong>

        </div>


        <?php if ($metode === 'qris'): ?>


            <!-- PAYMENT BOX -->

            <div class="payment-box">


                <h3>
                    Pembayaran QRIS
                </h3>


                <?php if (
                    $statusPembayaran ===
                    'belum_bayar'
                ): ?>

                    <img
                        src="assets/img/qris-niswah.jpeg"
                        alt="QRIS Warung Makan Niswah"
                        class="qris-image"
                    >


                    <div class="total-payment">

                        Rp<?= number_format(
                            $total,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>


                    <div>

                        <span class="payment-status status-belum">

                            💳 Belum Bayar

                        </span>

                    </div>


                    <!-- UPLOAD -->

                    <div class="upload-box">

                        <form
                            action="upload-bukti-pembayaran.php"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <input
                                type="hidden"
                                name="pesanan_id"
                                value="<?= (int) $pesananId; ?>"
                            >


                            <label>
                                Upload Bukti Pembayaran
                            </label>


                            <input
                                type="file"
                                name="bukti_pembayaran"
                                accept="image/jpeg,image/png,image/webp"
                                required
                            >


                            <button
                                type="submit"
                                class="upload-button"
                            >

                                📤 Upload Bukti Pembayaran

                            </button>

                        </form>

                    </div>


                <?php elseif (
                    $statusPembayaran ===
                    'menunggu_verifikasi'
                ): ?>


                    <div class="total-payment">

                        Rp<?= number_format(
                            $total,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>


                    <span class="payment-status status-menunggu">

                        ⏳ Bukti Pembayaran Dikirim

                    </span>


                <?php elseif (
                    $statusPembayaran ===
                    'terverifikasi'
                ): ?>


                    <div class="total-payment">

                        Rp<?= number_format(
                            $total,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>


                    <span class="payment-status status-terverifikasi">

                        ✅ Pembayaran Terverifikasi

                    </span>


                <?php elseif (
                    $statusPembayaran ===
                    'ditolak'
                ): ?>


                    <div class="total-payment">

                        Rp<?= number_format(
                            $total,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </div>


                    <span class="payment-status status-ditolak">

                        ❌ Pembayaran Ditolak

                    </span>


                    <!-- UPLOAD ULANG -->

                    <div class="upload-box">

                        <form
                            action="upload-bukti-pembayaran.php"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <input
                                type="hidden"
                                name="pesanan_id"
                                value="<?= (int) $pesananId; ?>"
                            >


                            <label>
                                Upload Bukti Pembayaran Baru
                            </label>


                            <input
                                type="file"
                                name="bukti_pembayaran"
                                accept="image/jpeg,image/png,image/webp"
                                required
                            >


                            <button
                                type="submit"
                                class="upload-button"
                            >

                                📤 Upload Ulang

                            </button>

                        </form>

                    </div>


                <?php endif; ?>


            </div>


        <?php endif; ?>


        <!-- BUTTONS -->

        <div class="buttons">


            <a
                href="status-pesanan.php?id=<?= (int) $pesananId; ?>"
                class="btn-primary"
            >

                📦 Lihat Status Pesanan

            </a>


            <a
                href="riwayat-pesanan.php"
                class="btn-secondary"
            >

                📋 Riwayat Pesanan

            </a>


            <a
                href="index.php"
                class="btn-secondary"
            >

                🏠 Kembali ke Beranda

            </a>


        </div>


    </div>


</div>


</body>

</html>