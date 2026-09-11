<?php

session_start();

require_once "includes/config.php";


// =========================
// CEK CART
// =========================

$cart = $_SESSION['cart'] ?? [];

if (count($cart) === 0) {

    header("Location: keranjang.php");
    exit;

}


// =========================
// AMBIL DATA FORM
// =========================

$nama =
    trim($_POST['nama'] ?? '');

$no_hp =
    trim($_POST['no_hp'] ?? '');

$alamat =
    trim($_POST['alamat'] ?? '');

$catatan =
    trim($_POST['catatan'] ?? '');

$metode_pembayaran =
    $_POST['metode_pembayaran'] ?? '';


// =========================
// VALIDASI
// =========================

if (
    $nama === '' ||
    $no_hp === '' ||
    $alamat === '' ||
    $metode_pembayaran === ''
) {

    die("
        <h2>Data belum lengkap</h2>
        <p>Silakan kembali ke halaman checkout.</p>
        <a href='checkout.php'>
            Kembali ke Checkout
        </a>
    ");

}


$metodeValid = [
    'qris',
    'tunai'
];


if (
    !in_array(
        $metode_pembayaran,
        $metodeValid,
        true
    )
) {

    die("
        <h2>Metode pembayaran tidak valid.</h2>
        <a href='checkout.php'>
            Kembali ke Checkout
        </a>
    ");

}


// =========================
// HITUNG TOTAL
// =========================

$totalHarga = 0;

foreach (
    $cart as $item
) {

    $totalHarga +=
        (float) $item['subtotal'];

}


// =========================
// KODE PESANAN
// =========================

$kodePesanan =
    'NIS-' .
    date('YmdHis') .
    '-' .
    strtoupper(
        substr(
            uniqid(),
            -4
        )
    );


// =========================
// MULAI TRANSACTION
// =========================

$conn->begin_transaction();


try {


    // =========================
    // CARI PELANGGAN
    // =========================

    $stmt = $conn->prepare("
        SELECT id
        FROM pelanggan
        WHERE no_hp = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "s",
        $no_hp
    );

    $stmt->execute();

    $pelangganResult =
        $stmt->get_result();


    if (
        $pelangganResult->num_rows > 0
    ) {

        // Pelanggan sudah ada

        $pelanggan =
            $pelangganResult->fetch_assoc();

        $pelangganId =
            $pelanggan['id'];

    } else {

        // Pelanggan baru

        $stmtInsert =
            $conn->prepare("
                INSERT INTO pelanggan
                (
                    nama,
                    no_hp,
                    alamat
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ");

        $stmtInsert->bind_param(
            "sss",
            $nama,
            $no_hp,
            $alamat
        );

        $stmtInsert->execute();

        $pelangganId =
            $conn->insert_id;

        $stmtInsert->close();

    }

    $stmt->close();


    // =========================
    // PESANAN
    // =========================

    $statusPesanan =
        'menunggu';


    $stmt = $conn->prepare("
        INSERT INTO pesanan
        (
            kode_pesanan,
            pelanggan_id,
            total_harga,
            metode_pembayaran,
            status_pesanan,
            catatan
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->bind_param(
        "sidsss",
        $kodePesanan,
        $pelangganId,
        $totalHarga,
        $metode_pembayaran,
        $statusPesanan,
        $catatan
    );

    $stmt->execute();

    $pesananId =
        $conn->insert_id;

    $stmt->close();


    // =========================
    // DETAIL PESANAN
    // =========================

    foreach (
        $cart as $item
    ) {


        $namaMenu =
            $item['nama_menu'];

        $harga =
            (float) $item['harga_satuan'];

        $jumlah =
            (int) $item['jumlah'];

        $subtotal =
            (float) $item['subtotal'];


        $stmt = $conn->prepare("
            INSERT INTO detail_pesanan
            (
                pesanan_id,
                menu_id,
                nama_menu,
                harga,
                jumlah,
                subtotal
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


        $stmt->bind_param(
            "iisdid",
            $pesananId,
            $item['menu_id'],
            $namaMenu,
            $harga,
            $jumlah,
            $subtotal
        );


        $stmt->execute();


        $detailPesananId =
            $conn->insert_id;


        $stmt->close();


        // =========================
        // PILIHAN
        // =========================

        if (
            !empty(
                $item['pilihan']
            )
        ) {


            foreach (
                $item['pilihan']
                as $pilihan
            ) {


                $tipePilihan =
                    $pilihan[
                        'tipe_pilihan'
                    ];


                $namaPilihan =
                    $pilihan[
                        'nama_pilihan'
                    ];


                $hargaTambahan =
                    (float)
                    $pilihan[
                        'harga_tambahan'
                    ];


                $stmt = $conn->prepare("
                    INSERT INTO
                    detail_pilihan_pesanan
                    (
                        detail_pesanan_id,
                        tipe_pilihan,
                        nama_pilihan,
                        harga_tambahan
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");


                $stmt->bind_param(
                    "issd",
                    $detailPesananId,
                    $tipePilihan,
                    $namaPilihan,
                    $hargaTambahan
                );


                $stmt->execute();

                $stmt->close();

            }

        }

    }


    // =========================
    // COMMIT
    // =========================

    $conn->commit();


    // =========================
    // KOSONGKAN CART
    // =========================

    unset(
        $_SESSION['cart']
    );


    // =========================
    // SIMPAN INFO ORDER
    // =========================

    $_SESSION[
        'pesanan_berhasil'
    ] = [

        'id' =>
            $pesananId,

        'kode' =>
            $kodePesanan,

        'total' =>
            $totalHarga

    ];


    // =========================
    // REDIRECT
    // =========================

    header(
        "Location: pesanan-berhasil.php"
    );

    exit;


} catch (
    Exception $e
) {


    // =========================
    // ROLLBACK
    // =========================

    $conn->rollback();


    echo "

        <!DOCTYPE html>

        <html lang='id'>

        <head>

            <meta charset='UTF-8'>

            <meta
                name='viewport'
                content='width=device-width,
                initial-scale=1.0'
            >

            <title>
                Pesanan Gagal
            </title>

            <style>

                body {
                    font-family: Arial, sans-serif;
                    background: #fffaf5;
                    padding: 40px 20px;
                }

                .error-box {
                    max-width: 500px;
                    margin: 50px auto;
                    padding: 30px;
                    background: white;
                    border-radius: 18px;
                    text-align: center;
                    border: 1px solid #eee;
                }

                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 18px;
                    background: #e87518;
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                }

            </style>

        </head>

        <body>

            <div class='error-box'>

                <div style='font-size:50px;'>
                    ❌
                </div>

                <h2>
                    Pesanan Gagal
                </h2>

                <p>
                    Terjadi kesalahan saat
                    menyimpan pesanan.
                </p>

                <a href='checkout.php'>
                    Kembali ke Checkout
                </a>

            </div>

        </body>

        </html>

    ";

}

?>