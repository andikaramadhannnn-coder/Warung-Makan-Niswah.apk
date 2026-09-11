<?php

session_start();

require_once "includes/config.php";


// =====================================================
// CEK REQUEST
// =====================================================

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    header("Location: menu.php");

    exit;

}


// =====================================================
// AMBIL DATA FORM
// =====================================================

$nama =
    trim(
        $_POST['nama'] ?? ''
    );

$noHp =
    trim(
        $_POST['no_hp'] ?? ''
    );

$lokasi =
    trim(
        $_POST['lokasi'] ?? ''
    );

$alamat =
    trim(
        $_POST['alamat'] ?? ''
    );

$catatanPengantaran =
    trim(
        $_POST['catatan_pengantaran'] ?? ''
    );

$metodePembayaran =
    $_POST['metode_pembayaran']
    ?? 'cod';


// =====================================================
// VALIDASI
// =====================================================

if (
    $nama === '' ||
    $noHp === '' ||
    $lokasi === '' ||
    $alamat === ''
) {

    die("

        <div style='
            font-family:Arial;
            padding:40px;
        '>

            <h2>
                Data belum lengkap
            </h2>

            <p>
                Silakan lengkapi semua data checkout.
            </p>

            <a href='checkout.php'>
                ← Kembali ke Checkout
            </a>

        </div>

    ");

}


// =====================================================
// VALIDASI METODE PEMBAYARAN
// =====================================================

$metodeValid = [

    'cod',

    'transfer',

    'qris'

];


if (
    !in_array(
        $metodePembayaran,
        $metodeValid,
        true
    )
) {

    $metodePembayaran =
        'cod';

}


// =====================================================
// CEK CART
// =====================================================

$cart =
    $_SESSION['cart']
    ?? [];


if (
    empty($cart)
) {

    header(
        "Location: menu.php"
    );

    exit;

}


// =====================================================
// ALAMAT LENGKAP
// =====================================================

$alamatLengkap =
    $lokasi .
    " - " .
    $alamat;


// =====================================================
// HITUNG TOTAL
// =====================================================

$totalHarga = 0;


foreach (
    $cart as $item
) {

    $jumlah =
        intval(
            $item['jumlah']
            ?? 0
        );

    $harga =
        floatval(
            $item['harga']
            ?? 0
        );


    if (
        $jumlah < 1
    ) {

        $jumlah = 1;

    }


    $totalHarga +=
        $harga *
        $jumlah;

}


// =====================================================
// MULAI TRANSAKSI DATABASE
// =====================================================

$conn->begin_transaction();


try {


    // =================================================
    // CARI PELANGGAN BERDASARKAN NO HP
    // =================================================

    $stmt =
        $conn->prepare("
            SELECT
                id
            FROM pelanggan
            WHERE no_hp = ?
            LIMIT 1
        ");


    $stmt->bind_param(
        "s",
        $noHp
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $pelanggan =
        $result->fetch_assoc();


    $stmt->close();


    // =================================================
    // PELANGGAN SUDAH ADA
    // =================================================

    if (
        $pelanggan
    ) {

        $pelangganId =
            intval(
                $pelanggan['id']
            );


        $stmt =
            $conn->prepare("
                UPDATE pelanggan

                SET
                    nama = ?,
                    alamat = ?

                WHERE id = ?
            ");


        $stmt->bind_param(
            "ssi",
            $nama,
            $alamatLengkap,
            $pelangganId
        );


        $stmt->execute();

        $stmt->close();

    }


    // =================================================
    // PELANGGAN BARU
    // =================================================

    else {

        $stmt =
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


        $stmt->bind_param(
            "sss",
            $nama,
            $noHp,
            $alamatLengkap
        );


        $stmt->execute();


        $pelangganId =
            $conn->insert_id;


        $stmt->close();

    }


    // =================================================
    // BUAT KODE PESANAN
    // =================================================

    $tanggal =
        date('Ymd');


    $kodePesanan =
        'NW-' .
        $tanggal .
        '-' .
        strtoupper(
            substr(
                uniqid(),
                -5
            )
        );


    // =================================================
    // CATATAN PESANAN
    // =================================================

    $catatanPesanan =
        $catatanPengantaran;


    // =================================================
    // INSERT PESANAN
    // =================================================

    $stmt =
        $conn->prepare("
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
                'menunggu',
                ?
            )
        ");


    $stmt->bind_param(
        "sidss",
        $kodePesanan,
        $pelangganId,
        $totalHarga,
        $metodePembayaran,
        $catatanPesanan
    );


    $stmt->execute();


    $pesananId =
        $conn->insert_id;


    $stmt->close();


    // =================================================
    // INSERT DETAIL PESANAN
    // =================================================

    foreach (
        $cart as $item
    ) {


        // =============================================
        // DATA ITEM
        // =============================================

        $menuId =
            intval(
                $item['menu_id']
                ?? 0
            );


        $namaMenu =
            $item['nama_menu']
            ?? 'Menu';


        $jumlah =
            intval(
                $item['jumlah']
                ?? 1
            );


        if (
            $jumlah < 1
        ) {

            $jumlah = 1;

        }


        // =============================================
        // HARGA MENU DASAR
        // =============================================

        $hargaMenu =
            floatval(
                $item['harga_menu']
                ??
                $item['harga']
                ??
                0
            );


        // =============================================
        // HARGA SATUAN FINAL
        //
        // Ini sudah termasuk pilihan.
        // =============================================

        $hargaSatuan =
            floatval(
                $item['harga']
                ??
                0
            );


        $subtotalMenu =
            $hargaSatuan *
            $jumlah;


        // =============================================
        // INSERT DETAIL PESANAN
        // =============================================

        $stmt =
            $conn->prepare("
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
            $menuId,
            $namaMenu,
            $hargaSatuan,
            $jumlah,
            $subtotalMenu
        );


        $stmt->execute();


        $detailPesananId =
            $conn->insert_id;


        $stmt->close();


        // =============================================
        // SIMPAN VARIAN MENU
        // =============================================
        //
        // Varian sudah menjadi bagian dari harga satuan
        // final. Di sini kita simpan nama variannya sebagai
        // detail pilihan agar tidak hilang saat cart
        // dikosongkan setelah checkout.
        // =============================================

        $namaVarian =
            trim(
                $item['varian']
                ?? ''
            );

        if ($namaVarian !== '') {

            $stmt =
                $conn->prepare("
                    INSERT INTO detail_pilihan_pesanan
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

            $tipeVarian = 'varian';
            $hargaTambahanVarian = 0;

            $stmt->bind_param(
                "issd",
                $detailPesananId,
                $tipeVarian,
                $namaVarian,
                $hargaTambahanVarian
            );

            $stmt->execute();

            $stmt->close();

        }


        // =============================================
        // AMBIL PILIHAN DINAMIS
        // =============================================

        $pilihan =
            $item['pilihan']
            ?? [];


        if (
            !is_array(
                $pilihan
            )
        ) {

            $pilihan = [];

        }


        // =============================================
        // SIMPAN SEMUA PILIHAN
        // =============================================

        foreach (
            $pilihan as $pilihanItem
        ) {


            $tipePilihan =
                $pilihanItem[
                    'tipe_pilihan'
                ]
                ??
                'pilihan';


            $namaPilihan =
                $pilihanItem[
                    'nama_pilihan'
                ]
                ??
                '';


            $hargaTambahan =
                floatval(
                    $pilihanItem[
                        'harga_tambahan'
                    ]
                    ?? 0
                );


            // -----------------------------------------
            // Jangan simpan pilihan kosong
            // -----------------------------------------

            if (
                $namaPilihan === ''
            ) {

                continue;

            }


            // =========================================
            // INSERT DETAIL PILIHAN
            // =========================================

            $stmt =
                $conn->prepare("
                    INSERT INTO detail_pilihan_pesanan
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


    // =================================================
    // SIMPAN PESANAN KE SESSION
    // =================================================

    $_SESSION[
        'pesanan_berhasil'
    ] = [

        'id' =>
            $pesananId,

        'kode' =>
            $kodePesanan,

        'total' =>
            $totalHarga,

        'metode' =>
            $metodePembayaran

    ];


    // =================================================
    // SIMPAN PESANAN TERAKHIR
    // UNTUK CEK STATUS
    // =================================================

    $_SESSION[
        'pesanan_terakhir'
    ] = [

        'kode' =>
            $kodePesanan,

        'no_hp' =>
            $noHp

    ];


    // =================================================
    // KOSONGKAN CART
    // =================================================

    $_SESSION['cart'] = [];


    // =================================================
    // COMMIT
    // =================================================

    $conn->commit();


    // =================================================
    // KE HALAMAN BERHASIL
    // =================================================

    header(
        "Location: pesanan-berhasil.php"
    );

    exit;


}


// =====================================================
// ERROR
// =====================================================

catch (
    Exception $e
) {


    // =================================================
    // ROLLBACK
    // =================================================

    $conn->rollback();


    die("

        <div style='
            font-family:Arial;
            padding:40px;
        '>

            <h2>
                ❌ Pesanan gagal diproses
            </h2>

            <p>
                Terjadi kesalahan saat menyimpan pesanan.
            </p>

            <p>

                <strong>
                    Error:
                </strong>

                "
                .
                htmlspecialchars(
                    $e->getMessage()
                )
                .

                "

            </p>


            <a
                href='checkout.php'
            >

                ← Kembali ke Checkout

            </a>

        </div>

    ");

}

?>