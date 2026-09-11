<?php

session_start();

require_once "includes/config.php";

/*
====================================================
 CEK PESANAN
====================================================
*/

if (
    !isset($_SESSION['pesanan_berhasil']) ||
    !isset($_SESSION['pesanan_berhasil']['id'])
) {
    $_SESSION['upload_error'] = "Data pesanan tidak ditemukan.";
    header("Location: pesanan-berhasil.php");
    exit;
}

$pesanan_id = (int) $_SESSION['pesanan_berhasil']['id'];

/*
====================================================
 HANYA BOLEH POST
====================================================
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 CEK FILE
====================================================
*/

if (
    !isset($_FILES['bukti_pembayaran']) ||
    $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['upload_error'] =
        "Silakan pilih bukti pembayaran terlebih dahulu.";

    header("Location: pesanan-berhasil.php");
    exit;
}

$file = $_FILES['bukti_pembayaran'];

/*
====================================================
 BATAS UKURAN FILE
 Maksimal 2 MB
====================================================
*/

$max_size = 2 * 1024 * 1024;

if ($file['size'] > $max_size) {
    $_SESSION['upload_error'] =
        "Ukuran bukti pembayaran maksimal 2 MB.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 CEK GAMBAR
====================================================
*/

$image_info = @getimagesize($file['tmp_name']);

if ($image_info === false) {
    $_SESSION['upload_error'] =
        "File yang diupload harus berupa gambar.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 CEK MIME TYPE
====================================================
*/

$allowed_types = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$file_type = $image_info['mime'];

if (!isset($allowed_types[$file_type])) {
    $_SESSION['upload_error'] =
        "Format tidak didukung. Gunakan JPG, PNG, atau WEBP.";

    header("Location: pesanan-berhasil.php");
    exit;
}

$extension = $allowed_types[$file_type];

/*
====================================================
 AMBIL DATA PESANAN
====================================================
*/

$stmt = $conn->prepare("
    SELECT
        id,
        kode_pesanan,
        metode_pembayaran,
        status_pembayaran,
        bukti_pembayaran
    FROM pesanan
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    $_SESSION['upload_error'] =
        "Gagal memproses data pesanan.";

    header("Location: pesanan-berhasil.php");
    exit;
}

$stmt->bind_param("i", $pesanan_id);
$stmt->execute();

$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();

$stmt->close();

if (!$pesanan) {
    $_SESSION['upload_error'] =
        "Pesanan tidak ditemukan.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 HARUS QRIS
====================================================
*/

if ($pesanan['metode_pembayaran'] !== 'qris') {
    $_SESSION['upload_error'] =
        "Upload bukti pembayaran hanya tersedia untuk pembayaran QRIS.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 CEK STATUS PEMBAYARAN
====================================================
*/

if (
    $pesanan['status_pembayaran'] === 'menunggu_verifikasi' ||
    $pesanan['status_pembayaran'] === 'terverifikasi'
) {
    $_SESSION['upload_error'] =
        "Bukti pembayaran sudah dikirim dan sedang diproses.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 FOLDER UPLOAD
====================================================
*/

$upload_dir = __DIR__ . "/assets/uploads/bukti-pembayaran/";

if (!is_dir($upload_dir)) {

    if (!mkdir($upload_dir, 0755, true)) {

        $_SESSION['upload_error'] =
            "Folder upload bukti pembayaran gagal dibuat.";

        header("Location: pesanan-berhasil.php");
        exit;
    }
}

/*
====================================================
 NAMA FILE
====================================================
*/

$kode_pesanan = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '',
    $pesanan['kode_pesanan']
);

$random_name = bin2hex(random_bytes(6));

$file_name =
    "bukti_" .
    $kode_pesanan .
    "_" .
    $random_name .
    "." .
    $extension;

$target_file = $upload_dir . $file_name;

/*
====================================================
 PINDAHKAN FILE
====================================================
*/

if (!move_uploaded_file($file['tmp_name'], $target_file)) {

    $_SESSION['upload_error'] =
        "Bukti pembayaran gagal diupload.";

    header("Location: pesanan-berhasil.php");
    exit;
}

/*
====================================================
 HAPUS BUKTI LAMA JIKA ADA
====================================================
*/

if (!empty($pesanan['bukti_pembayaran'])) {

    $old_file =
        __DIR__ .
        "/assets/uploads/bukti-pembayaran/" .
        basename($pesanan['bukti_pembayaran']);

    if (file_exists($old_file)) {
        @unlink($old_file);
    }
}

/*
====================================================
 SIMPAN KE DATABASE
====================================================
*/

$stmt = $conn->prepare("
    UPDATE pesanan
    SET
        status_pembayaran = 'menunggu_verifikasi',
        bukti_pembayaran = ?,
        tanggal_pembayaran = NOW()
    WHERE id = ?
");

if (!$stmt) {

    @unlink($target_file);

    $_SESSION['upload_error'] =
        "Gagal menyimpan bukti pembayaran.";

    header("Location: pesanan-berhasil.php");
    exit;
}

$stmt->bind_param(
    "si",
    $file_name,
    $pesanan_id
);

if (!$stmt->execute()) {

    @unlink($target_file);

    $_SESSION['upload_error'] =
        "Bukti pembayaran gagal disimpan.";

    $stmt->close();

    header("Location: pesanan-berhasil.php");
    exit;
}

$stmt->close();

/*
====================================================
 PESAN SUKSES
====================================================
*/

$_SESSION['upload_success'] =
    "Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.";

/*
====================================================
 KEMBALI
====================================================
*/

header("Location: pesanan-berhasil.php");
exit;