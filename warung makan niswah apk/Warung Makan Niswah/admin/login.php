<?php

session_start();

require_once "../includes/config.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password, nama
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            /*
             * Untuk tahap awal project,
             * password admin menggunakan admin123.
             */

            if ($username === "admin" && $password === "admin123") {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Username atau password salah.";

            }

        } else {

            $error = "Username atau password salah.";

        }

        $stmt->close();
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

    <title>Login Admin | Warung Makan Niswah</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body class="login-page">

    <div class="login-container">

        <div class="login-card">

            <div class="login-logo">
                🍛
            </div>

            <h1>Warung Makan Niswah</h1>

            <p class="login-subtitle">
                Admin Panel
            </p>

            <?php if ($error): ?>

                <div class="alert-error">
                    <?= htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="form-group">

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn-login"
                >
                    Masuk ke Dashboard
                </button>

            </form>

            <div class="login-footer">
                © 2026 Warung Makan Niswah
            </div>

        </div>

    </div>

</body>

</html>