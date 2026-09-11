<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);
$menu_id = intval($_GET['menu_id'] ?? 0);

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE pilihan_menu
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

header(
    "Location: pilihan.php?id=" .
    $menu_id
);

exit;

?>