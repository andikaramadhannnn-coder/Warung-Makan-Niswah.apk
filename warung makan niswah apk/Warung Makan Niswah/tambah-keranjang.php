<?php

session_start();

require_once "includes/config.php";


// =====================================================
// AMBIL DATA POST
// =====================================================

$menuId = intval(
    $_POST['menu_id'] ?? 0
);

$jumlah = intval(
    $_POST['jumlah'] ?? 1
);

$catatan = trim(
    $_POST['catatan'] ?? ''
);

$pilihanPost =
    $_POST['pilihan'] ?? [];

$varianId = intval(
    $_POST['varian_id'] ?? 0
);


// =====================================================
// VALIDASI DASAR
// =====================================================

if ($menuId <= 0) {

    header("Location: menu.php");
    exit;

}


if ($jumlah < 1) {

    $jumlah = 1;

}


// =====================================================
// PASTIKAN PILIHAN ARRAY
// =====================================================

if (!is_array($pilihanPost)) {

    $pilihanPost = [];

}


// =====================================================
// AMBIL DATA MENU
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        nama_menu,
        deskripsi,
        harga,
        foto,
        status
    FROM menu
    WHERE id = ?
      AND status = 'tersedia'
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: menu.php");
    exit;

}


$menu = $result->fetch_assoc();

$stmt->close();


// =====================================================
// VARIAN
// =====================================================

$varian = null;

$hargaVarian = null;


if ($varianId > 0) {

    $stmt = $conn->prepare("
        SELECT
            id,
            menu_id,
            nama_varian,
            harga,
            status,
            urutan
        FROM varian_menu
        WHERE id = ?
          AND menu_id = ?
          AND status = 'tersedia'
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $varianId,
        $menuId
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows === 1) {

        $varian =
            $result->fetch_assoc();

        $hargaVarian =
            floatval(
                $varian['harga']
            );

    }

    $stmt->close();

}


// =====================================================
// AMBIL KELOMPOK PILIHAN
// =====================================================

$kelompokData = [];

$stmt = $conn->prepare("
    SELECT
        id,
        nama_kelompok,
        tipe_pilihan,
        wajib,
        min_pilih,
        max_pilih,
        urutan
    FROM kelompok_pilihan
    WHERE menu_id = ?
      AND status = 'tersedia'
    ORDER BY
        urutan ASC,
        id ASC
");

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $kelompokId =
        intval($row['id']);

    $kelompokData[$kelompokId] = [

        'id' =>
            $kelompokId,

        'nama_kelompok' =>
            $row['nama_kelompok'],

        'tipe_pilihan' =>
            $row['tipe_pilihan'],

        'wajib' =>
            intval($row['wajib']),

        'min_pilih' =>
            intval($row['min_pilih']),

        'max_pilih' =>
            intval($row['max_pilih']),

        'urutan' =>
            intval($row['urutan']),

        'pilihan' =>
            []

    ];

}


$stmt->close();


// =====================================================
// KALAU TIDAK ADA KELOMPOK
// =====================================================

if (
    count($kelompokData) === 0
) {

    $pilihanPost = [];

}


// =====================================================
// PROSES PILIHAN
// =====================================================

$pilihanTerpilih = [];

$hargaPilihan = 0;


// =====================================================
// LOOP KELOMPOK
// =====================================================

foreach (
    $kelompokData
    as $kelompokId => $kelompok
) {


    $pilihanDalamKelompok =
        $pilihanPost[$kelompokId]
        ?? [];


    // =================================================
    // SINGLE
    // =================================================

    if (
        $kelompok['tipe_pilihan']
        === 'single'
    ) {


        if (
            is_array(
                $pilihanDalamKelompok
            )
        ) {

            $pilihanDalamKelompok =
                $pilihanDalamKelompok[0]
                ?? 0;

        }


        $pilihanDalamKelompok =
            intval(
                $pilihanDalamKelompok
            );


        if (
            $pilihanDalamKelompok > 0
        ) {

            $pilihanIds = [
                $pilihanDalamKelompok
            ];

        } else {

            $pilihanIds = [];

        }

    }


    // =================================================
    // MULTIPLE
    // =================================================

    else {


        if (
            !is_array(
                $pilihanDalamKelompok
            )
        ) {

            $pilihanDalamKelompok = [];

        }


        $pilihanIds = [];


        foreach (
            $pilihanDalamKelompok
            as $pilihanId
        ) {


            $pilihanId =
                intval(
                    $pilihanId
                );


            if (
                $pilihanId > 0
            ) {

                $pilihanIds[] =
                    $pilihanId;

            }

        }


        $pilihanIds =
            array_values(
                array_unique(
                    $pilihanIds
                )
            );

    }


    // =================================================
    // VALIDASI
    // =================================================

    $jumlahDipilih =
        count($pilihanIds);


    $minPilih =
        intval(
            $kelompok['min_pilih']
        );


    $maxPilih =
        intval(
            $kelompok['max_pilih']
        );


    $wajib =
        intval(
            $kelompok['wajib']
        );


    if (
        $wajib === 1 &&
        $jumlahDipilih === 0
    ) {

        header(
            "Location: detail-menu.php?id="
            . $menuId
        );

        exit;

    }


    if (
        $minPilih > 0 &&
        $jumlahDipilih < $minPilih
    ) {

        header(
            "Location: detail-menu.php?id="
            . $menuId
        );

        exit;

    }


    if (
        $maxPilih > 0 &&
        $jumlahDipilih > $maxPilih
    ) {

        header(
            "Location: detail-menu.php?id="
            . $menuId
        );

        exit;

    }


    // =================================================
    // AMBIL DATA PILIHAN
    // =================================================

    foreach (
        $pilihanIds
        as $pilihanId
    ) {


        $stmt = $conn->prepare("
            SELECT
                id,
                menu_id,
                kelompok_id,
                tipe_pilihan,
                nama_pilihan,
                harga_tambahan,
                status
            FROM pilihan_menu
            WHERE id = ?
              AND menu_id = ?
              AND kelompok_id = ?
              AND status = 'tersedia'
            LIMIT 1
        ");


        $stmt->bind_param(
            "iii",
            $pilihanId,
            $menuId,
            $kelompokId
        );


        $stmt->execute();

        $result =
            $stmt->get_result();


        $item =
            $result->fetch_assoc();


        $stmt->close();


        if (!$item) {

            header(
                "Location: detail-menu.php?id="
                . $menuId
            );

            exit;

        }


        $hargaTambahan =
            floatval(
                $item['harga_tambahan']
            );


        $hargaPilihan +=
            $hargaTambahan;


        $pilihanTerpilih[] = [

            'kelompok_id' =>
                intval(
                    $item['kelompok_id']
                ),

            'kelompok' =>
                $kelompok[
                    'nama_kelompok'
                ],

            'pilihan_id' =>
                intval(
                    $item['id']
                ),

            'nama_pilihan' =>
                $item['nama_pilihan'],

            'tipe_pilihan' =>
                $item['tipe_pilihan'],

            'harga_tambahan' =>
                $hargaTambahan

        ];

    }

}


// =====================================================
// HARGA DASAR
// =====================================================
//
// Kalau varian dipilih:
// harga varian = harga final menu
//
// Kalau tidak ada varian:
// gunakan harga menu biasa.
// =====================================================

$hargaMenu =
    floatval(
        $menu['harga']
    );


if (
    $hargaVarian !== null
) {

    $hargaDasar =
        $hargaVarian;

} else {

    $hargaDasar =
        $hargaMenu;

}


// =====================================================
// HARGA SATUAN
// =====================================================

$hargaSatuan =
    $hargaDasar +
    $hargaPilihan;


// =====================================================
// SIGNATURE PILIHAN
// =====================================================

$signaturePilihan = [];


foreach (
    $pilihanTerpilih
    as $item
) {


    $signaturePilihan[] = [

        'kelompok_id' =>
            intval(
                $item['kelompok_id']
            ),

        'pilihan_id' =>
            intval(
                $item['pilihan_id']
            )

    ];

}


// =====================================================
// URUTKAN PILIHAN
// =====================================================

usort(
    $signaturePilihan,
    function (
        $a,
        $b
    ) {


        if (
            $a['kelompok_id']
            ===
            $b['kelompok_id']
        ) {

            return
                $a['pilihan_id']
                <=>
                $b['pilihan_id'];

        }


        return
            $a['kelompok_id']
            <=>
            $b['kelompok_id'];

    }
);


// =====================================================
// CART SIGNATURE
// =====================================================

$signatureData = [

    'menu_id' =>
        $menuId,

    'varian_id' =>
        $varianId,

    'pilihan' =>
        $signaturePilihan

];


$cartKey =
    md5(
        json_encode(
            $signatureData
        )
    );


// =====================================================
// SIAPKAN CART
// =====================================================

if (
    !isset(
        $_SESSION['cart']
    )
    ||
    !is_array(
        $_SESSION['cart']
    )
) {

    $_SESSION['cart'] = [];

}


// =====================================================
// ITEM SUDAH ADA
// =====================================================

if (
    isset(
        $_SESSION['cart'][$cartKey]
    )
) {


    $_SESSION['cart']
        [$cartKey]
        ['jumlah']
        += $jumlah;


    if (
        $catatan !== ''
    ) {

        $_SESSION['cart']
            [$cartKey]
            ['catatan']
            = $catatan;

    }

}


// =====================================================
// ITEM BARU
// =====================================================

else {


    $_SESSION['cart'][$cartKey] = [

        // =================================================
        // IDENTITAS
        // =================================================

        'cart_key' =>
            $cartKey,

        'menu_id' =>
            $menuId,


        // =================================================
        // MENU
        // =================================================

        'nama_menu' =>
            $menu['nama_menu'],

        'harga_menu' =>
            $hargaMenu,

        'harga' =>
            $hargaSatuan,


        // =================================================
        // VARIAN
        // =================================================

        'varian_id' =>
            $varianId,

        'varian' =>
            $varian
                ? $varian['nama_varian']
                : '',

        'harga_varian' =>
            $hargaVarian !== null
                ? $hargaVarian
                : $hargaMenu,


        // =================================================
        // GAMBAR
        // =================================================

        'gambar' =>
            $menu['foto'] ?? '',

        'foto' =>
            $menu['foto'] ?? '',


        // =================================================
        // JUMLAH
        // =================================================

        'jumlah' =>
            $jumlah,


        // =================================================
        // PILIHAN
        // =================================================

        'pilihan' =>
            $pilihanTerpilih,


        // =================================================
        // CATATAN
        // =================================================

        'catatan' =>
            $catatan

    ];

}


// =====================================================
// KEMBALI KE KERANJANG
// =====================================================

header(
    "Location: keranjang.php"
);

exit;

?>