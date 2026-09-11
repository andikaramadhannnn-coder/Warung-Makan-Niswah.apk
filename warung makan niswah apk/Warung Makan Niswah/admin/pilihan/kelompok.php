<?php

require_once "../../includes/config.php";
require_once "../../includes/functions.php";

cekLogin();


// =====================================================
// AMBIL MENU ID
// =====================================================

$menuId = intval(
    $_GET['menu_id'] ?? 0
);

if ($menuId <= 0) {

    header("Location: index.php");
    exit;

}


// =====================================================
// AMBIL DATA MENU
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id,
        nama_menu,
        harga,
        foto,
        status
    FROM menu
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}

$menu = $result->fetch_assoc();

$stmt->close();


// =====================================================
// AMBIL SEMUA KELOMPOK + PILIHAN
// =====================================================

$kelompok = [];

$stmt = $conn->prepare("
    SELECT
        kp.id AS kelompok_id,
        kp.nama_kelompok,
        kp.tipe_pilihan,
        kp.wajib,
        kp.min_pilih,
        kp.max_pilih,
        kp.urutan,
        kp.status AS kelompok_status,

        pm.id AS pilihan_id,
        pm.nama_pilihan,
        pm.harga_tambahan,
        pm.status AS pilihan_status

    FROM kelompok_pilihan kp

    LEFT JOIN pilihan_menu pm
        ON pm.kelompok_id = kp.id
        AND pm.menu_id = kp.menu_id

    WHERE kp.menu_id = ?

    ORDER BY
        kp.urutan ASC,
        kp.id ASC,
        pm.id ASC
");

$stmt->bind_param(
    "i",
    $menuId
);

$stmt->execute();

$result = $stmt->get_result();


while (
    $row = $result->fetch_assoc()
) {

    $kelompokId =
        intval(
            $row['kelompok_id']
        );


    if (
        !isset(
            $kelompok[$kelompokId]
        )
    ) {

        $kelompok[$kelompokId] = [

            'id' =>
                $kelompokId,

            'nama_kelompok' =>
                $row['nama_kelompok'],

            'tipe_pilihan' =>
                $row['tipe_pilihan'],

            'wajib' =>
                intval(
                    $row['wajib']
                ),

            'min_pilih' =>
                intval(
                    $row['min_pilih']
                ),

            'max_pilih' =>
                intval(
                    $row['max_pilih']
                ),

            'urutan' =>
                intval(
                    $row['urutan']
                ),

            'status' =>
                $row['kelompok_status'],

            'pilihan' => []

        ];

    }


    if (
        !empty(
            $row['pilihan_id']
        )
    ) {

        $kelompok[$kelompokId]['pilihan'][] = [

            'id' =>
                intval(
                    $row['pilihan_id']
                ),

            'nama_pilihan' =>
                $row['nama_pilihan'],

            'harga_tambahan' =>
                floatval(
                    $row['harga_tambahan']
                ),

            'status' =>
                $row['pilihan_status']

        ];

    }

}

$stmt->close();


// =====================================================
// HITUNG TOTAL
// =====================================================

$totalKelompok =
    count(
        $kelompok
    );

$totalPilihan = 0;

foreach (
    $kelompok
    as $group
) {

    $totalPilihan +=
        count(
            $group['pilihan']
        );

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
        Kelola Pilihan |
        <?= htmlspecialchars(
            $menu['nama_menu']
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

        .choice-page-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 25px;

        }


        .choice-page-title {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        .choice-menu-image {

            width: 65px;

            height: 65px;

            border-radius: 14px;

            overflow: hidden;

            background: #f7eee5;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

            flex-shrink: 0;

        }


        .choice-menu-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .choice-page-title h1 {

            margin: 0 0 5px;

        }


        .choice-page-title p {

            margin: 0;

            color: #777;

        }


        /* =====================================================
           SUMMARY
        ===================================================== */

        .choice-summary {

            display: flex;

            gap: 10px;

            margin-bottom: 25px;

            flex-wrap: wrap;

        }


        .summary-box {

            background: white;

            border: 1px solid #eee;

            border-radius: 12px;

            padding: 12px 18px;

        }


        .summary-box strong {

            font-size: 20px;

            margin-right: 5px;

        }


        .summary-box span {

            color: #777;

            font-size: 12px;

        }


        /* =====================================================
           GROUP CARD
        ===================================================== */

        .group-card {

            background: white;

            border: 1px solid #eee;

            border-radius: 18px;

            margin-bottom: 20px;

            overflow: hidden;

        }


        .group-header {

            padding: 20px;

            background: #fffaf5;

            border-bottom: 1px solid #eee;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 15px;

        }


        .group-title {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .group-icon {

            width: 40px;

            height: 40px;

            background: #fff0df;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;

        }


        .group-title h3 {

            margin: 0 0 5px;

            font-size: 17px;

        }


        .group-meta {

            color: #888;

            font-size: 11px;

        }


        /* =====================================================
           BADGES
        ===================================================== */

        .badge {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 700;

            margin-right: 4px;

        }


        .badge-required {

            background: #ffe8e8;

            color: #c62828;

        }


        .badge-optional {

            background: #eaf5ff;

            color: #1976d2;

        }


        .badge-single {

            background: #eee9ff;

            color: #6941c6;

        }


        .badge-multiple {

            background: #e8f7ed;

            color: #218838;

        }


        .badge-off {

            background: #eee;

            color: #777;

        }


        /* =====================================================
           GROUP ACTION
        ===================================================== */

        .group-actions {

            display: flex;

            gap: 7px;

            flex-shrink: 0;

        }


        .small-btn {

            width: 34px;

            height: 34px;

            border: 1px solid #ddd;

            border-radius: 9px;

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            background: white;

        }


        .small-btn:hover {

            border-color: #e87518;

        }


        /* =====================================================
           PILIHAN LIST
        ===================================================== */

        .choice-list {

            padding: 5px 20px 20px;

        }


        .choice-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            padding: 15px 0;

            border-bottom: 1px solid #f0f0f0;

        }


        .choice-row:last-child {

            border-bottom: none;

        }


        .choice-name {

            font-size: 14px;

            font-weight: 700;

        }


        .choice-status {

            font-size: 10px;

            padding: 4px 8px;

            border-radius: 20px;

            margin-left: 7px;

        }


        .choice-status.active {

            background: #e8f7ed;

            color: #218838;

        }


        .choice-status.inactive {

            background: #eee;

            color: #777;

        }


        .choice-price {

            color: #e87518;

            font-size: 13px;

            font-weight: 700;

        }


        .choice-actions {

            display: flex;

            align-items: center;

            gap: 6px;

        }


        .choice-actions a {

            text-decoration: none;

            width: 32px;

            height: 32px;

            display: flex;

            align-items: center;

            justify-content: center;

            border: 1px solid #eee;

            border-radius: 8px;

        }


        /* =====================================================
           ADD CHOICE
        ===================================================== */

        .add-choice-button {

            display: block;

            padding: 11px;

            border: 1px dashed #e87518;

            border-radius: 10px;

            color: #e87518;

            text-align: center;

            font-size: 12px;

            font-weight: 800;

            text-decoration: none;

            margin-top: 5px;

        }


        .add-choice-button:hover {

            background: #fff8ef;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-group {

            padding: 25px;

            text-align: center;

            color: #888;

        }


        .empty-group strong {

            display: block;

            margin-bottom: 5px;

            color: #555;

        }


        .empty-choice {

            background: white;

            border: 1px solid #eee;

            border-radius: 18px;

            padding: 60px 20px;

            text-align: center;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .btn-add-group {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 12px 17px;

            background: #e87518;

            color: white;

            border-radius: 10px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 800;

        }


        .btn-add-group:hover {

            background: #d76510;

        }


        .btn-back {

            display: inline-block;

            margin-bottom: 15px;

            color: #e87518;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

        }


        /* =====================================================
           SIMPAN & KEMBALI
        ===================================================== */

        .save-page-actions {

            display: flex;

            justify-content: flex-end;

            margin-top: 25px;

            padding: 5px 0 35px;

        }


        .btn-save-page {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 14px 22px;

            background: #e87518;

            color: white;

            border-radius: 11px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 800;

            transition: .2s;

            box-shadow:
                0 5px 15px
                rgba(232, 117, 24, .15);

        }


        .btn-save-page:hover {

            background: #d76510;

            transform: translateY(-1px);

            box-shadow:
                0 7px 18px
                rgba(232, 117, 24, .20);

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (
            max-width: 700px
        ) {

            .choice-page-header {

                flex-direction: column;

            }


            .choice-page-header
            .btn-add-group {

                width: 100%;

                box-sizing: border-box;

                justify-content: center;

            }


            .group-header {

                flex-direction: column;

            }


            .group-actions {

                width: 100%;

            }


            .choice-row {

                align-items: flex-start;

            }


            .choice-actions {

                flex-shrink: 0;

            }


            .save-page-actions {

                justify-content: stretch;

            }


            .btn-save-page {

                width: 100%;

                box-sizing: border-box;

                text-align: center;

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


        <!-- KEMBALI -->

        <a
            href="index.php"
            class="btn-back"
        >

            ← Kembali ke Pilihan Menu

        </a>


        <!-- =================================================
             HEADER
        ================================================= -->

        <header
            class="page-header choice-page-header"
        >


            <div
                class="choice-page-title"
            >


                <div
                    class="choice-menu-image"
                >

                    <?php if (
                        !empty(
                            $menu['foto']
                        )
                    ): ?>

                        <img
                            src="../../assets/uploads/menu/<?= htmlspecialchars(
                                $menu['foto']
                            ); ?>"
                            alt="<?= htmlspecialchars(
                                $menu['nama_menu']
                            ); ?>"
                        >

                    <?php else: ?>

                        🍛

                    <?php endif; ?>

                </div>


                <div>

                    <h1>

                        <?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>

                    </h1>


                    <p>
                        Kelola semua pilihan menu
                    </p>

                </div>


            </div>


            <a
                href="tambah-kelompok.php?menu_id=<?= $menuId; ?>"
                class="btn-add-group"
            >

                + Tambah Kelompok

            </a>


        </header>


        <!-- =================================================
             SUMMARY
        ================================================= -->

        <div
            class="choice-summary"
        >


            <div
                class="summary-box"
            >

                <strong>
                    <?= $totalKelompok; ?>
                </strong>

                <span>
                    Kelompok
                </span>

            </div>


            <div
                class="summary-box"
            >

                <strong>
                    <?= $totalPilihan; ?>
                </strong>

                <span>
                    Pilihan
                </span>

            </div>


            <div
                class="summary-box"
            >

                <strong>

                    Rp<?= number_format(
                        floatval(
                            $menu['harga']
                        ),
                        0,
                        ',',
                        '.'
                    ); ?>

                </strong>

                <span>
                    Harga Dasar
                </span>

            </div>


        </div>


        <!-- =================================================
             KELOMPOK
        ================================================= -->

        <?php if (
            !empty($kelompok)
        ): ?>


            <?php foreach (
                $kelompok
                as $group
            ): ?>


                <section
                    class="group-card"
                >


                    <!-- GROUP HEADER -->

                    <div
                        class="group-header"
                    >


                        <div
                            class="group-title"
                        >


                            <div
                                class="group-icon"
                            >

                                <?php if (
                                    $group[
                                        'tipe_pilihan'
                                    ]
                                    ===
                                    'single'
                                ): ?>

                                    🔘

                                <?php else: ?>

                                    ☑️

                                <?php endif; ?>

                            </div>


                            <div>


                                <h3>

                                    <?= htmlspecialchars(
                                        $group[
                                            'nama_kelompok'
                                        ]
                                    ); ?>

                                </h3>


                                <div
                                    class="group-meta"
                                >

                                    <?php if (
                                        $group[
                                            'tipe_pilihan'
                                        ]
                                        ===
                                        'single'
                                    ): ?>

                                        Pilih satu

                                    <?php else: ?>

                                        Pilih lebih dari satu

                                    <?php endif; ?>


                                    <?php if (
                                        $group[
                                            'min_pilih'
                                        ] > 0
                                    ): ?>

                                        · Min
                                        <?= $group[
                                            'min_pilih'
                                        ]; ?>

                                    <?php endif; ?>


                                    <?php if (
                                        $group[
                                            'max_pilih'
                                        ] > 0
                                    ): ?>

                                        · Max
                                        <?= $group[
                                            'max_pilih'
                                        ]; ?>

                                    <?php endif; ?>

                                </div>


                                <div
                                    style="margin-top:8px;"
                                >


                                    <?php if (
                                        $group[
                                            'wajib'
                                        ] === 1
                                    ): ?>

                                        <span
                                            class="badge badge-required"
                                        >
                                            Wajib
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge badge-optional"
                                        >
                                            Opsional
                                        </span>

                                    <?php endif; ?>


                                    <?php if (
                                        $group[
                                            'tipe_pilihan'
                                        ]
                                        ===
                                        'single'
                                    ): ?>

                                        <span
                                            class="badge badge-single"
                                        >
                                            Single
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge badge-multiple"
                                        >
                                            Multiple
                                        </span>

                                    <?php endif; ?>


                                    <?php if (
                                        $group[
                                            'status'
                                        ]
                                        !==
                                        'tersedia'
                                    ): ?>

                                        <span
                                            class="badge badge-off"
                                        >
                                            Nonaktif
                                        </span>

                                    <?php endif; ?>


                                </div>


                            </div>


                        </div>


                        <!-- ACTION GROUP -->

                        <div
                            class="group-actions"
                        >


                            <a
                                href="edit-kelompok.php?id=<?= $group['id']; ?>"
                                class="small-btn"
                                title="Edit kelompok"
                            >

                                ✏️

                            </a>


                            <a
                                href="hapus-kelompok.php?id=<?= $group['id']; ?>&menu_id=<?= $menuId; ?>"
                                class="small-btn"
                                title="Hapus kelompok"
                                onclick="return confirm('Yakin ingin menghapus kelompok ini beserta semua pilihannya?')"
                            >

                                🗑️

                            </a>


                        </div>


                    </div>


                    <!-- PILIHAN -->

                    <div
                        class="choice-list"
                    >


                        <?php if (
                            !empty(
                                $group[
                                    'pilihan'
                                ]
                            )
                        ): ?>


                            <?php foreach (
                                $group[
                                    'pilihan'
                                ]
                                as $item
                            ): ?>


                                <div
                                    class="choice-row"
                                >


                                    <div>


                                        <span
                                            class="choice-name"
                                        >

                                            <?= htmlspecialchars(
                                                $item[
                                                    'nama_pilihan'
                                                ]
                                            ); ?>

                                        </span>


                                        <?php if (
                                            $item[
                                                'status'
                                            ]
                                            ===
                                            'tersedia'
                                        ): ?>

                                            <span
                                                class="choice-status active"
                                            >
                                                Aktif
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="choice-status inactive"
                                            >
                                                Nonaktif
                                            </span>

                                        <?php endif; ?>


                                    </div>


                                    <div
                                        class="choice-actions"
                                    >


                                        <span
                                            class="choice-price"
                                        >

                                            <?php if (
                                                floatval(
                                                    $item[
                                                        'harga_tambahan'
                                                    ]
                                                ) > 0
                                            ): ?>

                                                +Rp<?= number_format(
                                                    floatval(
                                                        $item[
                                                            'harga_tambahan'
                                                        ]
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            <?php else: ?>

                                                Gratis

                                            <?php endif; ?>

                                        </span>


                                        <a
                                            href="status-pilihan.php?id=<?= $item['id']; ?>&menu_id=<?= $menuId; ?>"
                                            title="Ubah status"
                                        >
                                            🔄
                                        </a>


                                        <a
                                            href="edit-pilihan.php?id=<?= $item['id']; ?>"
                                            title="Edit pilihan"
                                        >
                                            ✏️
                                        </a>


                                        <a
                                            href="hapus-pilihan.php?id=<?= $item['id']; ?>&menu_id=<?= $menuId; ?>"
                                            title="Hapus pilihan"
                                            onclick="return confirm('Yakin ingin menghapus pilihan ini?')"
                                        >
                                            🗑️
                                        </a>


                                    </div>


                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <div
                                class="empty-group"
                            >

                                <strong>
                                    Belum ada pilihan
                                </strong>

                                <span>
                                    Tambahkan pilihan
                                    untuk kelompok ini.
                                </span>

                            </div>


                        <?php endif; ?>


                        <!-- TAMBAH PILIHAN -->

                        <a
                            href="tambah-pilihan.php?kelompok_id=<?= $group['id']; ?>&menu_id=<?= $menuId; ?>"
                            class="add-choice-button"
                        >

                            + Tambah Pilihan ke
                            <?= htmlspecialchars(
                                $group[
                                    'nama_kelompok'
                                ]
                            ); ?>

                        </a>


                    </div>


                </section>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- =================================================
                 BELUM ADA KELOMPOK
            ================================================== -->

            <div
                class="empty-choice"
            >


                <div
                    style="
                        font-size:60px;
                        margin-bottom:15px;
                    "
                >
                    ⚙️
                </div>


                <h3>
                    Belum Ada Kelompok Pilihan
                </h3>


                <p
                    style="
                        color:#777;
                        margin-bottom:20px;
                    "
                >

                    Tambahkan kelompok seperti
                    Bagian Ayam, Sambal,
                    Topping, Ukuran,
                    dan lainnya.

                </p>


                <a
                    href="tambah-kelompok.php?menu_id=<?= $menuId; ?>"
                    class="btn-add-group"
                >

                    + Tambah Kelompok Pertama

                </a>


            </div>


        <?php endif; ?>


        <!-- =================================================
             SIMPAN & KEMBALI
        ================================================== -->

        <div
            class="save-page-actions"
        >

            <a
                href="index.php"
                class="btn-save-page"
            >

                💾 Simpan & Kembali ke Pilihan Menu

            </a>

        </div>


    </main>


</div>


</body>

</html>