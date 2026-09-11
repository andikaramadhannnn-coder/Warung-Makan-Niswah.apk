<?php

session_start();

require_once "includes/config.php";

// =====================================================
// VARIABEL
// =====================================================

$hasil = [];
$error = '';

$noHp = trim($_POST['no_hp'] ?? '');


// =====================================================
// CARI RIWAYAT PESANAN
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($noHp === '') {

        $error = 'Nomor HP wajib diisi.';

    } else {

        $stmt = $conn->prepare("
            SELECT
                p.id,
                p.kode_pesanan,
                p.total_harga,
                p.metode_pembayaran,
                p.status_pesanan,
                p.created_at,
                pl.nama,
                pl.no_hp
            FROM pesanan p
            INNER JOIN pelanggan pl
                ON p.pelanggan_id = pl.id
            WHERE pl.no_hp = ?
            ORDER BY p.created_at DESC
        ");

        $stmt->bind_param("s", $noHp);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($pesanan = $result->fetch_assoc()) {

            $pesanan['detail'] = [];

            $stmtDetail = $conn->prepare("
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

            $stmtDetail->bind_param("i", $pesanan['id']);
            $stmtDetail->execute();

            $resultDetail = $stmtDetail->get_result();

            while ($detail = $resultDetail->fetch_assoc()) {

                $detail['pilihan'] = [];

                $stmtPilihan = $conn->prepare("
                    SELECT
                        tipe_pilihan,
                        nama_pilihan,
                        harga_tambahan
                    FROM detail_pilihan_pesanan
                    WHERE detail_pesanan_id = ?
                    ORDER BY id ASC
                ");

                $stmtPilihan->bind_param("i", $detail['id']);
                $stmtPilihan->execute();

                $resultPilihan = $stmtPilihan->get_result();

                while ($pilihan = $resultPilihan->fetch_assoc()) {
                    $detail['pilihan'][] = $pilihan;
                }

                $stmtPilihan->close();

                $pesanan['detail'][] = $detail;
            }

            $stmtDetail->close();

            $hasil[] = $pesanan;
        }

        $stmt->close();

        if (count($hasil) === 0) {
            $error = 'Belum ditemukan pesanan dengan nomor HP tersebut.';
        }
    }
}


// =====================================================
// JUMLAH CART
// =====================================================

$cart = $_SESSION['cart'] ?? [];

$jumlahCart = 0;

foreach ($cart as $item) {
    $jumlahCart += intval($item['jumlah'] ?? 0);
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

    <title>Riwayat Pesanan | Niswah</title>

    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >

    <style>

        .history-page {
            min-height: 80vh;
            padding: 25px 20px 70px;
        }

        .history-card {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 20px;
            padding: 30px;
            box-sizing: border-box;
        }

        .history-card h1 {
            margin: 0 0 8px;
        }

        .history-card > p {
            color: #777;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #e87518;
        }

        .history-button {
            width: 100%;
            border: none;
            padding: 14px;
            background: #e87518;
            color: white;
            border-radius: 11px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }

        .error-box {
            margin-bottom: 20px;
            padding: 13px;
            background: #ffe8e8;
            color: #c62828;
            border-radius: 10px;
            font-size: 13px;
        }

        .history-list {
            margin-top: 30px;
        }

        .history-title {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .history-item {
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 15px;
            background: #fff;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .history-code {
            font-weight: 800;
            color: #e87518;
            font-size: 15px;
        }

        .history-date {
            color: #888;
            font-size: 11px;
            margin-top: 4px;
        }

        /* ACTION PESANAN */
        .history-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .copy-button,
        .status-button {
            border: 1px solid #e87518;
            background: white;
            color: #e87518;
            padding: 8px 11px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .copy-button:hover,
        .status-button:hover {
            background: #fff8ef;
        }

        .status-form {
            margin: 0;
        }

        .history-menu {
            padding: 13px 0;
        }

        .history-menu-item {
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }

        .history-menu-item:last-child {
            border-bottom: none;
        }

        .menu-top {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            font-size: 13px;
            font-weight: 800;
        }

        .menu-meta {
            margin-top: 5px;
            color: #888;
            font-size: 11px;
        }

        .menu-option {
            margin-top: 4px;
            color: #666;
            font-size: 11px;
        }

        .menu-option strong {
            color: #333;
        }

        .history-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding-top: 13px;
            border-top: 1px solid #eee;
        }

        .history-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #fff0df;
            color: #e87518;
            font-size: 11px;
            font-weight: 700;
        }

        .history-total {
            text-align: right;
            font-size: 12px;
            color: #777;
        }

        .history-total strong {
            display: block;
            color: #e87518;
            font-size: 17px;
        }

        .empty-history {
            text-align: center;
            padding: 25px 10px;
            color: #888;
            font-size: 13px;
        }

        .bottom-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 25px;
        }

        .bottom-action {
            display: block;
            width: 100%;
            padding: 13px;
            box-sizing: border-box;
            border-radius: 11px;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .home-action {
            background: #e87518;
            color: white;
        }

        .status-action {
            background: white;
            color: #e87518;
            border: 1px solid #e87518;
        }

        @media (max-width: 550px) {

            .history-page {
                padding: 20px 15px 60px;
            }

            .history-card {
                padding: 22px 18px;
            }

            .history-header {
                flex-direction: column;
            }

            .history-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .copy-button,
            .status-button {
                flex: 1;
                text-align: center;
            }

            .history-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .history-total {
                text-align: left;
            }
        }

    </style>

</head>


<body>

<!-- =====================================================
     NAVBAR
====================================================== -->

<header class="customer-navbar">

    <div class="customer-container navbar-inner">

        <a
            href="index.php"
            class="customer-logo"
        >
            🍛
            <span>Niswah</span>
        </a>

        <a
            href="keranjang.php"
            class="cart-button"
        >
            🛒
            <span>Keranjang</span>

            <?php if ($jumlahCart > 0): ?>
                <b><?= $jumlahCart; ?></b>
            <?php endif; ?>

        </a>

    </div>

</header>


<!-- =====================================================
     MAIN
====================================================== -->

<main class="history-page">

    <div class="history-card">

        <h1>📜 Riwayat Pesanan</h1>

        <p>
            Masukkan nomor HP yang digunakan
            saat memesan untuk melihat pesanan
            sebelumnya dan menyalin kembali
            kode pesanan.
        </p>


        <?php if ($error !== ''): ?>

            <div class="error-box">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">

            <div class="form-group">

                <label for="no_hp">
                    Nomor HP
                </label>

                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    value="<?= htmlspecialchars($noHp); ?>"
                    placeholder="Masukkan nomor HP"
                    required
                >

            </div>

            <button
                type="submit"
                class="history-button"
            >
                🔎 Lihat Riwayat Pesanan
            </button>

        </form>


        <!-- HASIL -->

        <?php if (count($hasil) > 0): ?>

            <div class="history-list">

                <div class="history-title">
                    <?= count($hasil); ?> Pesanan Ditemukan
                </div>


                <?php foreach ($hasil as $pesanan): ?>

                    <div class="history-item">

                        <div class="history-header">

                            <div>

                                <div class="history-code">
                                    <?= htmlspecialchars(
                                        $pesanan['kode_pesanan']
                                    ); ?>
                                </div>

                                <div class="history-date">
                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime($pesanan['created_at'])
                                    ); ?>
                                </div>

                            </div>


                            <!-- TOMBOL AKSI -->

                            <div class="history-actions">

                                <!-- LIHAT STATUS -->

                                <form
                                    method="POST"
                                    action="status-pesanan.php"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="kode_pesanan"
                                        value="<?= htmlspecialchars(
                                            $pesanan['kode_pesanan'],
                                            ENT_QUOTES
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="no_hp"
                                        value="<?= htmlspecialchars(
                                            $noHp,
                                            ENT_QUOTES
                                        ); ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="status-button"
                                    >
                                        🔎 Lihat Status
                                    </button>

                                </form>


                                <!-- SALIN KODE -->

                                <button
                                    type="button"
                                    class="copy-button"
                                    data-code="<?= htmlspecialchars(
                                        $pesanan['kode_pesanan'],
                                        ENT_QUOTES
                                    ); ?>"
                                >
                                    📋 Salin Kode
                                </button>

                            </div>

                        </div>


                        <!-- MENU -->

                        <div class="history-menu">

                            <?php if (
                                count($pesanan['detail']) > 0
                            ): ?>

                                <?php foreach (
                                    $pesanan['detail']
                                    as $detail
                                ): ?>

                                    <div class="history-menu-item">

                                        <div class="menu-top">

                                            <span>
                                                <?= htmlspecialchars(
                                                    $detail['nama_menu']
                                                ); ?>
                                            </span>

                                            <span>
                                                ×<?= intval(
                                                    $detail['jumlah']
                                                ); ?>
                                            </span>

                                        </div>


                                        <div class="menu-meta">

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

                                                <div class="menu-option">

                                                    <?php

                                                    $tipe =
                                                        $pilihan[
                                                            'tipe_pilihan'
                                                        ];

                                                    $label =
                                                        $tipe === 'varian'
                                                            ? 'Penyajian'
                                                            : ucfirst(
                                                                str_replace(
                                                                    '_',
                                                                    ' ',
                                                                    $tipe
                                                                )
                                                            );

                                                    ?>

                                                    <?= htmlspecialchars(
                                                        $label
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

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="empty-history">
                                    Detail pesanan tidak tersedia.
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- FOOTER -->

                        <div class="history-footer">

                            <span class="history-status">

                                <?= htmlspecialchars(
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $pesanan['status_pesanan']
                                        )
                                    )
                                ); ?>

                            </span>


                            <div class="history-total">

                                Total

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

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- ACTION -->

        <div class="bottom-actions">

            <a
                href="status-pesanan.php"
                class="bottom-action status-action"
            >
                🔎 Cek Status Pesanan
            </a>

            <a
                href="index.php"
                class="bottom-action home-action"
            >
                🍛 Kembali ke Beranda
            </a>

        </div>

    </div>

</main>


<script>

// =====================================================
// SALIN KODE PESANAN
// =====================================================

document
    .querySelectorAll('.copy-button')
    .forEach(function(button) {

        button.addEventListener(
            'click',
            async function() {

                const code = this.dataset.code;

                try {

                    await navigator.clipboard.writeText(code);

                    const originalText = this.textContent;

                    this.textContent = '✅ Tersalin';

                    setTimeout(
                        () => {
                            this.textContent = originalText;
                        },
                        1500
                    );

                } catch (error) {

                    alert(
                        'Kode pesanan: ' + code
                    );

                }

            }
        );

    });

</script>


</body>

</html>