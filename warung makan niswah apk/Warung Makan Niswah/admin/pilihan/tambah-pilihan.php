<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL ID KELOMPOK & MENU
// =====================================================

$kelompokId = intval(
    $_GET['kelompok_id']
    ?? $_POST['kelompok_id']
    ?? 0
);

$menuId = intval(
    $_GET['menu_id']
    ?? $_POST['menu_id']
    ?? 0
);


// =====================================================
// VALIDASI ID
// =====================================================

if ($kelompokId <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA KELOMPOK
// =====================================================

$stmt = $conn->prepare("
    SELECT
        kp.id,
        kp.menu_id,
        kp.nama_kelompok,
        kp.tipe_pilihan,
        kp.wajib,
        kp.min_pilih,
        kp.max_pilih,
        kp.urutan,
        kp.status,

        m.nama_menu,
        m.harga AS harga_menu,
        m.foto

    FROM kelompok_pilihan kp

    INNER JOIN menu m
        ON m.id = kp.menu_id

    WHERE kp.id = ?

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $kelompokId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$kelompok =
    $result->fetch_assoc();

$stmt->close();


// =====================================================
// PAKAI MENU_ID DARI DATABASE
// =====================================================

$menuId =
    intval(
        $kelompok['menu_id']
    );


// =====================================================
// DEFAULT
// =====================================================

$error = '';

$namaPilihan = '';

$hargaTambahan = 0;

$status = 'tersedia';


// =====================================================
// PROSES TAMBAH
// =====================================================

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $namaPilihan =
        trim(
            $_POST['nama_pilihan']
            ?? ''
        );

    $hargaTambahan =
        floatval(
            $_POST['harga_tambahan']
            ?? 0
        );

    $status =
        $_POST['status']
        ?? 'tersedia';


    // =================================================
    // VALIDASI
    // =================================================

    if (
        $namaPilihan === ''
    ) {

        $error =
            'Nama pilihan wajib diisi.';

    } elseif (
        $hargaTambahan < 0
    ) {

        $error =
            'Harga tambahan tidak boleh negatif.';

    } elseif (
        !in_array(
            $status,
            [
                'tersedia',
                'tidak_tersedia'
            ],
            true
        )
    ) {

        $error =
            'Status pilihan tidak valid.';

    } else {


        // =============================================
        // CEK DUPLIKAT
        // =============================================

        $stmt = $conn->prepare("
            SELECT id
            FROM pilihan_menu
            WHERE menu_id = ?
              AND kelompok_id = ?
              AND nama_pilihan = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "iis",
            $menuId,
            $kelompokId,
            $namaPilihan
        );

        $stmt->execute();

        $cek =
            $stmt->get_result();

        $sudahAda =
            $cek->num_rows > 0;

        $stmt->close();


        if ($sudahAda) {

            $error =
                'Pilihan dengan nama tersebut sudah ada di kelompok ini.';

        }


        // =============================================
        // SIMPAN
        // =============================================

        if (
            $error === ''
        ) {

            $stmt = $conn->prepare("
                INSERT INTO pilihan_menu (
                    menu_id,
                    kelompok_id,
                    tipe_pilihan,
                    nama_pilihan,
                    harga_tambahan,
                    status
                )
                VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");


            $tipePilihan =
                $kelompok[
                    'nama_kelompok'
                ];


            /*
             * tipe_pilihan harus mengikuti
             * tipe kelompok.
             *
             * Contoh:
             * Bagian Ayam → bagian_ayam
             * Sambal      → sambal
             * Tambahan    → tambahan
             */

            $namaKelompokLower =
                strtolower(
                    trim(
                        $kelompok[
                            'nama_kelompok'
                        ]
                    )
                );


            if (
                strpos(
                    $namaKelompokLower,
                    'ayam'
                ) !== false
            ) {

                $tipePilihan =
                    'bagian_ayam';

            } elseif (
                strpos(
                    $namaKelompokLower,
                    'sambal'
                ) !== false
            ) {

                $tipePilihan =
                    'sambal';

            } elseif (
                strpos(
                    $namaKelompokLower,
                    'tambahan'
                ) !== false
                ||
                strpos(
                    $namaKelompokLower,
                    'topping'
                ) !== false
            ) {

                $tipePilihan =
                    'tambahan';

            } else {

                /*
                 * Untuk kelompok lain,
                 * gunakan nama kelompok
                 * sebagai tipe.
                 */

                $tipePilihan =
                    $namaKelompokLower;

            }


            $stmt->bind_param(
                "iissds",
                $menuId,
                $kelompokId,
                $tipePilihan,
                $namaPilihan,
                $hargaTambahan,
                $status
            );


            if (
                $stmt->execute()
            ) {

                $stmt->close();

                header(
                    "Location: kelompok.php?menu_id="
                    . $menuId
                );

                exit;

            } else {

                $error =
                    'Gagal menyimpan pilihan: '
                    . $stmt->error;

                $stmt->close();

            }

        }

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

    <title>
        Tambah Pilihan |
        <?= htmlspecialchars(
            $kelompok[
                'nama_kelompok'
            ]
        ); ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >


    <style>

        /* =====================================================
           HEADER
        ===================================================== */

        .form-page-header {

            margin-bottom:
                25px;

        }


        .back-link {

            display:
                inline-block;

            margin-bottom:
                15px;

            color:
                #e87518;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                700;

        }


        .form-page-header h1 {

            margin:
                0 0 7px;

        }


        .form-page-header p {

            margin:
                0;

            color:
                #777;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .choice-form-card {

            width:
                100%;

            max-width:
                850px;

            box-sizing:
                border-box;

            background:
                #fff;

            border:
                1px solid #eee;

            border-radius:
                18px;

            padding:
                28px;

        }


        /* =====================================================
           INFO
        ===================================================== */

        .choice-info {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;

            background:
                #fff8ef;

            border:
                1px solid #f4dfca;

            border-radius:
                14px;

            padding:
                16px;

            margin-bottom:
                25px;

        }


        .choice-icon {

            width:
                55px;

            height:
                55px;

            border-radius:
                12px;

            background:
                #fff0df;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                27px;

            flex-shrink:
                0;

        }


        .choice-info strong {

            display:
                block;

            font-size:
                16px;

            margin-bottom:
                4px;

        }


        .choice-info span {

            color:
                #777;

            font-size:
                12px;

        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-box {

            background:
                #ffe8e8;

            border:
                1px solid #ffcaca;

            color:
                #c62828;

            border-radius:
                10px;

            padding:
                13px 15px;

            margin-bottom:
                20px;

            font-size:
                13px;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {

            margin-bottom:
                22px;

        }


        .form-group label {

            display:
                block;

            margin-bottom:
                8px;

            font-size:
                13px;

            font-weight:
                800;

        }


        .required {

            color:
                #e87518;

        }


        .form-control {

            width:
                100%;

            box-sizing:
                border-box;

            padding:
                13px 14px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            background:
                white;

            font-size:
                14px;

            outline:
                none;

        }


        .form-control:focus {

            border-color:
                #e87518;

            box-shadow:
                0 0 0 3px
                rgba(
                    232,
                    117,
                    24,
                    .10
                );

        }


        .form-help {

            display:
                block;

            color:
                #888;

            font-size:
                11px;

            margin-top:
                6px;

        }


        /* =====================================================
           PRICE
        ===================================================== */

        .price-input-wrapper {

            position:
                relative;

        }


        .price-prefix {

            position:
                absolute;

            left:
                14px;

            top:
                50%;

            transform:
                translateY(-50%);

            color:
                #777;

            font-weight:
                700;

            font-size:
                13px;

        }


        .price-input {

            padding-left:
                45px;

        }


        /* =====================================================
           PREVIEW
        ===================================================== */

        .preview-box {

            margin-top:
                5px;

            padding:
                18px;

            background:
                #fafafa;

            border:
                1px solid #eee;

            border-radius:
                12px;

        }


        .preview-title {

            font-size:
                11px;

            color:
                #888;

            margin-bottom:
                10px;

        }


        .preview-item {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                10px;

        }


        .preview-name {

            font-weight:
                800;

        }


        .preview-price {

            color:
                #e87518;

            font-weight:
                800;

        }


        /* =====================================================
           ACTION
        ===================================================== */

        .form-actions {

            display:
                flex;

            justify-content:
                flex-end;

            gap:
                10px;

            border-top:
                1px solid #eee;

            padding-top:
                22px;

            margin-top:
                25px;

        }


        .btn-cancel {

            padding:
                12px 18px;

            border:
                1px solid #ddd;

            border-radius:
                10px;

            background:
                #fff;

            color:
                #555;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                700;

        }


        .btn-save {

            padding:
                12px 20px;

            border:
                none;

            border-radius:
                10px;

            background:
                #e87518;

            color:
                white;

            font-size:
                13px;

            font-weight:
                800;

            cursor:
                pointer;

        }


        .btn-save:hover {

            background:
                #d76510;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (
            max-width: 700px
        ) {

            .choice-form-card {

                padding:
                    20px;

            }


            .form-actions {

                flex-direction:
                    column-reverse;

            }


            .btn-cancel,
            .btn-save {

                width:
                    100%;

                box-sizing:
                    border-box;

                text-align:
                    center;

            }

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =====================================================
         SIDEBAR
    ===================================================== -->

    <aside class="sidebar">


        <div class="sidebar-logo">

            <div class="logo-icon">
                🍛
            </div>

            <div class="brand-text">

                <h2>
                    Niswah
                </h2>

                <span>
                    Admin Panel
                </span>

            </div>

        </div>


        <nav class="sidebar-menu">


            <a href="../dashboard.php">

                <span>📊</span>

                <span>
                    Dashboard
                </span>

            </a>


            <a href="../menu/index.php">

                <span>🍛</span>

                <span>
                    Menu Makanan
                </span>

            </a>


            <a href="../kategori/index.php">

                <span>📂</span>

                <span>
                    Kategori
                </span>

            </a>


            <a href="../pesanan/index.php">

                <span>🛒</span>

                <span>
                    Pesanan
                </span>

            </a>


            <a href="../pelanggan/index.php">

                <span>👥</span>

                <span>
                    Pelanggan
                </span>

            </a>


            <a href="../pembayaran/index.php">

                <span>💰</span>

                <span>
                    Pembayaran
                </span>

            </a>


            <a href="../laporan/index.php">

                <span>📈</span>

                <span>
                    Laporan
                </span>

            </a>


            <a
                href="index.php"
                class="active"
            >

                <span>⚙️</span>

                <span>
                    Pilihan Menu
                </span>

            </a>


        </nav>


        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="logout"
            >

                <span>
                    🚪
                </span>

                <span>
                    Keluar
                </span>

            </a>

        </div>


    </aside>



    <!-- =====================================================
         MAIN
    ===================================================== -->

    <main class="main-content">


        <div class="form-page-header">


            <a
                href="kelompok.php?menu_id=<?= $menuId; ?>"
                class="back-link"
            >

                ← Kembali ke
                <?= htmlspecialchars(
                    $kelompok[
                        'nama_kelompok'
                    ]
                ); ?>

            </a>


            <h1>
                Tambah Pilihan
            </h1>


            <p>
                Tambahkan pilihan baru
                untuk kelompok menu.
            </p>


        </div>



        <div
            class="choice-form-card"
        >


            <!-- =================================================
                 INFO KELOMPOK
            ================================================== -->

            <div
                class="choice-info"
            >

                <div
                    class="choice-icon"
                >

                    <?php

                    $namaKelompok =
                        strtolower(
                            $kelompok[
                                'nama_kelompok'
                            ]
                        );

                    if (
                        strpos(
                            $namaKelompok,
                            'ayam'
                        ) !== false
                    ) {

                        echo '🍗';

                    } elseif (
                        strpos(
                            $namaKelompok,
                            'sambal'
                        ) !== false
                    ) {

                        echo '🌶️';

                    } elseif (
                        strpos(
                            $namaKelompok,
                            'tambahan'
                        ) !== false
                        ||
                        strpos(
                            $namaKelompok,
                            'topping'
                        ) !== false
                    ) {

                        echo '➕';

                    } else {

                        echo '⚙️';

                    }

                    ?>

                </div>


                <div>

                    <strong>

                        <?= htmlspecialchars(
                            $kelompok[
                                'nama_kelompok'
                            ]
                        ); ?>

                    </strong>


                    <span>

                        Menu:
                        <?= htmlspecialchars(
                            $kelompok[
                                'nama_menu'
                            ]
                        ); ?>

                        ·

                        <?php if (
                            $kelompok[
                                'wajib'
                            ]
                            == 1
                        ): ?>

                            Wajib

                        <?php else: ?>

                            Opsional

                        <?php endif; ?>

                    </span>

                </div>

            </div>



            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if (
                $error !== ''
            ): ?>

                <div
                    class="error-box"
                >

                    ⚠️
                    <?= htmlspecialchars(
                        $error
                    ); ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
            >


                <input
                    type="hidden"
                    name="kelompok_id"
                    value="<?= $kelompokId; ?>"
                >


                <input
                    type="hidden"
                    name="menu_id"
                    value="<?= $menuId; ?>"
                >



                <!-- =============================================
                     NAMA
                ============================================== -->

                <div
                    class="form-group"
                >

                    <label>

                        Nama Pilihan

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        name="nama_pilihan"
                        id="namaPilihan"
                        class="form-control"
                        placeholder="Contoh: Dada"
                        value="<?= htmlspecialchars(
                            $namaPilihan
                        ); ?>"
                        required
                    >


                    <small
                        class="form-help"
                    >

                        Contoh:
                        Dada, Paha, Sayap,
                        Sambal Merah,
                        Telur, Extra Sambal.

                    </small>

                </div>



                <!-- =============================================
                     HARGA
                ============================================== -->

                <div
                    class="form-group"
                >

                    <label>

                        Harga Tambahan

                    </label>


                    <div
                        class="price-input-wrapper"
                    >

                        <span
                            class="price-prefix"
                        >
                            Rp
                        </span>


                        <input
                            type="number"
                            name="harga_tambahan"
                            id="hargaTambahan"
                            class="form-control price-input"
                            min="0"
                            step="100"
                            value="<?= htmlspecialchars(
                                $hargaTambahan
                            ); ?>"
                        >

                    </div>


                    <small
                        class="form-help"
                    >

                        Isi 0 jika pilihan gratis.

                        Contoh:
                        Telur = 5000.

                    </small>

                </div>



                <!-- =============================================
                     STATUS
                ============================================== -->

                <div
                    class="form-group"
                >

                    <label>
                        Status
                    </label>


                    <select
                        name="status"
                        class="form-control"
                    >

                        <option
                            value="tersedia"
                            <?= $status === 'tersedia'
                                ? 'selected'
                                : ''; ?>
                        >

                            Tersedia

                        </option>


                        <option
                            value="tidak_tersedia"
                            <?= $status === 'tidak_tersedia'
                                ? 'selected'
                                : ''; ?>
                        >

                            Tidak Tersedia

                        </option>

                    </select>


                    <small
                        class="form-help"
                    >

                        Pilihan tidak tersedia
                        tidak akan ditampilkan
                        kepada pelanggan.

                    </small>

                </div>



                <!-- =============================================
                     PREVIEW
                ============================================== -->

                <div
                    class="preview-box"
                >

                    <div
                        class="preview-title"
                    >

                        Preview pilihan

                    </div>


                    <div
                        class="preview-item"
                    >

                        <span
                            class="preview-name"
                            id="previewName"
                        >

                            Nama Pilihan

                        </span>


                        <span
                            class="preview-price"
                            id="previewPrice"
                        >

                            Gratis

                        </span>

                    </div>

                </div>



                <!-- =============================================
                     ACTION
                ============================================== -->

                <div
                    class="form-actions"
                >


                    <a
                        href="kelompok.php?menu_id=<?= $menuId; ?>"
                        class="btn-cancel"
                    >

                        Batal

                    </a>


                    <button
                        type="submit"
                        class="btn-save"
                    >

                        💾 Simpan Pilihan

                    </button>


                </div>


            </form>


        </div>


    </main>


</div>



<script>


// =====================================================
// PREVIEW
// =====================================================

const namaPilihan =
    document.getElementById(
        'namaPilihan'
    );

const hargaTambahan =
    document.getElementById(
        'hargaTambahan'
    );

const previewName =
    document.getElementById(
        'previewName'
    );

const previewPrice =
    document.getElementById(
        'previewPrice'
    );


function formatRupiah(
    angka
) {

    angka =
        Number(
            angka
        ) || 0;


    return new Intl.NumberFormat(
        'id-ID'
    ).format(
        angka
    );

}


function updatePreview() {

    const nama =
        namaPilihan.value.trim();


    const harga =
        Number(
            hargaTambahan.value
        ) || 0;


    previewName.textContent =
        nama !== ''
            ? nama
            : 'Nama Pilihan';


    if (
        harga > 0
    ) {

        previewPrice.textContent =
            '+Rp'
            +
            formatRupiah(
                harga
            );

    } else {

        previewPrice.textContent =
            'Gratis';

    }

}


namaPilihan.addEventListener(
    'input',
    updatePreview
);


hargaTambahan.addEventListener(
    'input',
    updatePreview
);


updatePreview();

</script>


</body>

</html>