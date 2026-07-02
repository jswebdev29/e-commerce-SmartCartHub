<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit;
}

$id = intval($_GET['id']);
$table = mysqli_real_escape_string($conn, $_GET['table']);

$allowed_tables = [
    'boys_clothes',
    'boys_shoes',
    'boys_fashion_product',
    'girls_clothes',
    'girls_footwear',
    'girls_fashion_product'
];

if (!in_array($table, $allowed_tables)) {
    die("Invalid table");
}

$product = $conn->query("SELECT * FROM $table WHERE id='$id' LIMIT 1");

// ❌ BLOCK if not found OR invalid
if (!$product || $product->num_rows == 0) {
    echo "<script>
        alert('⚠️ Product deleted or not available. Order blocked.');
        window.location = 'index.php';
    </script>";
    exit;
}

$row = $product->fetch_assoc();

if (isset($_POST['place_order']) && $_POST['payment'] == "Cash on Delivery") {

    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        $quantity = 1;
    }

    // 🔥 ADD THIS HERE (FIRST THING INSIDE BLOCK)
    $checkProduct = $conn->query("SELECT id FROM $table WHERE id='$id' LIMIT 1");

    if (!$checkProduct || $checkProduct->num_rows == 0) {
        echo "<script>
            alert('⚠️ Product no longer exists. Order cancelled.');
            window.location='index.php';
        </script>";
        exit;
    }

    $size = $_POST['size'];
    $payment = $_POST['payment'];


    $delivery_charge = 0;

    if ($payment == "Cash on Delivery") {
        $delivery_charge = 49;
    }

    $final_price =
        ($row['price'] * $quantity) +
        $delivery_charge;

    $payment_status = "Pending";


    if ($payment == "Online Payment") {
        $payment_status = "Paid";
    }

    $email = $_SESSION['customer_email'];

    $cust = $conn->query("SELECT * FROM customers WHERE email='$email'");
    $cust_data = $cust->fetch_assoc();

    $customer_name = $cust_data['name'];
    $phone = $cust_data['phone'];
    $address = $cust_data['address'];

    $sql = "INSERT INTO buy_product
(
    customer_email,
    customer_name,
    phone,
    address,
    product_id,
    category,
    size,
    payment_method,
    payment_status,
    price,
    quantity
)
VALUES
(
    '$email',
    '$customer_name',
    '$phone',
    '$address',
    '$id',
    '$table',
    '$size',
    '$payment',
    '$payment_status',
    '$final_price',
    '$quantity'
)";

    if ($conn->query($sql)) {

        echo "<script>
    alert('✅ Cash on Delivery Order Placed Successfully');
    window.location='index.php';
</script>";

        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Checkout</title>

    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body {
            background: #f2f2f2;
        }

        .box {
            width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        #upiBox {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        #upiBox img {
            width: 250px;
            border-radius: 10px;
            border: 2px solid #ccc;
        }
    </style>
</head>

<body>

    <div class="box">

        <h2 class="text-center mb-4">Checkout</h2>

        <img src="admin/<?php echo $row['image_path']; ?>">

        <h3 class="mt-3">
            <?php echo $row['name']; ?>
        </h3>

        <h4 class="text-success">
            ₹<span id="productPrice">
                <?php echo $row['price']; ?>
            </span>
        </h4>

        <?php
        $delivery_charge_online = 0;
        $delivery_charge_cod = 49;
        $gateway_charge = 10;

        $quantity = 1;

        $online_total =
            ($row['price'] * $quantity) +
            $delivery_charge_online +
            $gateway_charge;

        $cod_total =
            ($row['price'] * $quantity) +
            $delivery_charge_cod;

        $_SESSION['checkout_product_id'] = $id;
        $_SESSION['checkout_table'] = $table;

        ?>
        <form method="post">

            <div class="mt-3">
                <label class="fw-bold">
                    Quantity
                </label>

                <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control">
            </div>

            <h5 class="mt-4">Select Size</h5>

            <?php if (
                $table == 'boys_shoes' ||
                $table == 'girls_footwear'
            ) { ?>

                <input type="radio" name="size" value="6" required> 6
                <input type="radio" name="size" value="7"> 7
                <input type="radio" name="size" value="8"> 8
                <input type="radio" name="size" value="9"> 9
                <input type="radio" name="size" value="10"> 10

            <?php } else { ?>

                <input type="radio" name="size" value="M" required> M
                <input type="radio" name="size" value="L"> L
                <input type="radio" name="size" value="XL"> XL
                <input type="radio" name="size" value="XXL"> XXL

            <?php } ?>

            <h5 class="mt-4">Payment Method</h5>
            <div class="border p-3 rounded mb-3">
                <input type="radio" name="payment" value="Online Payment" required>
                <strong>Online Payment</strong>
                <br>
                Product Price :
                ₹
                <?php echo $row['price']; ?>
                <br>

                Delivery Charge :
                ₹0

                <br>

                Gateway Charge :
                ₹
                <?php echo $gateway_charge; ?>
                <br>

                <span class="text-success fw-bold">
                    Total Payment :
                    ₹
                    <span class="onlineTotal">
                        <?php echo $online_total; ?>
                    </span>
                </span>
            </div>

            <div class="border p-3 rounded">
                <input type="radio" name="payment" value="Cash on Delivery">
                <strong>Cash on Delivery</strong>
                <br>
                Product Price :
                ₹
                <?php echo $row['price']; ?>
                <br>

                Delivery Charge :
                ₹49
                <br>
                <span class="text-danger fw-bold">
                    Total Payment :
                    ₹
                    <span class="codTotal">
                        <?php echo $cod_total; ?>
                    </span>
                </span>
            </div>
            <br>
            <button type="button" id="rzp-button1" class="btn btn-success w-100 mb-2">
                Pay Online & Place Order
            </button>
            <button type="submit" name="place_order" class="btn btn-primary w-100">
                Cash on Delivery Order
            </button>
        </form>

    </div>

    <script>
        const quantityInput =
            document.getElementById('quantity');

        const productPrice =
            <?php echo $row['price']; ?>;

        const gatewayCharge =
            <?php echo $gateway_charge; ?>;

        const codCharge = 49;

        function updateTotals() {

            let qty =
                parseInt(quantityInput.value) || 1;

            if (qty < 1) {

                qty = 1;
                quantityInput.value = 1;
            }

            let onlineTotal =
                (productPrice * qty) + gatewayCharge;

            let codTotal =
                (productPrice * qty) + codCharge;

            document.querySelectorAll('.onlineTotal')
                .forEach(el => {
                    el.innerText = onlineTotal;
                });

            document.querySelectorAll('.codTotal')
                .forEach(el => {
                    el.innerText = codTotal;
                });
        }

        quantityInput.addEventListener(
            'input',
            updateTotals
        );

        var options = {

            "key": "rzp_test_SrVQDcjWz5g5fv",
            "amount": "<?php echo $online_total * 100; ?>",
            "currency": "INR",
            "name": "SmartCartHub",
            "description": "Product Payment",

            "handler": function (response) {

                const size =
                    document.querySelector('input[name="size"]:checked').value;

                const qty =
                    document.getElementById('quantity').value;

                window.location.href =
                    "payment_success.php?" +
                    "payment_id=" + encodeURIComponent(response.razorpay_payment_id) +
                    "&size=" + encodeURIComponent(size) +
                    "&quantity=" + encodeURIComponent(qty);
            },
            "theme": {
                "color": "#198754"
            }
        };

        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button1').onclick = function (e) {

            e.preventDefault();

            const onlineRadio =
                document.querySelector('input[value="Online Payment"]');

            if (!onlineRadio.checked) {

                alert("Please select Online Payment");
                return;
            }

            const selectedSize =
                document.querySelector('input[name="size"]:checked');

            if (!selectedSize) {

                alert("Please select size");
                return;
            }

            let qty =
                parseInt(quantityInput.value) || 1;

            let finalAmount =
                ((productPrice * qty) + gatewayCharge) * 100;

            options.amount = finalAmount;

            rzp1 = new Razorpay(options);

            rzp1.open();
        }
    </script>
</body>

</html>