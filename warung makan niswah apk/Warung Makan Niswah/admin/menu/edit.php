<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT * FROM menu WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    header("Location: index.php");
    exit;
}

$menu = $result->fetch_assoc();

$kategori = $conn->query(
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

$error = "";

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

        $foto = $menu['foto'];

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

            $extension = strtolower(
                pathinfo(
                    $_FILES['foto']['name'],
                    PATHINFO_EXTENSION
                )
            );

            if (!in_array($extension, $allowed)) {

                $error = "Format foto tidak diperbolehkan.";

            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {

                $error = "Ukuran foto maksimal 2 MB.";

            } else {

                $newFoto =
                    uniqid('menu_') .
                    '.' .
                    $extension;

                $uploadDir = __DIR__ . "/../../assets/uploads/menu/";

                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }

                $uploadPath = $uploadDir . $newFoto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {

                    if (!empty($menu['foto'])) {
                        $oldPath = $uploadDir . $menu['foto'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $foto = $newFoto;
                } else {
                    $error = "Foto gagal diupload. Pastikan folder assets/uploads/menu tersedia dan bisa ditulis.";
                }
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare("
                UPDATE menu
                SET
                    kategori_id = ?,
                    nama_menu = ?,
                    deskripsi = ?,
                    harga = ?,
                    foto = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "issdssi",
                $kategori_id,
                $nama_menu,
                $deskripsi,
                $harga,
                $foto,
                $status,
                $id
            );

            if ($stmt->execute()) {

                header("Location: index.php");

                exit;

            } else {

                $error = "Menu gagal diperbarui.";
            }
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

    <title>Edit Menu | Warung Makan Niswah</title>

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

                <h1>Edit Menu</h1>

                <p>
                    Perbarui informasi menu.
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
                            value="<?= htmlspecialchars($menu['nama_menu']); ?>"
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

                            <?php while ($kat = $kategori->fetch_assoc()): ?>

                                <option
                                    value="<?= $kat['id']; ?>"
                                    <?= $menu['kategori_id'] == $kat['id'] ? 'selected' : ''; ?>
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
                            value="<?= (int)$menu['harga']; ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Status
                        </label>

                        <select name="status">

                            <option
                                value="tersedia"
                                <?= $menu['status'] === 'tersedia' ? 'selected' : ''; ?>
                            >
                                Tersedia
                            </option>

                            <option
                                value="habis"
                                <?= $menu['status'] === 'habis' ? 'selected' : ''; ?>
                            >
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
                        ><?= htmlspecialchars($menu['deskripsi']); ?></textarea>

                    </div>


                    <div class="form-group full-width">

                        <label>
                            Foto Menu
                        </label>

                        <?php if (!empty($menu['foto'])): ?>

                            <img
                                src="../../assets/uploads/menu/<?= htmlspecialchars($menu['foto']); ?>"
                                class="preview-image"
                                alt="Foto <?= htmlspecialchars($menu['nama_menu']); ?>"
                            >

                        <?php endif; ?>

                        <input
                            type="file"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="form-help">
                            Kosongkan jika tidak ingin mengganti foto.
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
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>