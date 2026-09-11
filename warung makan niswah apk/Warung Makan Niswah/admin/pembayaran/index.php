<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

/* =====================================================
   PROSES VERIFIKASI PEMBAYARAN
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $aksi = $_POST['aksi'] ?? '';
    $pesananId = (int) ($_POST['pesanan_id'] ?? 0);

    if ($pesananId <= 0) {
        $_SESSION['flash_error'] = 'ID pesanan tidak valid.';
        header("Location: index.php");
        exit;
    }

    /* =========================
       TERIMA PEMBAYARAN
    ========================= */
    if ($aksi === 'terima') {

        $stmt = $conn->prepare("
            UPDATE pesanan
            SET
                status_pembayaran = 'terverifikasi',
                tanggal_pembayaran = NOW()
            WHERE id = ?
            AND metode_pembayaran = 'qris'
            AND status_pembayaran = 'menunggu_verifikasi'
        ");

        $stmt->bind_param("i", $pesananId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['flash_success'] = 'Pembayaran berhasil diterima dan diverifikasi.';
        } else {
            $_SESSION['flash_error'] = 'Pembayaran tidak dapat diverifikasi. Mungkin sudah diproses.';
        }

        $stmt->close();

        header("Location: index.php");
        exit;
    }


    /* =========================
       TOLAK PEMBAYARAN
    ========================= */
    if ($aksi === 'tolak') {

        $stmt = $conn->prepare("
            UPDATE pesanan
            SET
                status_pembayaran = 'ditolak'
            WHERE id = ?
            AND metode_pembayaran = 'qris'
            AND status_pembayaran = 'menunggu_verifikasi'
        ");

        $stmt->bind_param("i", $pesananId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['flash_success'] = 'Pembayaran ditolak. Customer dapat mengupload bukti kembali.';
        } else {
            $_SESSION['flash_error'] = 'Pembayaran tidak dapat ditolak. Mungkin sudah diproses.';
        }

        $stmt->close();

        header("Location: index.php");
        exit;
    }
}


/* =====================================================
   DATA PEMBAYARAN
===================================================== */

$query = "
    SELECT
        p.id,
        p.kode_pesanan,
        p.total_harga,
        p.metode_pembayaran,
        p.status_pembayaran,
        p.bukti_pembayaran,
        p.tanggal_pembayaran,
        p.status_pesanan,
        p.created_at,
        pl.nama,
        pl.no_hp
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    ORDER BY p.created_at DESC
";

$result = $conn->query($query);


/* =====================================================
   LABEL STATUS PEMBAYARAN
===================================================== */

function labelPembayaran($status)
{
    $label = [
        'belum_bayar'        => 'Belum Bayar',
        'menunggu_verifikasi'=> 'Menunggu Verifikasi',
        'terverifikasi'      => 'Terverifikasi',
        'ditolak'            => 'Ditolak'
    ];

    return $label[$status] ?? ucfirst(
        str_replace('_', ' ', $status)
    );
}


/* =====================================================
   CLASS STATUS
===================================================== */

function classPembayaran($status)
{
    $class = [
        'belum_bayar'         => 'belum-bayar',
        'menunggu_verifikasi' => 'menunggu-verifikasi',
        'terverifikasi'       => 'terverifikasi',
        'ditolak'             => 'ditolak'
    ];

    return $class[$status] ?? 'belum-bayar';
}


/* =====================================================
   FLASH MESSAGE
===================================================== */

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';

unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);

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
        Pembayaran | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        /* =================================================
           PAYMENT
        ================================================= */

        .payment-method {

            display: inline-block;

            padding: 7px 11px;

            background: #fff0df;

            color: #e87518;

            border-radius: 8px;

            font-size: 11px;

            font-weight: 800;

            text-transform: uppercase;

            white-space: nowrap;

        }


        .payment-total {

            font-weight: 800;

            white-space: nowrap;

        }


        .payment-customer {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .payment-customer strong {

            font-size: 13px;

        }


        .payment-customer small {

            color: #999;

            font-size: 11px;

        }


        .payment-date {

            white-space: nowrap;

            font-size: 12px;

            color: #777;

        }


        /* =================================================
           PAYMENT STATUS
        ================================================= */

        .payment-status {

            display: inline-block;

            padding: 6px 11px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 800;

            white-space: nowrap;

        }


        .payment-status.belum-bayar {

            background: #f3f3f3;

            color: #777;

        }


        .payment-status.menunggu-verifikasi {

            background: #fff4d6;

            color: #a86b00;

        }


        .payment-status.terverifikasi {

            background: #e6f7ed;

            color: #20834d;

        }


        .payment-status.ditolak {

            background: #ffe8e8;

            color: #c62828;

        }


        /* =================================================
           ACTION
        ================================================= */

        .payment-actions {

            display: flex;

            align-items: center;

            gap: 6px;

            flex-wrap: wrap;

        }


        .payment-action {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 8px 11px;

            background: #fff;

            border: 1px solid #e5e5e5;

            color: #555;

            border-radius: 8px;

            font-size: 11px;

            font-weight: 800;

            text-decoration: none;

            cursor: pointer;

            transition: .2s;

            white-space: nowrap;

        }


        .payment-action:hover {

            background: #fff0df;

            border-color: #e87518;

            color: #e87518;

        }


        .payment-action.accept {

            background: #e6f7ed;

            border-color: #bde5ce;

            color: #20834d;

        }


        .payment-action.accept:hover {

            background: #20834d;

            border-color: #20834d;

            color: #fff;

        }


        .payment-action.reject {

            background: #ffe8e8;

            border-color: #f3c4c4;

            color: #c62828;

        }


        .payment-action.reject:hover {

            background: #c62828;

            border-color: #c62828;

            color: #fff;

        }


        .payment-action.disabled {

            opacity: .5;

            cursor: not-allowed;

        }


        /* =================================================
           FLASH
        ================================================= */

        .flash {

            padding: 13px 16px;

            border-radius: 10px;

            margin-bottom: 18px;

            font-size: 13px;

            font-weight: 700;

        }


        .flash-success {

            background: #e6f7ed;

            color: #20834d;

            border: 1px solid #c7e8d5;

        }


        .flash-error {

            background: #ffe8e8;

            color: #c62828;

            border: 1px solid #f2c7c7;

        }


        /* =================================================
           PROOF
        ================================================= */

        .proof-link {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 10px;

            background: #f2edff;

            color: #6845b5;

            border-radius: 8px;

            text-decoration: none;

            font-size: 11px;

            font-weight: 800;

        }


        .proof-link:hover {

            background: #6845b5;

            color: #fff;

        }


        .no-proof {

            color: #aaa;

            font-size: 11px;

        }


        /* =================================================
           PAYMENT TIME
        ================================================= */

        .payment-time {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .payment-time small {

            color: #aaa;

            font-size: 10px;

        }


        /* =================================================
           EMPTY
        ================================================= */

        .payment-empty {

            text-align: center;

            padding: 60px 20px;

            color: #888;

        }


        .payment-empty-icon {

            font-size: 50px;

            margin-bottom: 10px;

        }


        .payment-empty strong {

            display: block;

            color: #555;

            font-size: 15px;

            margin-bottom: 5px;

        }


        .payment-empty p {

            margin: 0;

            font-size: 12px;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 800px) {

            .table-wrapper {

                overflow-x: auto;

            }


            table {

                min-width: 1250px;

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


            <a
                href="index.php"
                class="active"
            >

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
         MAIN CONTENT
    ================================================== -->

    <main class="main-content">


        <header class="page-header">

            <div>

                <h1>
                    Pembayaran
                </h1>

                <p>
                    Kelola dan verifikasi pembayaran pelanggan.
                </p>

            </div>

        </header>


        <!-- =================================================
             FLASH SUCCESS
        ================================================== -->

        <?php if ($flashSuccess): ?>

            <div class="flash flash-success">

                ✅
                <?= htmlspecialchars($flashSuccess); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             FLASH ERROR
        ================================================== -->

        <?php if ($flashError): ?>

            <div class="flash flash-error">

                ❌
                <?= htmlspecialchars($flashError); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="table-card">


            <div class="table-header">

                <div>

                    <h3>
                        Semua Pembayaran
                    </h3>

                    <p>
                        Pembayaran QRIS dapat diverifikasi setelah bukti diterima.
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
                                Kode Pesanan
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Metode
                            </th>

                            <th>
                                Status Pembayaran
                            </th>

                            <th>
                                Bukti
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

                        while (
                            $row =
                            $result->fetch_assoc()
                        ):

                            $statusPembayaran =
                                $row['status_pembayaran']
                                ?: 'belum_bayar';

                    ?>


                        <tr>


                            <!-- NO -->

                            <td>

                                <?= $no++; ?>

                            </td>


                            <!-- KODE -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['kode_pesanan']
                                    ); ?>

                                </strong>

                            </td>


                            <!-- PELANGGAN -->

                            <td>

                                <div class="payment-customer">

                                    <strong>

                                        <?= htmlspecialchars(
                                            $row['nama']
                                        ); ?>

                                    </strong>

                                    <small>

                                        <?= htmlspecialchars(
                                            $row['no_hp']
                                        ); ?>

                                    </small>

                                </div>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <span class="payment-total">

                                    Rp<?= number_format(
                                        $row['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </span>

                            </td>


                            <!-- METODE -->

                            <td>

                                <span class="payment-method">

                                    <?= htmlspecialchars(
                                        strtoupper(
                                            $row['metode_pembayaran']
                                        )
                                    ); ?>

                                </span>

                            </td>


                            <!-- STATUS PEMBAYARAN -->

                            <td>

                                <span
                                    class="payment-status <?= htmlspecialchars(
                                        classPembayaran(
                                            $statusPembayaran
                                        )
                                    ); ?>"
                                >

                                    <?= htmlspecialchars(
                                        labelPembayaran(
                                            $statusPembayaran
                                        )
                                    ); ?>

                                </span>

                            </td>


                            <!-- BUKTI -->

                            <td>

                                <?php if (
                                    $row['bukti_pembayaran']
                                ): ?>

                                    <a
                                        href="../../<?= htmlspecialchars(
                                            $row['bukti_pembayaran']
                                        ); ?>"
                                        target="_blank"
                                        class="proof-link"
                                    >

                                        🧾 Lihat Bukti

                                    </a>

                                <?php else: ?>

                                    <span class="no-proof">
                                        Belum ada
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <div class="payment-time">

                                    <span class="payment-date">

                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $row['created_at']
                                            )
                                        ); ?>

                                    </span>


                                    <?php if (
                                        $row['tanggal_pembayaran']
                                    ): ?>

                                        <small>

                                            Pembayaran:
                                            <?= date(
                                                'd-m-Y H:i',
                                                strtotime(
                                                    $row['tanggal_pembayaran']
                                                )
                                            ); ?>

                                        </small>

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- AKSI -->

                            <td>

                                <div class="payment-actions">


                                    <!-- DETAIL -->

                                    <a
                                        href="../pesanan/detail.php?id=<?= (int) $row['id']; ?>"
                                        class="payment-action"
                                    >

                                        👁️ Detail

                                    </a>


                                    <?php if (
                                        strtolower(
                                            $row['metode_pembayaran']
                                        ) === 'qris'
                                        &&
                                        $statusPembayaran ===
                                        'menunggu_verifikasi'
                                    ): ?>


                                        <!-- TERIMA -->

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Yakin ingin menerima dan memverifikasi pembayaran ini?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="aksi"
                                                value="terima"
                                            >

                                            <input
                                                type="hidden"
                                                name="pesanan_id"
                                                value="<?= (int) $row['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="payment-action accept"
                                            >

                                                ✅ Terima

                                            </button>

                                        </form>


                                        <!-- TOLAK -->

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Yakin ingin menolak pembayaran ini? Customer dapat mengupload bukti kembali.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="aksi"
                                                value="tolak"
                                            >

                                            <input
                                                type="hidden"
                                                name="pesanan_id"
                                                value="<?= (int) $row['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="payment-action reject"
                                            >

                                                ❌ Tolak

                                            </button>

                                        </form>


                                    <?php elseif (
                                        $statusPembayaran ===
                                        'terverifikasi'
                                    ): ?>


                                        <span
                                            class="payment-action disabled"
                                        >

                                            ✅ Sudah Diverifikasi

                                        </span>


                                    <?php elseif (
                                        $statusPembayaran ===
                                        'ditolak'
                                    ): ?>


                                        <span
                                            class="payment-action disabled"
                                        >

                                            ❌ Ditolak

                                        </span>


                                    <?php endif; ?>


                                </div>

                            </td>


                        </tr>


                    <?php

                        endwhile;

                    else:

                    ?>


                        <tr>

                            <td
                                colspan="9"
                                style="padding:0;"
                            >

                                <div class="payment-empty">

                                    <div class="payment-empty-icon">
                                        💰
                                    </div>


                                    <strong>
                                        Belum ada data pembayaran
                                    </strong>


                                    <p>
                                        Data pembayaran akan muncul
                                        setelah pelanggan melakukan pemesanan.
                                    </p>

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>


            </div>


        </div>


    </main>


</div>


</body>

</html>