<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =========================
// DATA PELANGGAN
// =========================

$query = "
    SELECT
        pl.id,
        pl.nama,
        pl.no_hp,
        pl.alamat,
        pl.created_at,
        COUNT(p.id) AS jumlah_pesanan,
        COALESCE(
            SUM(
                CASE
                    WHEN p.status_pesanan != 'dibatalkan'
                    THEN p.total_harga
                    ELSE 0
                END
            ),
            0
        ) AS total_belanja
    FROM pelanggan pl
    LEFT JOIN pesanan p
        ON pl.id = p.pelanggan_id
    GROUP BY
        pl.id,
        pl.nama,
        pl.no_hp,
        pl.alamat,
        pl.created_at
    ORDER BY pl.created_at DESC
";

$result = $conn->query($query);

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
        Pelanggan | Warung Makan Niswah
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
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

                <h2>Niswah</h2>

                <span>Admin Panel</span>

            </div>

        </div>


        <nav class="sidebar-menu">

            <a href="../dashboard.php">

                <span>📊</span>
                <span>Dashboard</span>

            </a>


            <a href="../menu/index.php">

                <span>🍛</span>
                <span>Menu Makanan</span>

            </a>


            <a href="../kategori/index.php">

                <span>📂</span>
                <span>Kategori</span>

            </a>


            <a href="../pesanan/index.php">

                <span>🛒</span>
                <span>Pesanan</span>

            </a>


            <a
                href="index.php"
                class="active"
            >

                <span>👥</span>
                <span>Pelanggan</span>

            </a>


            <a href="../pembayaran/index.php">

                <span>💰</span>
                <span>Pembayaran</span>

            </a>


            <a href="../laporan/index.php">

                <span>📈</span>
                <span>Laporan</span>

            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../logout.php">

                <span>🚪</span>
                <span>Keluar</span>

            </a>

        </div>

    </aside>



    <!-- =========================
         MAIN
    ========================== -->

    <main class="main-content">


        <header class="page-header">

            <div>

                <h1>
                    Pelanggan
                </h1>

                <p>
                    Data pelanggan Warung Makan Niswah.
                </p>

            </div>

        </header>



        <!-- =========================
             TABLE
        ========================== -->

        <div class="table-card">


            <div class="table-header">

                <h3>
                    Daftar Pelanggan
                </h3>

                <p>
                    Pelanggan akan otomatis tercatat
                    ketika melakukan pemesanan.
                </p>

            </div>



            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                No
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                No. HP
                            </th>

                            <th>
                                Alamat
                            </th>

                            <th>
                                Pesanan
                            </th>

                            <th>
                                Total Belanja
                            </th>

                            <th>
                                Bergabung
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

                    ?>


                        <tr>


                            <!-- NO -->

                            <td>

                                <?= $no++; ?>

                            </td>



                            <!-- NAMA -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row['nama']
                                    ); ?>

                                </strong>

                            </td>



                            <!-- HP -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['no_hp']
                                ); ?>

                            </td>



                            <!-- ALAMAT -->

                            <td>

                                <span
                                    class="menu-description"
                                >

                                    <?= htmlspecialchars(
                                        $row['alamat']
                                    ); ?>

                                </span>

                            </td>



                            <!-- PESANAN -->

                            <td>

                                <span
                                    class="category-badge"
                                >

                                    <?= number_format(
                                        $row[
                                            'jumlah_pesanan'
                                        ]
                                    ); ?>

                                    Pesanan

                                </span>

                            </td>



                            <!-- TOTAL BELANJA -->

                            <td>

                                <strong>

                                    Rp<?= number_format(
                                        $row[
                                            'total_belanja'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </strong>

                            </td>



                            <!-- TANGGAL -->

                            <td>

                                <?= date(
                                    'd-m-Y',
                                    strtotime(
                                        $row[
                                            'created_at'
                                        ]
                                    )
                                ); ?>

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
                                    👥
                                </div>


                                <strong>

                                    Belum ada pelanggan

                                </strong>


                                <p>

                                    Data pelanggan akan
                                    muncul setelah ada
                                    pemesanan.

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

</body>

</html>