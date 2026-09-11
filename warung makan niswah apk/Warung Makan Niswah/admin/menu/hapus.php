<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {

    // Ambil foto menu
    $stmt = $conn->prepare(
        "SELECT foto FROM menu WHERE id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $menu = $result->fetch_assoc();

        /*
         * Hapus pilihan menu terlebih dahulu
         */
        $hapusPilihan = $conn->prepare(
            "DELETE FROM pilihan_menu WHERE menu_id = ?"
        );

        $hapusPilihan->bind_param("i", $id);
        $hapusPilihan->execute();
        $hapusPilihan->close();


        /*
         * Hapus kelompok pilihan
         */
        $hapusKelompok = $conn->prepare(
            "DELETE FROM kelompok_pilihan WHERE menu_id = ?"
        );

        $hapusKelompok->bind_param("i", $id);
        $hapusKelompok->execute();
        $hapusKelompok->close();


        /*
         * Hapus varian menu
         */
        $hapusVarian = $conn->prepare(
            "DELETE FROM varian_menu WHERE menu_id = ?"
        );

        $hapusVarian->bind_param("i", $id);
        $hapusVarian->execute();
        $hapusVarian->close();


        /*
         * Hapus foto dari folder
         */
        if (!empty($menu['foto'])) {

            $file =
                "../../assets/uploads/menu/" .
                $menu['foto'];

            if (file_exists($file)) {
                unlink($file);
            }
        }


        /*
         * Hapus menu
         */
        $delete = $conn->prepare(
            "DELETE FROM menu WHERE id = ?"
        );

        $delete->bind_param("i", $id);

        $delete->execute();

        $delete->close();
    }

    $stmt->close();
}

header("Location: index.php");
exit;

?>