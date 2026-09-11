<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cekLogin()
{
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

?>