<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_kategori = trim($_POST['nama_kategori']);

    if ($nama_kategori === "") {

        $error = "Nama kategori wajib diisi.";

    } else {

        // Cek kategori yang sama
        $cek = $conn->prepare("
            SELECT id
            FROM kategori
            WHERE nama_kategori = ?
            LIMIT 1
        ");

        $cek->bind_param(
            "s",
            $nama_kategori
        );

        $cek->execute();

        $result = $cek->get_result();

        if ($result->num_rows > 0) {

            $error = "Kategori tersebut sudah ada.";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO kategori
                (nama_kategori)
                VALUES (?)
            ");

            $stmt->bind_param(
                "s",
                $nama_kategori
            );

            if ($stmt->execute()) {

                header("Location: index.php");

                exit;

            } else {

                $error = "Kategori gagal ditambahkan.";

            }

            $stmt->close();
        }

        $cek->close();
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

    <title>Tambah Kategori | Warung Makan Niswah</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-wrapper">

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

        </nav>


        <div class="sidebar-bottom">

            <a href="../logout.php">

                <span>🚪</span>
                <span>Keluar</span>

            </a>

        </div>

    </aside>


    <main class="main-content">

        <header class="page-header">

            <div>

                <h1>Tambah Kategori</h1>

                <p>
                    Tambahkan kategori menu baru.
                </p>

            </div>

            <a
                href="index.php"
                class="btn-secondary"
            >
                ← Kembali
            </a>

        </header>


        <div class="form-card">

            <?php if ($error): ?>

                <div class="alert-error">
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="form-group">

                    <label>
                        Nama Kategori *
                    </label>

                    <input
                        type="text"
                        name="nama_kategori"
                        placeholder="Contoh: Makanan"
                        required
                    >

                </div>


                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn-secondary"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Simpan Kategori
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>