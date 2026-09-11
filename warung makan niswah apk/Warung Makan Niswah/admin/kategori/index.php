<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$result = $conn->query("
    SELECT 
        k.id,
        k.nama_kategori,
        k.created_at,
        COUNT(m.id) AS jumlah_menu
    FROM kategori k
    LEFT JOIN menu m
        ON k.id = m.kategori_id
    GROUP BY k.id
    ORDER BY k.id DESC
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

    <title>Kategori | Warung Makan Niswah</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-wrapper">

    <!-- SIDEBAR -->

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

            <a
                href="index.php"
                class="active"
            >
                <span>📂</span>
                <span>Kategori</span>
            </a>

            <a href="../pesanan/index.php">
                <span>🛒</span>
                <span>Pesanan</span>
            </a>

            <a href="../pelanggan/index.php">
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


    <!-- MAIN -->

    <main class="main-content">

        <header class="page-header">

            <div>

                <h1>Kategori</h1>

                <p>
                    Kelola kategori makanan dan minuman.
                </p>

            </div>

            <a
                href="tambah.php"
                class="btn-primary"
            >
                + Tambah Kategori
            </a>

        </header>


        <div class="table-card">

            <div class="table-header">

                <h3>Daftar Kategori</h3>

                <p>
                    Kategori yang digunakan pada menu.
                </p>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Nama Kategori</th>

                            <th>Jumlah Menu</th>

                            <th>Dibuat</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $no = 1;

                    if ($result->num_rows > 0):

                        while ($row = $result->fetch_assoc()):

                    ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $row['nama_kategori']
                                    ); ?>
                                </strong>

                            </td>

                            <td>

                                <span class="category-badge">

                                    <?= $row['jumlah_menu']; ?>

                                    Menu

                                </span>

                            </td>

                            <td>

                                <?= date(
                                    'd-m-Y',
                                    strtotime($row['created_at'])
                                ); ?>

                            </td>

                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="edit.php?id=<?= $row['id']; ?>"
                                        class="btn-edit"
                                        title="Edit"
                                    >
                                        ✏️
                                    </a>

                                    <?php if ($row['jumlah_menu'] == 0): ?>

                                        <a
                                            href="hapus.php?id=<?= $row['id']; ?>"
                                            class="btn-delete"
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                        >
                                            🗑️
                                        </a>

                                    <?php else: ?>

                                        <span
                                            class="btn-delete disabled-button"
                                            title="Kategori masih digunakan menu"
                                        >
                                            🔒
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
                                colspan="5"
                                class="empty-table"
                            >

                                <div>📂</div>

                                <strong>
                                    Belum ada kategori
                                </strong>

                                <p>
                                    Tambahkan kategori pertama.
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