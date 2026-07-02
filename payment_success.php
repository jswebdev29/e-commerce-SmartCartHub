<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit;
}

if (
    !isset($_GET['payment_id']) ||
    empty($_GET['payment_id'])
) {
    die("Payment Failed");
}

$payment_id = $_GET['payment_id'];
$check_payment = $conn->query("
    SELECT id FROM buy_product
    WHERE transaction_id='$payment_id'
");

if ($check_payment->num_rows > 0) {
    die("Payment already processed");
}

if (
    !isset($_SESSION['checkout_product_id']) ||
    !isset($_SESSION['checkout_table'])
) {
    die("Session expired");
}

$id = intval($_SESSION['checkout_product_id']);
$table = mysqli_real_escape_string(
    $conn,
    $_SESSION['checkout_table']
);

$id = $_SESSION['checkout_product_id'];
$table = $_SESSION['checkout_table'];

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

$product = $conn->query("SELECT * FROM $table WHERE id='$id'");
$row = $product->fetch_assoc();
if (!$row) {
    die("Product not found");
}

$email = $_SESSION['customer_email'];

$cust = $conn->query("SELECT * FROM customers WHERE email='$email'");
$cust_data = $cust->fetch_assoc();
if (!$cust_data) {
    die("Customer not found");
}

$customer_name = $cust_data['name'];
$phone = $cust_data['phone'];
$address = $cust_data['address'];
$location = $cust_data['location'];

$size = mysqli_real_escape_string($conn, $_GET['size']);
if (empty($size)) {
    die("Size not selected");
}

$gateway_charge = 10;

$final_price =
    $row['price'] +
    $gateway_charge;

$sql = "INSERT INTO buy_product
(
    customer_email,
    customer_name,
    phone,
    address,
    location,
    product_id,
    category,
    size,
    payment_method,
    payment_status,
    transaction_id,
    price,
    quantity
)
VALUES
(
    '$email',
    '$customer_name',
    '$phone',
    '$address',
    '$location',
    '$id',
    '$table',
    '$size',
    'Online Payment',
    'Paid',
    '$payment_id',
    '$final_price',
    1
)";

if ($conn->query($sql)) {

    unset($_SESSION['checkout_product_id']);
    unset($_SESSION['checkout_table']);
    unset($_SESSION['checkout_price']);

    echo "
    <script>
        alert('✅ Payment Successful & Order Placed');
        window.location='index.php';
    </script>
    ";
}
?>