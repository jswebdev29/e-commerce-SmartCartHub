<?php
require_once __DIR__ . '/connectfinity.php';

$id = intval($_GET['id']);
$category = $_GET['category'] ?? 'boys';

/* Section target */
$backSection = $_GET['section'] ?? 'boys_clothes';

/* Default values */
$table = 'boys_clothes';
$sectionTitle = 'Boys Clothes';

/* Correct table + heading according to section */
if ($category === 'girls') {

    if ($backSection === 'girls_footwear') {

        $table = 'girls_footwear';
        $sectionTitle = 'Girls Footwear';

    } elseif (
        $backSection === 'girls_fashion' ||
        $backSection === 'girls_fashion_product'
    ) {

        $table = 'girls_fashion_product';
        $sectionTitle = 'Girls Fashion Products';
    } else {

        $table = 'girls_clothes';
        $sectionTitle = 'Girls Clothes';
    }

} else {

    if ($backSection === 'boys_shoes') {

        $table = 'boys_shoes';
        $sectionTitle = 'Boys Shoes';

    } elseif (
        $backSection === 'boys_fashion' ||
        $backSection === 'boys_fashion_product'
    ) {

        $table = 'boys_fashion_product';
        $sectionTitle = 'Boys Fashion Products';
    } else {

        $table = 'boys_clothes';
        $sectionTitle = 'Boys Clothes';
    }
}

/* SECURITY CHECK */
if (!$id) {
    die("Invalid Product ID");
}

$allowed_tables = [
    'boys_clothes',
    'boys_shoes',
    'boys_fashion_product',
    'girls_clothes',
    'girls_footwear',
    'girls_fashion_product'
];

if (!in_array($table, $allowed_tables, true)) {
    die("Invalid Table");
}

/* Fetch product */
$product = $conn->query(
    "SELECT * FROM $table WHERE id = $id"
)->fetch_assoc();

if (!$product) {

    echo "<h3>Product not found!</h3>";
    exit;
}

/* ===============================
   STOCK STATUS
================================= */

$isOutOfStock = false;

/*
Assuming database column:
status = 'active' OR 'inactive'
*/

if (
    isset($product['status']) &&
    strtolower($product['status']) == 'inactive'
) {
    $isOutOfStock = true;
}

/* Charges */
$delivery_charge = 49;
$gateway_charge = 10;
?>

<!DOCTYPE html>
<html>

<head>

    <title>
        <?php echo htmlspecialchars($product['name']); ?> | SmartCart
    </title>

    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #dfefff, #f8fbff);
            min-height: 100vh;
        }

        .product-box {
            background: #fff;
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-top: 40px;
            position: relative;
        }

        .section-heading {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .product-box img {
            object-fit: cover;
            border-radius: 20px;
        }

        #quantity {
            border-radius: 12px;
            text-align: center;
            font-weight: bold;
            border: 2px solid #007bff;
        }

        .size-box label {
            margin: 5px;
            padding: 8px 15px;
            border: 2px solid #007bff;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .size-box input[type="radio"] {
            display: none;
        }

        .size-box input[type="radio"]:checked+label {
            background: #007bff;
            color: white;
        }

        .buy-btn {
            border-radius: 30px;
            font-size: 18px;
            padding: 12px;
        }

        /* ===========================
           OUT OF STOCK
        ============================ */

        .stock-out {
            position: absolute;
            top: 20px;
            right: 20px;
            background: red;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 18px;
            z-index: 10;
        }

        .disabled-product {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>

</head>

<body class="p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 product-box text-center">

                <!-- STOCK OUT LABEL -->
                <?php if ($isOutOfStock) { ?>

                    <div class="stock-out">
                        STOCK OUT
                    </div>

                <?php } ?>

                <!-- SECTION -->
                <h2 class="section-heading">
                    <?php echo $sectionTitle; ?>
                </h2>

                <!-- IMAGE -->
                <img src="admin/<?php echo htmlspecialchars($product['image_path']); ?>" width="300" height="300"
                    class="mb-3 border <?php echo $isOutOfStock ? 'disabled-product' : ''; ?>">

                <!-- NAME -->
                <h2>
                    <?php echo htmlspecialchars($product['name']); ?>
                </h2>

                <!-- PRICE -->
                <h4 class="text-success">
                    Product Price :
                    ₹<span id="price">
                        <?php echo number_format($product['price'], 2); ?>
                    </span>
                </h4>

                <!-- STOCK MESSAGE -->
                <?php if ($isOutOfStock) { ?>
                    <div class="alert alert-danger mt-3 fw-bold">
                        ❌ This product is currently unavailable
                    </div>
                <?php } ?>

                <!-- QUANTITY -->
                <div class="mb-4 mt-4">
                    <label class="fw-bold">
                        Quantity:
                    </label>
                    <input type="number" id="quantity" value="1" min="1" class="form-control w-25 mx-auto" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                </div>

                <!-- SIZE -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">
                        Select Size
                    </h5>
                    <div class="size-box">
                        <?php
                        if (
                            $table == 'boys_shoes' ||
                            $table == 'girls_footwear'
                        ) {
                            ?>
                            <input type="radio" id="s6" name="size" value="6" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="s6">6</label>
                            <input type="radio" id="s7" name="size" value="7" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="s7">7</label>
                            <input type="radio" id="s8" name="size" value="8" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="s8">8</label>
                            <input type="radio" id="s9" name="size" value="9" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="s9">9</label>
                            <input type="radio" id="s10" name="size" value="10" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="s10">10</label>
                        <?php } else { ?>

                            <input type="radio" id="m" name="size" value="M" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="m">M</label>
                            <input type="radio" id="l" name="size" value="L" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="l">L</label>
                            <input type="radio" id="xl" name="size" value="XL" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="xl">XL</label>
                            <input type="radio" id="xxl" name="size" value="XXL" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="xxl">XXL</label>
                        <?php } ?>

                    </div>
                </div>

                <!-- PAYMENT METHODS -->
                <div class="row mt-4">
                    <!-- CASH ON DELIVERY -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-success shadow-sm h-100">
                            <div class="card-body text-center">
                                <h4 class="text-success fw-bold">
                                    Cash On Delivery
                                </h4>
                                <p class="mb-2">
                                    Delivery Charge :
                                    ₹<?php echo number_format($delivery_charge, 2); ?>
                                </p>
                                <h5 class="text-danger fw-bold">
                                    Total :
                                    ₹<span id="codTotal">
                                        <?php
                                        echo number_format(
                                            $product['price'] + $delivery_charge,
                                            2
                                        );
                                        ?>
                                    </span>
                                </h5>
                            </div>

                        </div>
                    </div>

                    <!-- ONLINE PAYMENT -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-primary shadow-sm h-100">
                            <div class="card-body text-center">
                                <h4 class="text-primary fw-bold">
                                    Online Payment
                                </h4>
                                <p class="mb-2">
                                    Gateway Charge :
                                    ₹<?php echo number_format($gateway_charge, 2); ?>
                                </p>
                                <h5 class="text-danger fw-bold">
                                    Total :
                                    ₹<span id="onlineTotal">
                                        <?php
                                        echo number_format(
                                            $product['price'] + $gateway_charge,
                                            2
                                        );
                                        ?>
                                    </span>
                                </h5>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="mt-4">
                    <?php if ($isOutOfStock) { ?>
                        <button class="btn btn-danger buy-btn w-100 mb-3" onclick="showUnavailableMessage()">
                            Product Not Available
                        </button>
                    <?php } else { ?>
                        <a href="checkout.php?id=<?php echo $id; ?>&table=<?php echo $table; ?>"
                            class="btn btn-success buy-btn w-100 mb-3">
                            Buy Now
                        </a>

                    <?php } ?>

                    <button onclick="history.back()" class="btn btn-secondary">
                        ← Back</button>

                </div>
            </div>
        </div>
    </div>

    <script>

        const price =
            <?php echo $product['price']; ?>;
        const delivery =
            <?php echo $delivery_charge; ?>;
        const gateway =
            <?php echo $gateway_charge; ?>;
        const quantityInput =
            document.getElementById('quantity');
        const codTotal =
            document.getElementById('codTotal');
        const onlineTotal =
            document.getElementById('onlineTotal');

        function updateTotals() {
            let qty =
                parseInt(quantityInput.value) || 1;
            if (qty < 1) {
                qty = 1;
                quantityInput.value = 1;
            }

            let cod =
                (price * qty) + delivery;
            let online =
                (price * qty) + gateway;

            codTotal.textContent =
                cod.toFixed(2);
            onlineTotal.textContent =
                online.toFixed(2);
        }

        quantityInput.addEventListener(
            'input',
            updateTotals
        );

        // ==========================
        // PRODUCT NOT AVAILABLE
        // ==========================

        function showUnavailableMessage() {
            alert("❌ Product is currently out of stock!");
        }

    </script>

</body>

</html>