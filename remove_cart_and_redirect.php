<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit;
}

$email = $_SESSION['customer_email'];

$product_id = intval($_GET['id']);
$table = mysqli_real_escape_string($conn, $_GET['table']);

// delete from cart
$conn->query("
    DELETE FROM add_to_cart 
    WHERE customer_email='$email'
    AND product_id='$product_id'
    AND product_table='$table'
");

// redirect to checkout
header("Location: checkout.php?id=$product_id&table=$table");
exit;