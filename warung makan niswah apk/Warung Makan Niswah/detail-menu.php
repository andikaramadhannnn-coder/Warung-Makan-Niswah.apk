<?php

session_start();

require_once "includes/config.php";


// =====================================================
// HITUNG JUMLAH CART
// =====================================================

$cart = $_SESSION['cart'] ?? [];

$jumlahCart = 0;

foreach ($cart as $item) {

    $jumlahCart += intval(
        $item['jumlah'] ?? 0
    );

}


// =====================================================
// AMBIL ID MENU
// =====================================================

$id = intval(
    $_GET['id'] ?? 0
);


if ($id <= 0) {

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
    $id
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
// AMBIL VARIAN
// =====================================================

$varianList = [];

$stmt = $conn->prepare("
    SELECT
        id,
        menu_id,
        nama_varian,
        harga,
        status,
        urutan
    FROM varian_menu
    WHERE menu_id = ?
      AND status = 'tersedia'
    ORDER BY
        urutan ASC,
        id ASC
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $varianList[] = $row;

}

$stmt->close();


// =====================================================
// AMBIL KELOMPOK PILIHAN + PILIHAN
// =====================================================

$kelompokPilihan = [];

$stmt = $conn->prepare("
    SELECT

        kp.id AS kelompok_id,
        kp.menu_id,
        kp.nama_kelompok,
        kp.tipe_pilihan,
        kp.wajib,
        kp.min_pilih,
        kp.max_pilih,
        kp.urutan,

        pm.id AS pilihan_id,
        pm.nama_pilihan,
        pm.harga_tambahan

    FROM kelompok_pilihan kp

    LEFT JOIN pilihan_menu pm
        ON pm.kelompok_id = kp.id
        AND pm.menu_id = kp.menu_id
        AND pm.status = 'tersedia'

    WHERE kp.menu_id = ?
      AND kp.status = 'tersedia'

    ORDER BY
        kp.urutan ASC,
        kp.id ASC,
        pm.id ASC
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $kelompokId =
        intval(
            $row['kelompok_id']
        );


    if (
        !isset(
            $kelompokPilihan[$kelompokId]
        )
    ) {

        $kelompokPilihan[$kelompokId] = [

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

            'pilihan' =>
                []

        ];

    }


    if (
        !empty(
            $row['pilihan_id']
        )
    ) {

        $kelompokPilihan[$kelompokId]['pilihan'][] = [

            'id' =>
                intval(
                    $row['pilihan_id']
                ),

            'nama_pilihan' =>
                $row['nama_pilihan'],

            'harga_tambahan' =>
                floatval(
                    $row['harga_tambahan']
                )

        ];

    }

}

$stmt->close();


// =====================================================
// URUTKAN KELOMPOK
// =====================================================

$kelompokPilihan =
    array_values(
        $kelompokPilihan
    );


usort(
    $kelompokPilihan,
    function ($a, $b) {

        return
            $a['urutan']
            <=>
            $b['urutan'];

    }
);


// =====================================================
// ADA VARIAN?
// =====================================================

$adaVarian =
    count($varianList) > 0;


// =====================================================
// VARIAN PERTAMA UNTUK HARGA AWAL
// =====================================================

$hargaAwal =
    floatval(
        $menu['harga']
    );


if ($adaVarian) {

    $hargaAwal =
        floatval(
            $varianList[0]['harga']
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

        <?= htmlspecialchars(
            $menu['nama_menu']
        ); ?>

        | Warung Makan Niswah

    </title>


    <link
        rel="stylesheet"
        href="assets/css/customer.css"
    >


    <style>

        /* =====================================================
           DETAIL
        ===================================================== */

        .detail-page {

            padding:
                40px 0 70px;

        }


        .detail-back {

            display: inline-block;

            margin-bottom: 25px;

            color: #e87518;

            font-weight: 700;

            font-size: 14px;

            text-decoration: none;

        }


        .detail-back:hover {

            text-decoration: underline;

        }


        .product-detail {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 40px;

            background: white;

            border: 1px solid #eee;

            border-radius: 22px;

            overflow: hidden;

        }


        /* =====================================================
           IMAGE
        ===================================================== */

        .product-image {

            min-height: 450px;

            background: #f6eee5;

        }


        .product-image img {

            width: 100%;

            height: 100%;

            min-height: 450px;

            object-fit: cover;

            display: block;

        }


        .product-no-image {

            min-height: 450px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 90px;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .product-content {

            padding:
                40px 40px 40px 0;

        }


        .product-content h1 {

            font-size: 34px;

            margin:
                0 0 10px;

        }


        .product-price {

            color: #e87518;

            font-size: 24px;

            font-weight: 800;

            margin-bottom: 18px;

        }


        .product-description {

            color: #777;

            font-size: 14px;

            line-height: 1.7;

            margin-bottom: 30px;

        }


        /* =====================================================
           OPTION SECTION
        ===================================================== */

        .option-section {

            margin-bottom: 25px;

        }


        .option-title {

            font-size: 15px;

            font-weight: 800;

            margin-bottom: 12px;

        }


        .required-label {

            color: #e87518;

            font-size: 11px;

            margin-left: 5px;

        }


        .option-help {

            color: #999;

            font-size: 11px;

            margin:
                -5px 0 10px;

        }


        .option-list {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

        }


        .option-item {

            position: relative;

        }


        .option-item input {

            position: absolute;

            opacity: 0;

            pointer-events: none;

        }


        .option-label {

            display: block;

            padding:
                12px 15px;

            border:
                1px solid #ddd;

            border-radius: 11px;

            cursor: pointer;

            font-size: 13px;

            background: white;

            transition: .2s;

        }


        .option-label:hover {

            border-color: #e87518;

        }


        .option-item
        input:checked
        + .option-label {

            border-color: #e87518;

            background: #fff0df;

            color: #d76510;

            font-weight: 700;

        }


        .option-price {

            font-size: 11px;

            color: #888;

            margin-left: 3px;

        }


        .variant-price {

            font-size: 12px;

            color: #e87518;

            font-weight: 800;

            margin-left: 5px;

        }


        /* =====================================================
           QUANTITY
        ===================================================== */

        .quantity-control {

            display: flex;

            align-items: center;

            width: fit-content;

            border: 1px solid #ddd;

            border-radius: 11px;

            overflow: hidden;

            margin-bottom: 25px;

        }


        .quantity-control button {

            width: 45px;

            height: 42px;

            border: 0;

            background: #f7f7f7;

            cursor: pointer;

            font-size: 20px;

            font-weight: 700;

        }


        .quantity-control button:hover {

            background: #fff0df;

            color: #e87518;

        }


        .quantity-control input {

            width: 55px;

            height: 42px;

            border: 0;

            border-left: 1px solid #eee;

            border-right: 1px solid #eee;

            text-align: center;

            font-weight: 700;

            font-size: 15px;

            outline: none;

        }


        /* =====================================================
           NOTE
        ===================================================== */

        .note-label {

            display: block;

            font-size: 14px;

            font-weight: 700;

            margin-bottom: 8px;

        }


        .note-input {

            width: 100%;

            min-height: 80px;

            padding: 12px;

            border: 1px solid #ddd;

            border-radius: 11px;

            resize: vertical;

            font-family: inherit;

            margin-bottom: 20px;

            outline: none;

            box-sizing: border-box;

        }


        .note-input:focus {

            border-color: #e87518;

        }


        /* =====================================================
           TOTAL
        ===================================================== */

        .total-preview {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 12px;

        }


        .total-preview span {

            color: #888;

            font-size: 13px;

        }


        .total-preview strong {

            font-size: 20px;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .add-cart-button {

            width: 100%;

            border: 0;

            padding: 15px;

            border-radius: 13px;

            background: #e87518;

            color: white;

            font-weight: 800;

            font-size: 15px;

            cursor: pointer;

        }


        .add-cart-button:hover {

            background: #d76510;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (
            max-width: 800px
        ) {

            .product-detail {

                grid-template-columns:
                    1fr;

                gap: 0;

            }


            .product-image,
            .product-image img,
            .product-no-image {

                min-height: 280px;

                height: 280px;

            }


            .product-content {

                padding:
                    25px 20px 30px;

            }


            .product-content h1 {

                font-size: 28px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<header class="customer-navbar">


    <div
        class="customer-container navbar-inner"
    >


        <a
            href="index.php"
            class="customer-logo"
        >

            🍛

            <span>
                Niswah
            </span>

        </a>


        <a
            href="keranjang.php"
            class="cart-button"
        >

            🛒

            <span>
                Keranjang
            </span>

            <b id="cartCount">

                <?= $jumlahCart; ?>

            </b>

        </a>


    </div>


</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="detail-page">


    <div
        class="customer-container"
    >


        <a
            href="menu.php"
            class="detail-back"
        >

            ← Kembali ke Menu

        </a>



        <div
            class="product-detail"
        >


            <!-- =================================================
                 IMAGE
            ================================================== -->

            <div
                class="product-image"
            >


                <?php if (
                    !empty(
                        $menu['foto']
                    )
                ): ?>


                    <img
                        src="assets/uploads/menu/<?= htmlspecialchars(
                            $menu['foto']
                        ); ?>"
                        alt="<?= htmlspecialchars(
                            $menu['nama_menu']
                        ); ?>"
                    >


                <?php else: ?>


                    <div
                        class="product-no-image"
                    >

                        🍛

                    </div>


                <?php endif; ?>


            </div>



            <!-- =================================================
                 CONTENT
            ================================================== -->

            <div
                class="product-content"
            >


                <h1>

                    <?= htmlspecialchars(
                        $menu['nama_menu']
                    ); ?>

                </h1>


                <div
                    class="product-price"
                    id="displayHarga"
                >

                    Rp<?= number_format(
                        $hargaAwal,
                        0,
                        ',',
                        '.'
                    ); ?>

                </div>


                <p
                    class="product-description"
                >

                    <?= htmlspecialchars(
                        $menu['deskripsi']
                        ?? ''
                    ); ?>

                </p>



                <!-- =================================================
                     FORM
                ================================================== -->

                <form
                    action="tambah-keranjang.php"
                    method="POST"
                    id="orderForm"
                >


                    <input
                        type="hidden"
                        name="menu_id"
                        value="<?= (int)$menu['id']; ?>"
                    >


                    <!-- =================================================
                         VARIAN
                    ================================================== -->

                    <?php if (
                        $adaVarian
                    ): ?>


                        <div
                            class="option-section"
                            id="variantSection"
                        >


                            <div
                                class="option-title"
                            >

                                🍚 Pilih Penyajian

                                <span
                                    class="required-label"
                                >

                                    * Wajib

                                </span>

                            </div>


                            <div
                                class="option-help"
                            >

                                Pilih salah satu
                                jenis penyajian.

                            </div>


                            <div
                                class="option-list"
                            >


                                <?php foreach (
                                    $varianList
                                    as $index => $varian
                                ): ?>


                                    <label
                                        class="option-item"
                                    >


                                        <input
                                            type="radio"
                                            name="varian_id"
                                            value="<?= intval(
                                                $varian['id']
                                            ); ?>"
                                            data-variant-price="<?= floatval(
                                                $varian['harga']
                                            ); ?>"
                                            required
                                            <?= $index === 0
                                                ? 'checked'
                                                : ''; ?>
                                        >


                                        <span
                                            class="option-label"
                                        >

                                            <?= htmlspecialchars(
                                                $varian[
                                                    'nama_varian'
                                                ]
                                            ); ?>


                                            <span
                                                class="variant-price"
                                            >

                                                Rp<?= number_format(
                                                    $varian[
                                                        'harga'
                                                    ],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </span>

                                        </span>


                                    </label>


                                <?php endforeach; ?>


                            </div>


                        </div>


                    <?php endif; ?>



                    <!-- =================================================
                         PILIHAN DINAMIS LAMA
                    ================================================== -->

                    <?php foreach (
                        $kelompokPilihan
                        as $kelompok
                    ): ?>


                        <?php

                        $kelompokId =
                            intval(
                                $kelompok['id']
                            );

                        $tipe =
                            $kelompok[
                                'tipe_pilihan'
                            ];

                        $wajib =
                            intval(
                                $kelompok['wajib']
                            );

                        $minPilih =
                            intval(
                                $kelompok['min_pilih']
                            );

                        $maxPilih =
                            intval(
                                $kelompok['max_pilih']
                            );

                        $namaKelompok =
                            $kelompok[
                                'nama_kelompok'
                            ];

                        $daftarPilihan =
                            $kelompok[
                                'pilihan'
                            ];

                        ?>


                        <?php if (
                            count(
                                $daftarPilihan
                            ) > 0
                        ): ?>


                            <div
                                class="option-section"
                                data-group-id="<?= $kelompokId; ?>"
                                data-required="<?= $wajib; ?>"
                                data-min="<?= $minPilih; ?>"
                                data-max="<?= $maxPilih; ?>"
                                data-type="<?= htmlspecialchars(
                                    $tipe
                                ); ?>"
                            >


                                <div
                                    class="option-title"
                                >


                                    <?php if (
                                        $tipe === 'single'
                                    ): ?>

                                        🔘

                                    <?php elseif (
                                        $tipe === 'multiple'
                                    ): ?>

                                        ☑️

                                    <?php else: ?>

                                        ➕

                                    <?php endif; ?>


                                    <?= htmlspecialchars(
                                        $namaKelompok
                                    ); ?>


                                    <?php if (
                                        $wajib
                                    ): ?>

                                        <span
                                            class="required-label"
                                        >

                                            * Wajib

                                        </span>

                                    <?php endif; ?>


                                </div>



                                <?php if (
                                    $tipe === 'multiple'
                                    &&
                                    (
                                        $minPilih > 0
                                        ||
                                        $maxPilih > 0
                                    )
                                ): ?>


                                    <div
                                        class="option-help"
                                    >

                                        <?php if (
                                            $minPilih > 0
                                            &&
                                            $maxPilih > 0
                                        ): ?>

                                            Pilih
                                            <?= $minPilih; ?>
                                            -
                                            <?= $maxPilih; ?>
                                            pilihan

                                        <?php elseif (
                                            $minPilih > 0
                                        ): ?>

                                            Minimal
                                            <?= $minPilih; ?>
                                            pilihan

                                        <?php elseif (
                                            $maxPilih > 0
                                        ): ?>

                                            Maksimal
                                            <?= $maxPilih; ?>
                                            pilihan

                                        <?php endif; ?>

                                    </div>


                                <?php endif; ?>



                                <div
                                    class="option-list"
                                >


                                    <?php foreach (
                                        $daftarPilihan
                                        as $index => $item
                                    ): ?>


                                        <?php

                                        $pilihanId =
                                            intval(
                                                $item['id']
                                            );

                                        $hargaTambahan =
                                            floatval(
                                                $item[
                                                    'harga_tambahan'
                                                ]
                                            );

                                        ?>


                                        <label
                                            class="option-item"
                                        >


                                            <?php if (
                                                $tipe === 'multiple'
                                            ): ?>


                                                <input
                                                    type="checkbox"
                                                    name="pilihan[<?= $kelompokId; ?>][]"
                                                    value="<?= $pilihanId; ?>"
                                                    data-price="<?= $hargaTambahan; ?>"
                                                    data-group="<?= $kelompokId; ?>"
                                                >


                                            <?php else: ?>


                                                <input
                                                    type="radio"
                                                    name="pilihan[<?= $kelompokId; ?>]"
                                                    value="<?= $pilihanId; ?>"
                                                    data-price="<?= $hargaTambahan; ?>"
                                                    data-group="<?= $kelompokId; ?>"
                                                    <?= (
                                                        $wajib
                                                        &&
                                                        $index === 0
                                                    )
                                                        ? 'checked'
                                                        : ''; ?>
                                                >


                                            <?php endif; ?>


                                            <span
                                                class="option-label"
                                            >

                                                <?= htmlspecialchars(
                                                    $item[
                                                        'nama_pilihan'
                                                    ]
                                                ); ?>


                                                <?php if (
                                                    $hargaTambahan > 0
                                                ): ?>

                                                    <span
                                                        class="option-price"
                                                    >

                                                        +Rp<?= number_format(
                                                            $hargaTambahan,
                                                            0,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    </span>

                                                <?php endif; ?>


                                            </span>


                                        </label>


                                    <?php endforeach; ?>


                                </div>


                            </div>


                        <?php endif; ?>


                    <?php endforeach; ?>



                    <!-- =================================================
                         JUMLAH
                    ================================================== -->

                    <div
                        class="option-section"
                    >


                        <div
                            class="option-title"
                        >

                            Jumlah

                        </div>


                        <div
                            class="quantity-control"
                        >


                            <button
                                type="button"
                                id="minusBtn"
                            >

                                −

                            </button>


                            <input
                                type="number"
                                id="quantity"
                                name="jumlah"
                                value="1"
                                min="1"
                                readonly
                            >


                            <button
                                type="button"
                                id="plusBtn"
                            >

                                +

                            </button>


                        </div>


                    </div>



                    <!-- =================================================
                         CATATAN
                    ================================================== -->

                    <label
                        class="note-label"
                        for="catatan"
                    >

                        Catatan Pesanan

                    </label>


                    <textarea
                        id="catatan"
                        name="catatan"
                        class="note-input"
                        placeholder="Contoh: Sambalnya sedikit ya..."
                    ></textarea>



                    <!-- =================================================
                         TOTAL
                    ================================================== -->

                    <div
                        class="total-preview"
                    >

                        <span>
                            Total
                        </span>


                        <strong
                            id="totalHarga"
                        >

                            Rp<?= number_format(
                                $hargaAwal,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </strong>


                    </div>



                    <!-- =================================================
                         BUTTON
                    ================================================== -->

                    <button
                        type="submit"
                        class="add-cart-button"
                    >

                        🛒 Tambah ke Keranjang

                    </button>


                </form>


            </div>


        </div>


    </div>


</main>



<script>

// =====================================================
// DATA HARGA
// =====================================================

const hargaMenu =
    <?= floatval(
        $menu['harga']
    ); ?>;


const adaVarian =
    <?= $adaVarian ? 'true' : 'false'; ?>;


// =====================================================
// ELEMENT
// =====================================================

const quantityInput =
    document.getElementById(
        'quantity'
    );


const minusBtn =
    document.getElementById(
        'minusBtn'
    );


const plusBtn =
    document.getElementById(
        'plusBtn'
    );


const totalHarga =
    document.getElementById(
        'totalHarga'
    );


const displayHarga =
    document.getElementById(
        'displayHarga'
    );


const orderForm =
    document.getElementById(
        'orderForm'
    );


// =====================================================
// FORMAT RUPIAH
// =====================================================

function formatRupiah(
    angka
) {

    return 'Rp' +
        new Intl.NumberFormat(
            'id-ID'
        ).format(
            angka
        );

}


// =====================================================
// AMBIL HARGA VARIAN
// =====================================================

function getHargaDasar() {

    if (!adaVarian) {

        return hargaMenu;

    }


    const varianTerpilih =
        document.querySelector(
            'input[name="varian_id"]:checked'
        );


    if (!varianTerpilih) {

        return hargaMenu;

    }


    return parseFloat(
        varianTerpilih.dataset.variantPrice
    ) || hargaMenu;

}


// =====================================================
// HITUNG TOTAL
// =====================================================

function hitungTotal() {


    let hargaDasar =
        getHargaDasar();


    let totalPilihan = 0;


    document
        .querySelectorAll(
            'input[data-price]:checked'
        )
        .forEach(
            function(input) {

                totalPilihan +=
                    parseFloat(
                        input.dataset.price
                    ) || 0;

            }
        );


    let jumlah =
        parseInt(
            quantityInput.value
        ) || 1;


    if (
        jumlah < 1
    ) {

        jumlah = 1;

        quantityInput.value = 1;

    }


    const hargaSatuan =
        hargaDasar +
        totalPilihan;


    const total =
        hargaSatuan *
        jumlah;


    displayHarga.textContent =
        formatRupiah(
            hargaDasar
        );


    totalHarga.textContent =
        formatRupiah(
            total
        );

}


// =====================================================
// VARIAN BERUBAH
// =====================================================

document
    .querySelectorAll(
        'input[name="varian_id"]'
    )
    .forEach(
        function(input) {

            input.addEventListener(
                'change',
                function() {

                    hitungTotal();

                }
            );

        }
    );


// =====================================================
// MINUS
// =====================================================

minusBtn.addEventListener(
    'click',
    function() {

        let jumlah =
            parseInt(
                quantityInput.value
            ) || 1;


        if (
            jumlah > 1
        ) {

            jumlah--;

            quantityInput.value =
                jumlah;

            hitungTotal();

        }

    }
);


// =====================================================
// PLUS
// =====================================================

plusBtn.addEventListener(
    'click',
    function() {

        let jumlah =
            parseInt(
                quantityInput.value
            ) || 1;


        jumlah++;

        quantityInput.value =
            jumlah;

        hitungTotal();

    }
);


// =====================================================
// PILIHAN BERUBAH
// =====================================================

document
    .querySelectorAll(
        'input[data-price]'
    )
    .forEach(
        function(input) {

            input.addEventListener(
                'change',
                function() {

                    const groupId =
                        this.dataset.group;


                    const group =
                        document.querySelector(
                            '.option-section[data-group-id="' +
                            groupId +
                            '"]'
                        );


                    if (!group) {

                        hitungTotal();

                        return;

                    }


                    const max =
                        parseInt(
                            group.dataset.max
                        ) || 0;


                    if (
                        this.type === 'checkbox'
                        &&
                        max > 0
                    ) {


                        const checked =
                            group.querySelectorAll(
                                'input[type="checkbox"]:checked'
                            );


                        if (
                            checked.length > max
                        ) {

                            this.checked =
                                false;


                            alert(
                                'Maksimal ' +
                                max +
                                ' pilihan untuk kelompok ini.'
                            );

                        }

                    }


                    hitungTotal();

                }
            );

        }
    );


// =====================================================
// VALIDASI SUBMIT
// =====================================================

orderForm.addEventListener(
    'submit',
    function(e) {


        // =============================================
        // VALIDASI VARIAN
        // =============================================

        if (adaVarian) {


            const varian =
                document.querySelector(
                    'input[name="varian_id"]:checked'
                );


            if (!varian) {

                e.preventDefault();


                alert(
                    'Silakan pilih penyajian terlebih dahulu.'
                );


                const section =
                    document.getElementById(
                        'variantSection'
                    );


                if (section) {

                    section.scrollIntoView({

                        behavior:
                            'smooth',

                        block:
                            'center'

                    });

                }


                return;

            }

        }


        // =============================================
        // VALIDASI KELOMPOK
        // =============================================

        const groups =
            document.querySelectorAll(
                '.option-section[data-group-id]'
            );


        for (
            const group
            of groups
        ) {


            const required =
                parseInt(
                    group.dataset.required
                ) || 0;


            const min =
                parseInt(
                    group.dataset.min
                ) || 0;


            const max =
                parseInt(
                    group.dataset.max
                ) || 0;


            const checked =
                group.querySelectorAll(
                    'input[data-group]:checked'
                );


            const jumlahDipilih =
                checked.length;


            // =========================================
            // WAJIB
            // =========================================

            if (
                required === 1
                &&
                jumlahDipilih === 0
            ) {

                e.preventDefault();


                const title =
                    group.querySelector(
                        '.option-title'
                    );


                const nama =
                    title
                        ? title.innerText
                        : 'pilihan';


                alert(
                    'Silakan pilih ' +
                    nama +
                    ' terlebih dahulu.'
                );


                group.scrollIntoView({

                    behavior:
                        'smooth',

                    block:
                        'center'

                });


                return;

            }


            // =========================================
            // MINIMUM
            // =========================================

            if (
                min > 0
                &&
                jumlahDipilih < min
            ) {

                e.preventDefault();


                const title =
                    group.querySelector(
                        '.option-title'
                    );


                const nama =
                    title
                        ? title.innerText
                        : 'pilihan';


                alert(
                    'Untuk ' +
                    nama +
                    ', minimal pilih ' +
                    min +
                    ' pilihan.'
                );


                group.scrollIntoView({

                    behavior:
                        'smooth',

                    block:
                        'center'

                });


                return;

            }


            // =========================================
            // MAKSIMUM
            // =========================================

            if (
                max > 0
                &&
                jumlahDipilih > max
            ) {

                e.preventDefault();


                const title =
                    group.querySelector(
                        '.option-title'
                    );


                const nama =
                    title
                        ? title.innerText
                        : 'pilihan';


                alert(
                    'Untuk ' +
                    nama +
                    ', maksimal pilih ' +
                    max +
                    ' pilihan.'
                );


                group.scrollIntoView({

                    behavior:
                        'smooth',

                    block:
                        'center'

                });


                return;

            }

        }

    }
);


// =====================================================
// HITUNG SAAT AWAL
// =====================================================

hitungTotal();

</script>


</body>

</html>