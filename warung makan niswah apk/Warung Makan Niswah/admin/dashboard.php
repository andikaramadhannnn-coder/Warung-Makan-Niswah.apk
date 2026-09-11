<?php

require_once "../includes/config.php";
require_once "../includes/functions.php";

cekLogin();


// =========================
// STATISTIK DASHBOARD
// =========================

$totalMenu = 0;
$totalPesanan = 0;
$totalPelanggan = 0;
$totalPendapatan = 0;


// Total Menu
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM menu
");

if ($result) {

    $totalMenu =
        $result->fetch_assoc()['total'];

}


// Total Pesanan
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM pesanan
");

if ($result) {

    $totalPesanan =
        $result->fetch_assoc()['total'];

}


// Total Pelanggan
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM pelanggan
");

if ($result) {

    $totalPelanggan =
        $result->fetch_assoc()['total'];

}


// Total Pendapatan
$result = $conn->query("
    SELECT COALESCE(
        SUM(total_harga),
        0
    ) AS total
    FROM pesanan
    WHERE status_pesanan != 'dibatalkan'
");

if ($result) {

    $totalPendapatan =
        $result->fetch_assoc()['total'];

}


// =========================
// MENU TERLARIS
// =========================

$menuTerlaris = $conn->query("
    SELECT
        dp.nama_menu,
        SUM(dp.jumlah) AS jumlah_terjual
    FROM detail_pesanan dp
    INNER JOIN pesanan p
        ON dp.pesanan_id = p.id
    WHERE p.status_pesanan != 'dibatalkan'
    GROUP BY dp.menu_id, dp.nama_menu
    ORDER BY jumlah_terjual DESC
    LIMIT 5
");

// =========================
// PESANAN TERBARU
// =========================

$pesananTerbaru = $conn->query("
    SELECT
        p.id,
        p.kode_pesanan,
        p.total_harga,
        p.status_pesanan,
        p.created_at,
        pl.nama
    FROM pesanan p
    INNER JOIN pelanggan pl
        ON p.pelanggan_id = pl.id
    ORDER BY p.created_at DESC
    LIMIT 5
");

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
        Dashboard | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>


<body>


<div class="admin-wrapper">


    <!-- =========================
         SIDEBAR
    ========================== -->

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


            <!-- DASHBOARD -->

            <a
                href="dashboard.php"
                class="active"
            >

                <span>
                    📊
                </span>

                <span>
                    Dashboard
                </span>

            </a>



            <!-- MENU -->

            <a href="menu/index.php">

                <span>
                    🍛
                </span>

                <span>
                    Menu Makanan
                </span>

            </a>



            <!-- KATEGORI -->

            <a href="kategori/index.php">

                <span>
                    📂
                </span>

                <span>
                    Kategori
                </span>

            </a>



            <!-- PESANAN -->

            <a href="pesanan/index.php">

                <span>
                    🛒
                </span>

                <span>
                    Pesanan
                </span>

            </a>



            <!-- PELANGGAN -->

            <a href="pelanggan/index.php">

                <span>
                    👥
                </span>

                <span>
                    Pelanggan
                </span>

            </a>



            <!-- PEMBAYARAN -->

            <a href="pembayaran/index.php">

                <span>
                    💰
                </span>

                <span>
                    Pembayaran
                </span>

            </a>



            <!-- LAPORAN -->

            <a href="laporan/index.php">

                <span>
                    📈
                </span>

                <span>
                    Laporan
                </span>

            </a>


        </nav>



        <!-- LOGOUT -->

        <div class="sidebar-bottom">

            <a
                href="logout.php"
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



    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="main-content">


        <!-- HEADER -->

        <header class="topbar">


            <div>

                <h1>
                    Dashboard
                </h1>

                <p>

                    Selamat datang kembali,

                    <strong>

                        <?= htmlspecialchars(
                            $_SESSION[
                                'admin_nama'
                            ]
                        ); ?>

                    </strong>

                    👋

                </p>

            </div>

            <div class="notification-wrapper">

    <button
        type="button"
        class="notification-button"
        id="notificationButton"
    >

        🔔

        <span
            id="notificationBadge"
            class="notification-badge"
            style="display:none;"
        >
            0
        </span>

    </button>


    <div
        id="notificationPanel"
        class="notification-panel"
    >

        <div class="notification-header">

            <strong>
                Pesanan Baru
            </strong>

            <span id="notificationCount">
                0 pesanan
            </span>

        </div>


        <div
            id="notificationList"
            class="notification-list"
        >

            <div class="notification-empty">

                🔔

                <p>
                    Belum ada pesanan baru
                </p>

            </div>

        </div>

    </div>

</div>



            <div class="admin-profile">


                <div class="profile-avatar">

                    A

                </div>


                <div>

                    <strong>

                        <?= htmlspecialchars(
                            $_SESSION[
                                'admin_nama'
                            ]
                        ); ?>

                    </strong>


                    <small>
                        Administrator
                    </small>

                </div>


            </div>


        </header>



        <!-- =========================
             STATISTIK
        ========================== -->

        <section class="stats-grid">


            <!-- TOTAL MENU -->

            <div class="stat-card">

                <div class="stat-icon">
                    🍛
                </div>

                <div>

                    <span>
                        Total Menu
                    </span>

                    <h2>

                        <?= number_format(
                            $totalMenu
                        ); ?>

                    </h2>

                </div>

            </div>



            <!-- TOTAL PESANAN -->

            <div class="stat-card">

                <div class="stat-icon">
                    🛒
                </div>

                <div>

                    <span>
                        Total Pesanan
                    </span>

                    <h2>

                        <?= number_format(
                            $totalPesanan
                        ); ?>

                    </h2>

                </div>

            </div>



            <!-- PELANGGAN -->

            <div class="stat-card">

                <div class="stat-icon">
                    👥
                </div>

                <div>

                    <span>
                        Pelanggan
                    </span>

                    <h2>

                        <?= number_format(
                            $totalPelanggan
                        ); ?>

                    </h2>

                </div>

            </div>



            <!-- PENDAPATAN -->

            <div class="stat-card">

                <div class="stat-icon">
                    💰
                </div>

                <div>

                    <span>
                        Pendapatan
                    </span>

                    <h2>

                        Rp<?= number_format(
                            $totalPendapatan,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </h2>

                </div>

            </div>


        </section>



        <!-- =========================
             DASHBOARD CONTENT
        ========================== -->

        <section class="dashboard-grid">


            <!-- =====================
                 PESANAN TERBARU
            ====================== -->

            <div class="dashboard-card">


                <div class="card-header">


                    <div>

                        <h3>
                            Pesanan Terbaru
                        </h3>

                        <p>
                            Pesanan online terbaru
                        </p>

                    </div>


                    <a href="pesanan/index.php">

                        Lihat Semua

                    </a>


                </div>



                <?php if (
                    $pesananTerbaru &&
                    $pesananTerbaru->num_rows > 0
                ): ?>


                    <div class="recent-orders">


                        <?php while (
                            $order =
                            $pesananTerbaru->fetch_assoc()
                        ): ?>


                            <div class="order-item">


                                <div class="order-info">


                                    <strong>

                                        <?= htmlspecialchars(
                                            $order[
                                                'kode_pesanan'
                                            ]
                                        ); ?>

                                    </strong>


                                    <span>

                                        <?= htmlspecialchars(
                                            $order[
                                                'nama'
                                            ]
                                        ); ?>

                                    </span>


                                </div>



                                <div class="order-right">


                                    <strong>

                                        Rp<?= number_format(
                                            $order[
                                                'total_harga'
                                            ],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </strong>


                                    <span
                                        class="status
                                        <?= htmlspecialchars(
                                            $order[
                                                'status_pesanan'
                                            ]
                                        ); ?>"
                                    >

                                        <?= ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $order[
                                                    'status_pesanan'
                                                ]
                                            )
                                        ); ?>

                                    </span>


                                </div>


                            </div>


                        <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div class="empty-state">


                        <div class="empty-icon">
                            🛒
                        </div>


                        <h4>
                            Belum ada pesanan
                        </h4>


                        <p>

                            Pesanan pelanggan
                            akan muncul di sini.

                        </p>


                    </div>


                <?php endif; ?>


            </div>



            <!-- =====================
                 MENU TERLARIS
            ====================== -->

            <div class="dashboard-card">


                <div class="card-header">


                    <div>

                        <h3>
                            Menu Terlaris
                        </h3>

                        <p>
                            Menu dengan penjualan
                            tertinggi
                        </p>

                    </div>


                    <a href="menu/index.php">

                        Lihat Menu

                    </a>


                </div>



                <?php if ($menuTerlaris && $menuTerlaris->num_rows > 0): ?>

                    <div class="recent-orders">

                        <?php $peringkat = 1; ?>

                        <?php while ($menu = $menuTerlaris->fetch_assoc()): ?>

                            <div class="order-item">

                                <div class="order-info">
                                    <strong>
                                        <?= $peringkat == 1 ? '🏆' : '🍛'; ?>
                                        <?= $peringkat; ?>.
                                        <?= htmlspecialchars($menu['nama_menu']); ?>
                                    </strong>
                                </div>

                                <div class="order-right">
                                    <strong>
                                        <?= number_format($menu['jumlah_terjual']); ?> terjual
                                    </strong>
                                </div>

                            </div>

                            <?php $peringkat++; ?>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="empty-state">
                        <div class="empty-icon">🍛</div>
                        <h4>Belum ada data</h4>
                        <p>Data menu terlaris akan muncul setelah ada transaksi.</p>
                    </div>

                <?php endif; ?>


            </div>


        </section>


    </main>


</div>

<script>

let pesananSebelumnya = 0;

let pertamaKali = true;


function cekPesananBaru() {

    fetch('notifikasi.php')

        .then(response => response.json())

        .then(data => {

            if (!data.success) {
                return;
            }


            const jumlah =
                data.jumlah;


            const badge =
                document.getElementById(
                    'notificationBadge'
                );


            const count =
                document.getElementById(
                    'notificationCount'
                );


            const list =
                document.getElementById(
                    'notificationList'
                );


            // =========================================
            // BADGE
            // =========================================

            if (jumlah > 0) {

                badge.style.display =
                    'flex';

                badge.textContent =
                    jumlah;

            } else {

                badge.style.display =
                    'none';

            }


            count.textContent =
                jumlah +
                ' pesanan';


            // =========================================
            // LIST
            // =========================================

            if (jumlah === 0) {

                list.innerHTML = `

                    <div
                        class="notification-empty"
                    >

                        🔔

                        <p>
                            Belum ada pesanan baru
                        </p>

                    </div>

                `;

            } else {

                list.innerHTML =
                    data.pesanan.map(
                        pesanan => `

                        <a
                            href="pesanan/detail.php?id=${pesanan.id}"
                            class="notification-item"
                        >

                            <strong>
                                🛒 ${pesanan.kode}
                            </strong>

                            <span>
                                👤 ${pesanan.nama}
                            </span>

                            <span
                                class="notification-total"
                            >
                                Rp${pesanan.total}
                            </span>

                            <span>
                                🕐 ${pesanan.tanggal}
                            </span>

                        </a>

                    `
                    ).join('');

            }


            // =========================================
            // PESANAN BARU
            // =========================================

            if (
                !pertamaKali &&
                jumlah > pesananSebelumnya
            ) {

                tampilkanNotifikasi(
                    jumlah
                );

            }


            pesananSebelumnya =
                jumlah;

            pertamaKali = false;

        })

        .catch(error => {

            console.log(
                'Notifikasi:',
                error
            );

        });

}


// =====================================================
// POPUP NOTIFIKASI BROWSER
// =====================================================

function tampilkanNotifikasi(jumlah) {

    if (
        'Notification' in window
    ) {

        if (
            Notification.permission ===
            'granted'
        ) {

            new Notification(
                '🔔 Pesanan Baru!',
                {

                    body:
                        'Ada ' +
                        jumlah +
                        ' pesanan yang menunggu.'

                }
            );

        }

    }

}


// =====================================================
// KLIK BELL
// =====================================================

document
    .getElementById(
        'notificationButton'
    )
    .addEventListener(
        'click',
        function () {

            document
                .getElementById(
                    'notificationPanel'
                )
                .classList.toggle(
                    'show'
                );

        }
    );


// =====================================================
// REQUEST IZIN NOTIFIKASI
// =====================================================

if (
    'Notification' in window &&
    Notification.permission === 'default'
) {

    Notification.requestPermission();

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