<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE menu
        SET status =
            CASE
                WHEN status = 'tersedia'
                THEN 'habis'
                ELSE 'tersedia'
            END
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();
}

header("Location: index.php");
exit;

?>