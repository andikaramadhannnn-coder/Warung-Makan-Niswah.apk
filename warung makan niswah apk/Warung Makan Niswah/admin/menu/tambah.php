<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$error = "";

$kategori = $conn->query(
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_menu = trim($_POST['nama_menu']);
    $kategori_id = intval($_POST['kategori_id']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = floatval($_POST['harga']);
    $status = $_POST['status'];

    if (
        $nama_menu === "" ||
        $kategori_id <= 0 ||
        $harga <= 0
    ) {

        $error = "Nama, kategori, dan harga wajib diisi.";

    } else {

        $foto = null;

        if (
            isset($_FILES['foto']) &&
            $_FILES['foto']['error'] === UPLOAD_ERR_OK
        ) {

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $filename = $_FILES['foto']['name'];

            $extension = strtolower(
                pathinfo(
                    $filename,
                    PATHINFO_EXTENSION
                )
            );

            if (!in_array($extension, $allowed)) {

                $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";

            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {

                $error = "Ukuran foto maksimal 2 MB.";

            } else {

                $foto = 
    uniqid('menu_') . 
    '.' . 
    $extension;

$uploadDir = __DIR__ . "/../../assets/uploads/menu/";

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$uploadPath = $uploadDir . $foto;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
    $foto = null;
    $error = "Foto gagal diupload. Pastikan folder assets/uploads/menu tersedia dan bisa ditulis.";
}
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare("
                INSERT INTO menu
                (
                    kategori_id,
                    nama_menu,
                    deskripsi,
                    harga,
                    foto,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "issdss",
                $kategori_id,
                $nama_menu,
                $deskripsi,
                $harga,
                $foto,
                $status
            );

            if ($stmt->execute()) {

                header("Location: index.php");

                exit;

            } else {

                $error = "Menu gagal ditambahkan.";
            }

            $stmt->close();
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

    <title>Tambah Menu | Warung Makan Niswah</title>

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

            <a
                href="index.php"
                class="active"
            >
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

                <h1>Tambah Menu</h1>

                <p>
                    Tambahkan makanan atau minuman baru.
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


            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Nama Menu *
                        </label>

                        <input
                            type="text"
                            name="nama_menu"
                            placeholder="Contoh: Nasi Ayam Geprek"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Kategori *
                        </label>

                        <select
                            name="kategori_id"
                            required
                        >

                            <option value="">
                                -- Pilih Kategori --
                            </option>

                            <?php while ($kat = $kategori->fetch_assoc()): ?>

                                <option
                                    value="<?= $kat['id']; ?>"
                                >
                                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Harga *
                        </label>

                        <input
                            type="number"
                            name="harga"
                            min="1"
                            placeholder="15000"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Status *
                        </label>

                        <select name="status">

                            <option value="tersedia">
                                Tersedia
                            </option>

                            <option value="habis">
                                Habis
                            </option>

                        </select>

                    </div>


                    <div class="form-group full-width">

                        <label>
                            Deskripsi
                        </label>

                        <textarea
                            name="deskripsi"
                            rows="5"
                            placeholder="Contoh: Nasi putih dengan ayam geprek..."
                        ></textarea>

                    </div>


                    <div class="form-group full-width">

                        <label>
                            Foto Menu
                        </label>

                        <input
                            type="file"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="form-help">
                            Maksimal 2 MB.
                        </small>

                    </div>

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
                        Simpan Menu
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>