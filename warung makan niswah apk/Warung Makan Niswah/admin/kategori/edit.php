<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {

    header("Location: index.php");

    exit;
}


$stmt = $conn->prepare("
    SELECT *
    FROM kategori
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    header("Location: index.php");

    exit;
}


$kategori = $result->fetch_assoc();

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_kategori =
        trim($_POST['nama_kategori']);


    if ($nama_kategori === "") {

        $error =
            "Nama kategori wajib diisi.";

    } else {

        // Cek nama kategori agar tidak duplikat
        $cek = $conn->prepare("
            SELECT id
            FROM kategori
            WHERE nama_kategori = ?
            AND id != ?
            LIMIT 1
        ");

        $cek->bind_param(
            "si",
            $nama_kategori,
            $id
        );

        $cek->execute();

        $cekResult =
            $cek->get_result();


        if ($cekResult->num_rows > 0) {

            $error =
                "Nama kategori tersebut sudah digunakan.";

        } else {

            $update = $conn->prepare("
                UPDATE kategori
                SET nama_kategori = ?
                WHERE id = ?
            ");

            $update->bind_param(
                "si",
                $nama_kategori,
                $id
            );


            if ($update->execute()) {

                header("Location: index.php");

                exit;

            } else {

                $error =
                    "Kategori gagal diperbarui.";

            }

            $update->close();
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

    <title>Edit Kategori | Warung Makan Niswah</title>

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

                <h1>Edit Kategori</h1>

                <p>
                    Perbarui nama kategori.
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
                        value="<?= htmlspecialchars(
                            $kategori['nama_kategori']
                        ); ?>"
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
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>