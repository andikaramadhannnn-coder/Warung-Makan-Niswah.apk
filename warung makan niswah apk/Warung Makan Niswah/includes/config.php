<?php

$host = "sql105.infinityfree.com";
$user = "if0_42822050";
$pass = "Niswah2026abc";
$db   = "if0_42822050_niswah";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
