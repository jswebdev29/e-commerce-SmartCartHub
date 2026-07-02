<?php
session_start();

require_once __DIR__ . '/connectfinity.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";
$search_safe = $conn->real_escape_string($searchTerm);
$scrollToProduct = !empty($searchTerm) ? 'true' : 'false';


// =========================
// LOAD MORE LIMIT
// =========================
$boys_clothes_limit = isset($_GET['boys_clothes_show'])
    ? (int) $_GET['boys_clothes_show']
    : 12;

$boys_shoes_limit = isset($_GET['boys_shoes_show'])
    ? (int) $_GET['boys_shoes_show']
    : 12;

$boys_fashion_product_limit = isset($_GET['boys_fashion_product_show'])
    ? (int) $_GET['boys_fashion_product_show']
    : 12;

$girls_clothes_limit = isset($_GET['girls_clothes_show'])
    ? (int) $_GET['girls_clothes_show']
    : 12;

$girls_footwear_limit = isset($_GET['girls_footwear_show'])
    ? (int) $_GET['girls_footwear_show']
    : 12;

$girls_fashion_product_limit = isset($_GET['girls_fashion_product_show'])
    ? (int) $_GET['girls_fashion_product_show']
    : 12;


// =========================
// Fetch boys products
// =========================
// BOYS CLOTHES
$sql_boys_clothes = "SELECT * FROM boys_clothes WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_boys_clothes .= " AND name LIKE '%$search_safe%' ";
}

$sql_boys_clothes .= "ORDER BY id DESC LIMIT $boys_clothes_limit";
$result_boys_clothes = $conn->query($sql_boys_clothes);

$count_boys_clothes = $conn->query(" SELECT COUNT(*) AS total FROM boys_clothes");
$totalRows_boys_clothes = $count_boys_clothes->fetch_assoc()['total'];

// boys shoes
$sql_boys_shoes = "SELECT * FROM boys_shoes WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_boys_shoes .= " AND name LIKE '%$search_safe%' ";
}

$sql_boys_shoes .= "ORDER BY id DESC LIMIT $boys_shoes_limit";
$result_boys_shoes = $conn->query($sql_boys_shoes);

$count_boys_shoes = $conn->query(" SELECT COUNT(*) AS total FROM boys_shoes");
$totalRows_boys_shoes = $count_boys_shoes->fetch_assoc()['total'];

// boys fashion product 
$sql_boys_fashion_product = "SELECT * FROM boys_fashion_product WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_boys_fashion_product .= " AND name LIKE '%$search_safe%' ";
}

$sql_boys_fashion_product .= "ORDER BY id DESC LIMIT $boys_fashion_product_limit";
$result_boys_fashion_product = $conn->query($sql_boys_fashion_product);

$count_boys_fashion_product = $conn->query(" SELECT COUNT(*) AS total FROM boys_fashion_product");
$totalRows_boys_fashion_product = $count_boys_fashion_product->fetch_assoc()['total'];

// Fetch girls products
// girls clothes 
$sql_girls_clothes = "SELECT * FROM girls_clothes WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_girls_clothes .= " AND name LIKE '%$search_safe%' ";
}

$sql_girls_clothes .= "ORDER BY id DESC LIMIT $girls_clothes_limit";
$result_girls_clothes = $conn->query($sql_girls_clothes);

$count_girls_clothes = $conn->query(" SELECT COUNT(*) AS total FROM girls_clothes");
$totalRows_girls_clothes = $count_girls_clothes->fetch_assoc()['total'];

// girls footwear 
$sql_girls_footwear = "SELECT * FROM girls_footwear WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_girls_footwear .= " AND name LIKE '%$search_safe%' ";
}

$sql_girls_footwear .= "ORDER BY id DESC LIMIT $girls_footwear_limit";
$result_girls_footwear = $conn->query($sql_girls_footwear);

$count_girls_footwear = $conn->query(" SELECT COUNT(*) AS total FROM girls_footwear");
$totalRows_girls_footwear = $count_girls_footwear->fetch_assoc()['total'];

// girls fashion product
$sql_girls_fashion_product = "SELECT * FROM girls_fashion_product WHERE 1 ";
if (!empty($searchTerm)) {
    $sql_girls_fashion_product .= " AND name LIKE '%$search_safe%' ";
}

$sql_girls_fashion_product .= "ORDER BY id DESC LIMIT $girls_fashion_product_limit";
$result_girls_fashion_product = $conn->query($sql_girls_fashion_product);

$count_girls_fashion_product = $conn->query(" SELECT COUNT(*) AS total FROM girls_fashion_product");
$totalRows_girls_fashion_product = $count_girls_fashion_product->fetch_assoc()['total'];


// ✅ ADD THIS CODE HERE
$scrollTarget = "availstockboy";

// Boys search result
if (
    ($result_boys_clothes && $result_boys_clothes->num_rows > 0) ||
    ($result_boys_shoes && $result_boys_shoes->num_rows > 0) ||
    ($result_boys_fashion_product && $result_boys_fashion_product->num_rows > 0)
) {
    $scrollTarget = "boys_clothes";
}

// Girls search result
if (
    ($result_girls_clothes && $result_girls_clothes->num_rows > 0) ||
    ($result_girls_footwear && $result_girls_footwear->num_rows > 0) ||
    ($result_girls_fashion_product && $result_girls_fashion_product->num_rows > 0)
) {
    $scrollTarget = "girls_clothes";
}


// Total products
$totalProducts = 0;
$cart_count = 0;

if (isset($_SESSION['customer_email'])) {
    $email = $_SESSION['customer_email'];

    // Total Orders (Buy Products)
    $order_query = $conn->query("
        SELECT COUNT(*) AS total
        FROM buy_product
        WHERE customer_email='$email'
    ");

    $order_data = $order_query->fetch_assoc();
    $totalProducts = (int) ($order_data['total'] ?? 0);

    // Total Cart Quantity
    $cart_query = $conn->query("
        SELECT SUM(quantity) AS total
        FROM add_to_cart
        WHERE customer_email='$email'
    ");

    $cart_data = $cart_query->fetch_assoc();
    $cart_count = (int) ($cart_data['total'] ?? 0);
}


$cart_count = 0;

if (isset($_SESSION['customer_email'])) {
    $email = $_SESSION['customer_email'];

    $cart_query = $conn->query("
        SELECT COUNT(*) AS total
        FROM add_to_cart
        WHERE customer_email='$email'
    ");

    $cart_data = $cart_query->fetch_assoc();
    $cart_count = (int) ($cart_data['total'] ?? 0);
}

// =========================================
// ADD TO CART
// =========================================

if (isset($_POST['add_to_cart'])) {

    if (!isset($_SESSION['customer_email'])) {

        echo "<script>
            alert('⚠️ Please login first!');
            window.location.href='customer_login.php';
        </script>";

        exit();
    }

    $email = $_SESSION['customer_email'];

    // Customer Details
    $cust = $conn->query("SELECT * FROM customers WHERE email='$email' LIMIT 1");

    $cust_data = $cust->fetch_assoc();

    $customer_name = $cust_data['name'] ?? '';
    $phone = $cust_data['phone'] ?? '';
    $address = $cust_data['address'] ?? '';
    $location = $cust_data['location'] ?? '';

    $product_id = intval($_POST['product_id'] ?? 0);
    $category = $_POST['category'] ?? '';
    $table = trim($_POST['product_table'] ?? '');

    $allowed_tables = [
        'boys_clothes',
        'boys_shoes',
        'boys_fashion_product',
        'girls_clothes',
        'girls_footwear',
        'girls_fashion_product'
    ];

    if (empty($table)) {
        die("product_table not received");
    }

    if (!in_array($table, $allowed_tables, true)) {
        die("Invalid table: " . htmlspecialchars($table));
    }

    // ===================================
    // Detect Table
    // ===================================

    // $table = $_POST['product_table'];


    // ===================================
    // Product Fetch
    // ===================================

    $product = $conn->query("SELECT * FROM $table WHERE id='$product_id' LIMIT 1");

    if ($product && $product->num_rows > 0) {

        $prod = $product->fetch_assoc();

        $product_name = $prod['name'];
        $product_image = $prod['image_path'];
        $price = $prod['price'];

    } else {

        echo "<script>alert('❌ Product not found');</script>";
        exit();
    }

    // ===================================
    // Already Exists?
    // ===================================

    $already = $conn->query("
        SELECT * FROM add_to_cart
        WHERE customer_email='$email'
        AND product_id='$product_id'
        AND product_table='$table'
    ");

    if ($already->num_rows > 0) {

        $row = $already->fetch_assoc();

        $new_qty = $row['quantity'] + 1;

        $new_total = $price * $new_qty;

        $conn->query("
            UPDATE add_to_cart
            SET quantity='$new_qty',
                total_price='$new_total'
            WHERE id='{$row['id']}'
        ");

    } else {

        $total_price = $price;

        $conn->query("
            INSERT INTO add_to_cart (
                customer_email,
                customer_name,
                phone,
                address,
                location,

                product_id,
                product_name,
                product_image,

                product_table,
                category,

                price,
                quantity,
                total_price
            )

            VALUES (

                '$email',
                '$customer_name',
                '$phone',
                '$address',
                '$location',

                '$product_id',
                '$product_name',
                '$product_image',

                '$table',
                '$category',

                '$price',
                '1',
                '$total_price'
            )
        ");
    }

    $_SESSION['success_message'] = "✅ Product added to cart!";

    $return_url = $_POST['return_url'] ?? 'index.php';

    header("Location: " . $return_url);
    exit();
}

if (isset($_POST['buy_to_product'])) {
    if (!isset($_SESSION['customer_email'])) {
        echo "<script>alert('⚠️ Please login first to add items to buy product.'); window.location.href = 'customer_login.php';</script>";
        exit;
    }

    $email = $_SESSION['customer_email'];

    // Get customer details
    $cust = $conn->query("SELECT * FROM customers WHERE email='$email' LIMIT 1");
    $cust_data = $cust->fetch_assoc();

    $customer_name = $cust_data['name'];
    $phone = $cust_data['phone'];
    $address = $cust_data['address'];
    $location = $cust_data['location'];

    $product_id = $_POST['product_id'];
    $category = $_POST['category'];

    // Fetch price from the right table
    $product_id = $_POST['product_id'];
    $category = $_POST['category'];

    // Detect correct table
    $table = "";
    if ($category == "boys") {
        $check1 = $conn->query("SELECT price FROM boys_clothes WHERE id='$product_id'");
        if ($check1->num_rows > 0) {
            $table = "boys_clothes";
        }
        $check2 = $conn->query("SELECT price FROM boys_shoes WHERE id='$product_id'");
        if ($check2->num_rows > 0) {
            $table = "boys_shoes";
        }
        $check3 = $conn->query("SELECT price FROM boys_fashion_product WHERE id='$product_id'");
        if ($check3->num_rows > 0) {
            $table = "boys_fashion_product";
        }

    } else {
        $check1 = $conn->query("SELECT price FROM girls_clothes WHERE id='$product_id'");
        if ($check1->num_rows > 0) {
            $table = "girls_clothes";
        }
        $check2 = $conn->query("SELECT price FROM girls_footwear WHERE id='$product_id'");
        if ($check2->num_rows > 0) {
            $table = "girls_footwear";
        }
        $check3 = $conn->query("SELECT price FROM girls_fashion_product WHERE id='$product_id'");
        if ($check3->num_rows > 0) {
            $table = "girls_fashion_product";
        }
    }

    // Product fetch
    $prod_query = $conn->query("SELECT price FROM $table WHERE id='$product_id' LIMIT 1");
    if ($prod_query && $prod_query->num_rows > 0) {
        $prod_data = $prod_query->fetch_assoc();
        $base_price = $prod_data['price'];
    } else {
        echo "<script>alert('❌ Product not found!');</script>";
        exit();
    }

    // -------------------------------------------------------------
    $delivery_charge = 50; // fixed delivery charge

    // Check if this product already exists in the buy product
    $check = $conn->query("SELECT * FROM buy_product WHERE customer_email='$email' AND product_id='$product_id' AND category='$category'");
    if ($check->num_rows > 0) {
        // Already in buy now → increase quantity and update price
        $row = $check->fetch_assoc();
        $new_qty = $row['quantity'] + 1;
        $new_price = $base_price * $new_qty + $delivery_charge;

        $conn->query("UPDATE buy_product 
             SET quantity='$new_qty', price='$new_price'
             WHERE customer_email='$email' AND product_id='$product_id' AND category='$category'");

        echo "<script>window.location = 'index.php?added=1';</script>";
        exit;
    } else {
        // New product → add delivery charge
        $final_price = $base_price + $delivery_charge;

        $sql = "INSERT INTO buy_product (customer_email, customer_name, phone, address, location, product_id, category, price, quantity)
         VALUES ('$email', '$customer_name', '$phone', '$address', '$location', '$product_id', '$category', '$final_price', 1)";

        if ($conn->query($sql)) {
            echo "<script>window.location = 'index.php?added=1';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Failed to add to buy now!');</script>";
        }
    }
}

?>




<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jswebdev29@gmail.com google search console verify site -->
    <meta name="google-site-verification" content="eAk57jSc4mjVboCMvDEem6Mtl_b8dNrNbJshunIT_D8" />

    <title>SmartCartHub</title>

    <meta name="description"
        content="Shop Boys & Girls Clothes, Shoes, Footwear, Fashion and Accessories online at SmartCartHub. Find quality products at affordable prices with a secure shopping experience.">

    <meta name="keywords"
        content="SmartCartHub, online shopping, boys clothes, girls clothes, boys shoes, girls footwear, boys fashion, girls fashion, boys accessories, girls accessories, kids fashion, fashion products, e-commerce India">
    <meta name="author" content="Jaswinder Singh">
    <meta name="robots" content="index, follow">

    <!-- Open Graph (Facebook, WhatsApp) -->
    <meta property="og:title" content="SmartCartHub | Boys & Girls Fashion Store">
    <meta property="og:description"
        content="Shop Boys & Girls Clothes, Shoes, Fashion & Accessories online at SmartCartHub.">
    <meta property="og:image" content="https://smartcarthub.infinityfreeapp.com/img/S-logo.png">
    <meta property="og:url" content="https://smartcarthub.infinityfreeapp.com/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SmartCartHub">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SmartCartHub">
    <meta name="twitter:description" content="Shop Boys & Girls Clothes, Shoes, Fashion & Accessories online.">
    <meta name="twitter:image" content="https://yourdomain.com/img/S-logo.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/e-commerce/img/S-logo.png">

    <link rel="canonical" href="https://smartcarthub.infinityfreeapp.com/">


    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
        }

        .topnav {
            background-color: #84c5f3;
            position: fixed;
            top: 0px;
            z-index: 999;
            width: 100%;
        }

        /* Default white icons */
        .topnav .nav-link {
            padding: 7px 15px;
            color: #fff;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        /* Hover colors per platform */
        .topnav .nav-link:hover i.fa-phone {
            color: #006300;
            transform: scale(1.8);
        }

        /* Green for phone */
        .topnav .nav-link:hover i.fa-envelope {
            color: #ffe23d;
            transform: scale(1.8);
        }

        /* Yellow for email */
        .topnav .nav-link:hover i.fa-instagram {
            color: #E1306C;
            transform: scale(1.8);
        }

        /* Instagram pink */
        .topnav .nav-link:hover i.fa-facebook-f {
            color: #1877F2;
            transform: scale(1.8);
        }

        /* Facebook blue */
        .topnav .nav-link:hover i.fa-twitter {
            color: #1DA1F2;
            transform: scale(1.8);
        }

        /* Twitter blue */
        .topnav .nav-link:hover i.fa-pinterest-p {
            color: #E60023;
            transform: scale(1.8);
        }

        /* Pinterest red */
        /* =================PROFILE NAVBAR========================= */

        .profile-nav {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #000;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            padding: 6px 12px;
            color: black;
            font-size: 17px;
            text-decoration: none;
        }

        .profile-img {
            width: 27px;
            height: 27px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }

        .profile-nav:hover {
            font-size: 18px;
            color: blue;

        }

        .profile-img:hover {
            transform: scale(1.08);
            border-color: #0d6efd;
        }

        /* --------------------------------------------------------- */
        .navbar {
            background-color: #84c5f3;
            position: fixed;
            top: 38px;
            z-index: 998;
            width: 100%;
        }

        .navbar .nav-link {
            color: #000;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            padding: 6px 12px;
        }

        .navbar .nav-link:hover {
            color: #fff !important;
            background-color: #0d6efd !important;
            transform: scale(1.05);
        }

        .navbar-brand {
            margin: 0 !important;
            padding: 0 !important;
        }

        .navbar {
            margin: 0 !important;
            padding: 0 !important;
        }

        #navbarSupportedContent {
            margin: 0px 10px;
        }

        #navbarSupportedContent .nav-link {
            margin: 0px 7px;
            margin-top: 15px;
            position: relative;
        }

        #carouselExampleAutoplaying {
            margin-top: 160px !important;
        }

        /* --------------------------------------------------------- */

        /* Search wrapper below navbar */
        .search-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 20px;
            position: fixed;
            top: 100px;
            right: 0;
            z-index: 997;
        }

        /* Search Box */
        .new-search {
            width: 350px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        /* Container */
        .search-container {
            display: flex;
            align-items: center;
            background: #fff;
            border: 2px solid #568bfb85;
            width: 100%;
        }

        /* Hover effect */
        .search-container:hover {
            border-color: #007bff;
            box-shadow: 0 0 12px rgba(0, 123, 255, 0.3);
        }

        /* Input */
        .search-input {
            border: none;
            outline: none;
            padding: 6px 14px;
            flex: 1;
            font-size: 15px;
        }

        /* Button */
        .search-button {
            background: #007bff;
            border: none;
            color: white;
            padding: 6px 20px;
            cursor: pointer;
        }

        /* Button hover */
        .search-button:hover {
            background: #0056b3;
        }

        /* Icon animation */
        .search-button i {
            transition: 0.3s;
        }

        .search-button:hover i {
            transform: scale(1.2) rotate(10deg);
        }



        /* Call to Action Section */
        .cta-section {
            background-color: #dad6ff;
            padding: 50px 20px;
            margin-right: 5px;
            margin-left: 5px;
            text-align: center;
            border: 3px solid #b4b4b5;
        }

        .cta-section h4 {
            font-size: 26px;
            color: #0056b3;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 18px;
            margin-bottom: 25px;
            color: #333;
        }

        .cta-section .cta-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .cta-section .cta-btn:hover {
            background-color: #0056b3;
        }

        .services-section {
            background-color: #f0f8ff;
            padding: 50px 20px;
            text-align: center;
        }

        .services-section h2 {
            font-size: 28px;
            margin-bottom: 30px;
        }

        .services-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 50px;
        }

        .service-box {
            background: #fff;
            border: 2px solid #d3d1d1;
            padding: 20px;
            width: 220px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .service-box i {
            font-size: 36px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .service-box:hover {
            transform: scale(1.05);
        }

        .video-container {
            display: flex;
            justify-content: center;
            margin: 80px 0;
        }

        video {
            width: 90%;
            max-width: 1200px;
            border-radius: 10px;
        }

        .offers-banner {
            background: linear-gradient(90deg, #ff6a00, #ee0979);
            color: white;
            text-align: center;
            width: 240px;
            margin: 15px;
            padding: 18px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 20px 0px;
            position: sticky;
            top: 170px;
            left: 1300px;
            z-index: 997;
        }


        .section-1 {
            display: inline-block;
            vertical-align: top;
            margin-top: 0px;
        }

        .section-1 h2 {
            font: sans-serif;
            font-weight: 900;
            color: #0056b3;
            text-transform: uppercase;
            text-decoration: underline;
        }

        h1 {
            text-align: center;
            font-size: 2em;
            color: #4e2020;
            font-weight: 600;
            font-family: math;
            margin: 30px 40px;
        }

        h3 {
            margin: auto;
            margin-top: 5px;
            padding: 5px;
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 50px;
            text-align: center;
            animation: anmi1 5s ease-in-out 1s infinite, rotate 2s infinite, slideInLeft 2s ease forwards;
        }

        @keyframes anmi1 {
            0% {
                background-color: #74a8cc;
                border: 2px solid rgb(199, 97, 71);
                border-radius: 10px;
                width: 40%;
                letter-spacing: 3px;
            }

            50% {
                background-color: #2941c7;
                border: 2px solid rgb(15, 131, 96);
                width: 60%;
                letter-spacing: 6px;
                word-spacing: 30px;
            }

            100% {
                background-color: #74a8cc;
                border: 2px solid rgb(199, 97, 71);
                width: 40%;
                letter-spacing: 3px;
            }
        }

        @keyframes rotate {
            0% {
                text-shadow: 5px 0 #FF7300;
            }

            50% {
                text-shadow: -5px 0 #c8ff00ff;
            }

            100% {
                text-shadow: 5px 0 #FF7300;
            }
        }

        /* ----------------------------------------------- */
        .stock-out-card {
            opacity: 0.7;
            position: relative;
        }

        .stock-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background: red;
            color: white;
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 99;
        }

        /* CARD */
        .card {
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }

        .position-relative {
            overflow: hidden;
            border-radius: 8px;
        }

        .position-relative img {
            transition: all 0.4s ease;
        }

        .position-relative:hover img {
            transform: scale(1.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
            filter: brightness(85%);
        }

        .product-image-link {
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .position-relative:hover .product-image-link {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.05);
        }

        /* ---------------------------------------- */
        footer {
            margin-top: 100px;
            background-color: #343a40;
            border-radius: 0px 0px 5px 5px;
            color: #cbcbcb;
            padding: 20px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        .footer-container {
            display: flex;
            text-align: center;
            margin: 0 auto;
            margin-left: 60px;
        }

        .footer-section {
            width: 200px;
            margin: 10px;
        }

        .footer-section h4 {
            color: white;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 5px;
        }

        .footer-section ul li a {
            color: #bbb;
            text-decoration: none;
        }

        .footer-section ul li a:hover {
            color: #fff;
        }

        .footer-bottom {
            text-align: center;
            border-top: 1px solid #b1b1b1;
            padding-top: 10px;
            margin-top: 20px;
            color: #aaa;
        }

        .footer-section a {
            color: #ecf0f1;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
            display: inline-block;
            align-items: center;
            padding: 6px 9px;
        }

        .footer-section a:hover {
            color: #f39c12;
            transform: scale(1.2);
        }

        .footer-section i {
            font-size: 22px;
        }

        /* ------------------------------------------------ */
        #availstockboy,
        #availstockgirl {
            scroll-margin-top: 155px;
        }

        #boys_clothes,
        #girls_clothes {
            scroll-margin-top: 260px;
            border-top: 2px solid black;
            padding-top: 20px;
        }

        #boys_shoes,
        #boys_fashion,
        #girls_footwear,
        #girls_fashion {
            scroll-margin-top: 140px;
            border-top: 2px solid black;
            padding-top: 20px;
            margin-top: 40px;
        }

        /* ================= MOBILE RESPONSIVE ================= */

        @media (max-width:768px) {

            /* Topnav height fixed */
            .topnav {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
            }

            /* Desktop wali row maintain */
            .topnav .container-fluid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                flex-wrap: nowrap !important;
                padding: 5px 8px;
            }

            /* Profile section */
            .topnav .navbar-nav {
                margin: 0 !important;
                flex-direction: row !important;
            }

            .profile-nav {
                display: flex;
                align-items: center;
                white-space: nowrap;
                padding: 0;
                font-size: 12px;
            }

            .profile-img {
                width: 28px;
                height: 28px;
                margin-right: 4px;
            }

            /* Contact section */
            .topnav .nav {
                display: flex;
                align-items: center;
                flex-wrap: nowrap !important;
                margin: 0;
            }

            .topnav .nav-link {
                padding: 6px 7px;
                white-space: nowrap;
                font-size: 11px;
            }

            /* Hide long text only on mobile */
            .topnav .nav-link i {
                font-size: 13px;
                margin: 0;
            }

            .topnav .nav-link .fa-phone,
            .topnav .nav-link .fa-envelope {
                margin-right: 0 !important;
            }

            /* Phone number & email text hide */
            .topnav .nav-item:first-child .nav-link,
            .topnav .nav-item:nth-child(2) .nav-link {
                font-size: 0;
            }

            .topnav .nav-item:first-child .nav-link i,
            .topnav .nav-item:nth-child(2) .nav-link i {
                font-size: 14px;
            }

            /* Navbar + search position */

            .navbar {
                top: 38px;
            }

            .search-wrapper {
                position: fixed;
                right: 0;
                top: 104px;
                width: 100%;
                padding-left: 4px;
                padding-right: 4px;
                justify-content: center;
            }

            /* Input */
            .search-input {
                padding: 4px 14px;
            }

            /* Button */
            .search-button {
                padding: 4px 18px;

            }

            .new-search {
                width: 95%;
            }

            .search-container {
                width: 100%;
            }

            /* Carousel */
            .carousel-item img {
                height: 250px !important;
                object-fit: cover;
            }

            #carouselExampleAutoplaying {
                margin-top: 164px !important;
            }

            /* CTA MOBILE RESPONSIVE  */
            .cta-section {
                padding: 30px 15px;
            }

            .cta-section h4 {
                font-size: 22px;
            }

            .cta-section p {
                font-size: 15px;
            }

            .cta-section .cta-btn {
                display: block;
                width: 100%;
                max-width: 300px;
                margin: 10px auto;
                font-size: 16px;
                padding: 10px 15px;
            }

            /* Offers */
            .offers-banner {
                position: static;
                width: 90%;
                margin-bottom: 80px;
            }

            /* Headings */
            h3 {
                font-size: 22px;
                width: 90% !important;
            }

            h1 {
                font-size: 20px;
            }

            /* Cards */
            .card {
                width: 10rem !important;
                margin: 10px auto !important;
            }

            .card img {
                height: 150px !important;
                object-fit: cover;
            }

            .btn {
                font-size: 10px;
                margin: 2px;
            }

            .card-title,
            .card-text {
                font-size: 12px;
                margin: 2px;
            }


            .footer-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                margin-left: 0;
            }

            .footer-section {
                width: 31%;
                margin: 1%;
            }

            .footer-section h4 {
                font-size: 16px;
            }

            .footer-section ul li,
            .footer-section a {
                font-size: 13px;
            }

            .footer-section i {
                font-size: 20px;
            }
        }
    </style>

</head>

<body>

    <?php if (isset($_SESSION['success_message'])) { ?>
        <script>
            alert("<?php echo addslashes($_SESSION['success_message']); ?>");
        </script>
        <?php unset($_SESSION['success_message']);
    } ?>

    <nav class="topnav navbar-expand-lg" style="background-color: #28d645;" data-bs-theme="light">
        <div class="container-fluid d-flex align-items-center justify-content-end">

            <!-- profile pic info -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['customer_email'])): ?>

                    <?php
                    $email = $_SESSION['customer_email'];

                    $profile_query = $conn->query("
                           SELECT profile_pic
                           FROM customers
                           WHERE email='$email'
                           LIMIT 1
                       ");

                    $profile_data = $profile_query->fetch_assoc();

                    $profile_img = !empty($profile_data['profile_pic'])
                        ? "uploads/profile/" . $profile_data['profile_pic']
                        : "img/default-user.png";
                    ?>

                    <li class="nav-item">
                        <a class="d-flex align-items-center profile-nav" href="customer_profile.php">

                            <img src="<?php echo htmlspecialchars($profile_img); ?>" class="profile-img">

                            <span class="ms-2">
                                My Profile
                            </span>

                        </a>
                    </li>

                <?php endif; ?>
            </ul>

            <!-- Contact info -->
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link text-white" href="tel:+917340706375">
                        <i class="fa-solid fa-phone me-1"></i> +91 7340706375
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" id="showEmail">
                        <i class="fa-solid fa-envelope me-1"></i> Click to show email
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="https://instagram.com" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="https://facebook.com" target="_blank" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="https://twitter.com" target="_blank" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="https://pinterest.com" target="_blank" title="Pinterest">
                        <i class="fab fa-pinterest-p"></i>
                    </a>
                </li>

            </ul>

        </div>
    </nav>


    <nav class="navbar navbar-expand-lg" style="background-color: #84c5f3;">
        <div class="container-fluid">
            <!-- Logo + Brand -->
            <a class="navbar-brand d-flex align-items-center" href="/e-commerce/index.php">
                <img src="/e-commerce/img/S-logo.png" alt="Logo" width="60" height="66" class="me-2 rounded-circle">
                <span>SmartCartHub</span>
            </a>

            <!-- Navbar toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Links -->
            <!-- Links -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                <ul class="navbar-nav me-auto mb-2 mx-3 mb-lg-0">

                    <!-- Home -->
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/e-commerce/index.php">
                            Home
                        </a>
                    </li>

                    <!-- Boys Products Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="boysDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Boys Essentials
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#boys_clothes">
                                    <i class="fa-solid fa-shirt text-primary"></i>
                                    Boys Clothes
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#boys_shoes">
                                    <i class="fa-solid fa-shoe-prints text-success"></i>
                                    Boys Shoes
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#boys_fashion">
                                    <i class="fa-solid fa-bag-shopping text-danger"></i>
                                    Boys Accessories
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Girls Products Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="girlsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Girls Essentials
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#girls_clothes">
                                    <i class="fa-solid fa-shirt text-primary"></i>
                                    Girls Clothes
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#girls_footwear">
                                    <i class="fa-solid fa-shoe-prints text-success"></i>
                                    Girls Footwear
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="#girls_fashion">
                                    <i class="fa-solid fa-bag-shopping text-danger"></i>
                                    Girls Accessories
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Dropdown
                        </a>

                        <ul class="dropdown-menu">

                            <li>
                                <a class="dropdown-item" href="/e-commerce/admin/index.php" target="_blank">
                                    <i class="fa-solid fa-user-shield"></i> Login Only Owner
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="/e-commerce/admin/indexCustomers.php" target="_blank">
                                    <i class="fa-solid fa-truck-fast"></i> Login Orders Management
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item disabled" href="/e-commerce/admin/logout.php">
                                    Logout
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item " href="/e-commerce/customer_login.php">
                                    <i class="fa-solid fa-user"></i> Sign In
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item " href="/e-commerce/customer_register.php">
                                    <i class="fa-solid fa-user-plus"></i> Create an Account
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <?php if (isset($_SESSION['customer_email'])): ?>

                                <li>
                                    <a class="dropdown-item" href="customer_logout.php">
                                        Logout (
                                        <?php echo $_SESSION['customer_email']; ?>
                                        )
                                    </a>
                                </li>

                            <?php else: ?>

                                <li>
                                    <a class="dropdown-item" href="customer_login.php">
                                        <i class="fa-solid fa-right-to-bracket"></i> Sign In
                                    </a>
                                </li>

                            <?php endif; ?>

                        </ul>
                    </li>

                    <!-- Orders -->
                    <li class="nav-item ms-2">
                        <a class="nav-link" href="customer_profile.php#buyproduct">
                            <i class="fa-solid fa-bag-shopping"></i> Orders

                            <span id="ord"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">

                                <?php echo $totalProducts; ?>

                            </span>
                        </a>
                    </li>

                    <li class="nav-item ms-3">
                        <a class="nav-link" href="customer_profile.php#cartproduct">

                            <i class="fa-solid fa-cart-shopping"></i>
                            Cart

                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                                <?php echo $cart_count; ?>

                            </span>

                        </a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>
    <!-- Search Bar Under Navbar -->
    <div class="search-wrapper">

        <!-- Left Empty Space -->
        <div></div>

        <!-- Right Search Box -->
        <form class="search-container new-search" method="GET" action="index.php">

            <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                placeholder="Search products..." class="search-input">

            <button type="submit" class="search-button">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </form>

    </div>


    <!-- ------------------------------------------------------------------- -->

    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">

        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/e-commerce/img/ecommerce-1.webp" class="d-block w-100 " height="560px" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h5>First slide label</h5>
                    <p>Some representative placeholder content for the first slide.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="/e-commerce/img/ecommerce-2.webp" class="d-block w-100" height="560px" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Second slide label</h5>
                    <p>Some representative placeholder content for the second slide.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="/e-commerce/img/eCommerce-3.jpg" class="d-block w-100" height="560px" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Third slide label</h5>
                    <p>Some representative placeholder content for the third slide.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- --------------------------------------------- -->
    <!-- CTA Section -->
    <section class="cta-section text-center my-4">
        <h4>Ready to shop?</h4>
        <p>Explore the latest trends at unbeatable prices.</p>
        <a href="#Product" id="strtshopboys" class="cta-btn btn btn-warning btn-lg">Start Boys Shopping</a>
        <a href="#Product" id="strtshopgirls" class="cta-btn btn btn-warning btn-lg">Start Girls Shopping</a>
    </section>

    <!-- ------------------------------------------------------- -->
    <!-- 💼 Services Section -->
    <section class="services-section">
        <h2>Our Services</h2>
        <div class="services-grid">
            <div class="service-box">
                <i class="fas fa-shipping-fast"></i>
                <h4>Fast Delivery</h4>
                <p>Get your products delivered within 2-3 days nationwide.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-tags"></i>
                <h4>Best Deals</h4>
                <p>Factory prices directly to your doorstep. No middlemen.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-shield-alt"></i>
                <h4>Secure Payment</h4>
                <p>Multiple secure payment options with SSL encryption.</p>
            </div>
            <div class="service-box">
                <i class="fas fa-headset"></i>
                <h4>24/7 Support</h4>
                <p>Chat, call, or email us anytime. We’re always here to help.</p>
            </div>
        </div>
    </section>

    <!-- ---------------------------------------------------- -->
    <!-- Promo Video -->
    <div class="video-container">
        <video src="/e-commerce/img/44942217.mp4" autoplay muted loop></video>
        <!-- <video src="/ecommerce/image/44942217.mp4" controls autoplay muted loop></video> -->
    </div>

    <!-- -------------------------------------------- -->

    <!-- Product Section -->
    <div class="section-1">
        <h2 id="availstockboy" class="text-center my-5"><i class="fa-solid fa-layer-group"></i> All Categories</h2>
    </div>

    <!-- Offers -->
    <div class="offers-banner section-1">
        🎉 Mega Sale! Flat 30% OFF on First Order | Free Shipping over ₹4999 🎉
    </div>

    <!-- Boys Products -------------------------------------------------------------------------------->
    <h3 class="text-center my-1">Boys Collection</h3>

    <!-- boyes clothes----- -->
    <h1 id="boys_clothes">
        Boys Clothes <i class="fa-solid fa-shirt"></i>
    </h1>
    <div id="boys_clothes_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_boys_clothes->num_rows > 0) { ?>
            <?php while ($row = $result_boys_clothes->fetch_assoc()) { ?>
                <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
                    style="width: 18rem;">

                    <?php if ($row['status'] == 'inactive') { ?>
                        <div class="stock-label">
                            STOCK OUT
                        </div>
                    <?php } ?>

                    <?php $fullImagePath = "admin/" . $row['image_path']; ?>
                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=boys&section=boys_clothes"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="boys">
                                <input type="hidden" name="product_table" value="boys_clothes">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=boys_clothes" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching boys products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($boys_clothes_limit < $totalRows_boys_clothes) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreBoysClothes" class="btn btn-primary" data-table="boys_clothes"
                data-limit="<?php echo $boys_clothes_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>

    <!-- boys shoes -- --->
    <h1 id="boys_shoes">
        Boys Shoes <i class="fa-solid fa-shoe-prints"></i>
    </h1>
    <div id="boys_shoes_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_boys_shoes->num_rows > 0) { ?>
        <?php while ($row = $result_boys_shoes->fetch_assoc()) { ?>
        <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
            style="width: 18rem;">

            <?php if ($row['status'] == 'inactive') { ?>
            <div class="stock-label">
                STOCK OUT
            </div>
            <?php } ?>

            <?php $fullImagePath = "admin/" . $row['image_path']; ?>
            <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=boys&section=boys_shoes"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹
                            <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="boys">
                                <input type="hidden" name="product_table" value="boys_shoes">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=boys_shoes" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching boys products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($boys_shoes_limit < $totalRows_boys_shoes) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreBoysShoes" class="btn btn-primary" data-table="boys_shoes"
                data-limit="<?php echo $boys_shoes_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>

    <!-- boys fashion  -------->
    <h1 id="boys_fashion">
        Boys Fashion & Accessories <i class="fa-solid fa-bag-shopping"></i>
    </h1>
    <div id="boys_fashion_product_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_boys_fashion_product->num_rows > 0) { ?>
            <?php while ($row = $result_boys_fashion_product->fetch_assoc()) { ?>
                <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
                    style="width: 18rem;">

                    <?php if ($row['status'] == 'inactive') { ?>
                        <div class="stock-label">
                            STOCK OUT
                        </div>
                    <?php } ?>

                    <?php $fullImagePath = "admin/" . $row['image_path']; ?>
                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=boys&section=boys_fashion"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="boys">
                                <input type="hidden" name="product_table" value="boys_fashion_product">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=boys_fashion_product" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching boys products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($boys_fashion_product_limit < $totalRows_boys_fashion_product) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreBoysFashion" class="btn btn-primary" data-table="boys_fashion_product"
                data-limit="<?php echo $boys_fashion_product_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>


    <!-- girls Product Section ------------------------------------------------------------------------>
    <div class="section-1">
        <h2 id="availstockgirl" class="text-center my-5"><i class="fa-solid fa-layer-group"></i> All Categories</h2>
    </div>

    <h3 class="text-center my-4">Girls Collection</h3>


    <!-- Girls clothes -->
    <h1 id="girls_clothes">
        Girls Clothes <i class="fa-solid fa-person"></i>
    </h1>
    <div id="girls_clothes_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_girls_clothes->num_rows > 0) { ?>
            <?php while ($row = $result_girls_clothes->fetch_assoc()) { ?>
                <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
                    style="width: 18rem;">

                    <?php if ($row['status'] == 'inactive') { ?>
                        <div class="stock-label">
                            STOCK OUT
                        </div>
                    <?php } ?>

                    <?php $fullImagePath = "admin/" . $row['image_path']; ?>
                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=girls&section=girls_clothes"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="girls">
                                <input type="hidden" name="product_table" value="girls_clothes">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=girls_clothes" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching girls products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($girls_clothes_limit < $totalRows_girls_clothes) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreGirlsClothes" class="btn btn-primary" data-table="girls_clothes"
                data-limit="<?php echo $girls_clothes_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>

    <!-- Girls footwear -->
    <h1 id="girls_footwear">
        Girls Footwear <i class="fa-solid fa-shoe-prints"></i>
    </h1>
    <div id="girls_footwear_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_girls_footwear->num_rows > 0) { ?>
            <?php while ($row = $result_girls_footwear->fetch_assoc()) { ?>
                <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
                    style="width: 18rem;">

                    <?php if ($row['status'] == 'inactive') { ?>
                        <div class="stock-label">
                            STOCK OUT
                        </div>
                    <?php } ?>

                    <?php $fullImagePath = "admin/" . $row['image_path']; ?>
                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=girls&section=girls_footwear"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="girls">
                                <input type="hidden" name="product_table" value="girls_footwear">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=girls_footwear" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching girls products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($girls_footwear_limit < $totalRows_girls_footwear) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreGirlsFootwear" class="btn btn-primary" data-table="girls_footwear"
                data-limit="<?php echo $girls_footwear_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>

    <!-- Girls fashion product -->
    <h1 id="girls_fashion">
        Girls Fashion & Accessories <i class="fa-solid fa-bag-shopping"></i>
    </h1>
    <div id="girls_fashion_product_container" class="d-flex justify-content-evenly flex-wrap text-center my-3">
        <?php if ($result_girls_fashion_product->num_rows > 0) { ?>
            <?php while ($row = $result_girls_fashion_product->fetch_assoc()) { ?>
                <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>"
                    style="width: 18rem;">

                    <?php if ($row['status'] == 'inactive') { ?>
                        <div class="stock-label">
                            STOCK OUT
                        </div>
                    <?php } ?>

                    <?php $fullImagePath = "admin/" . $row['image_path']; ?>
                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250px">
                        <!-- VIEW BUTTON ON IMAGE -->
                        <a href="view_product.php?id=<?php echo $row['id']; ?>&category=girls&section=girls_fashion"
                            class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h5>
                        <p class="card-text text-success fw-bold">
                            ₹ <?php echo number_format($row['price'], 2); ?>
                        </p>

                        <?php if ($row['status'] == 'inactive') { ?>

                            <span class="badge bg-danger mb-2 w-100 p-2">
                                Stock Out
                            </span>

                            <button type="button" class="btn btn-secondary w-100"
                                onclick="alert('❌ Product not available right now');">
                                <i class="fa-solid fa-ban"></i> Not Available
                            </button>

                        <?php } else { ?>

                            <form method="post" class="add-cart-form d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="category" value="girls">
                                <input type="hidden" name="product_table" value="girls_fashion_product">
                                <input type="hidden" name="return_url"
                                    value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>

                            <a href="checkout.php?id=<?php echo $row['id']; ?>&table=girls_fashion_product" class="btn btn-success">
                                <i class="fa-solid fa-bag-shopping"></i> Buy Now
                            </a>

                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p class="text-danger fw-bold">No matching girls products found for "
                <?php echo htmlspecialchars($searchTerm); ?>"
            </p>
        <?php } ?>
    </div>
    <?php if ($girls_fashion_product_limit < $totalRows_girls_fashion_product) { ?>
        <div class="text-center mt-4">
            <button id="loadMoreGirlsFashion" class="btn btn-primary" data-table="girls_fashion_product"
                data-limit="<?php echo $girls_fashion_product_limit; ?>">
                View All
            </button>
        </div>
    <?php } ?>



    <!-- ------------------------------------------------------------------------- -->
    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>MY ACCOUNT</h4>
                <p><br>My account <br><br>Order list <br><br>Returns <br><br>Specials <br><br>Site map</p>
            </div>

            <div class="footer-section">
                <h4>OUR SUPPORT</h4>
                <p><br>About Us <br><br>Privacy policy <br><br>Your account <br><br>Advance search <br><br>Contact us
                </p>
            </div>

            <div class="footer-section">
                <!-- <h4>OPENING TIME</h4>
                <p><br>Mon-sat --- 08:00 AM - 05.00 PM <br><br>Sun ---------Closed</p> -->

                <h4>SMARTCART</h4>
                <p><br>Your one-stop shop for <br> fashion and gadgets.</p>
            </div>

            <div class="footer-section">
                <h4>CATEGORIES</h4>

                <ul>
                    <li>
                        <br><a href="#boys_clothes">
                            Boys Clothes
                        </a>
                    </li>
                    <li>
                        <a href="#boys_shoes">
                            Boys Shoes
                        </a>
                    </li>
                    <li>
                        <a href="#boys_fashion">
                            Boys Fashion Product
                        </a>
                    </li>
                    <li>
                        <a href="#girls_clothes">
                            Girls Clothes
                        </a>
                    </li>
                    <li>
                        <a href="#girls_footwear">
                            Girls Footwear
                        </a>
                    </li>
                    <li>
                        <a href="#girls_fashion">
                            Girls Fashion Product
                        </a>
                    </li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>CONTACT US</h4>
                <p><br>Address: 123 main street,makhu</p>
                <div>
                    <a href="https://phone.com">
                        <i class="fas fa-mobile-alt"></i>+91-9876543210</a>
                    <a href="mailto:jaswindersingh@gmail.com" title="Email">
                        <i class="fas fa-envelope"></i> support@smartcart.com
                    </a>
                </div>

                <a href="https://instagram.com" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://facebook.com" target="_blank" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com" target="_blank" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://pinterest.com" target="_blank" title="Pinterest">
                    <i class="fab fa-pinterest-p"></i>
                </a>
            </div>

            <div class="footer-section">
                <h4>DROPDOWN</h4>

                <ul>
                    <li>
                        <br><a href="/e-commerce/admin/index.php">
                            <i class="fa-solid fa-user-shield"></i> Login Only Owner
                        </a>
                    </li>
                    <li>
                        <a href="/e-commerce/admin/indexCustomers.php">
                            <i class="fa-solid fa-truck-fast"></i> Login Orders Management
                        </a>
                    </li>
                    <li>
                        <a href="/e-commerce/customer_login.php">
                            <i class="fa-solid fa-user"></i> Sign In
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copyright; 2025 SmartCart. All rights reserved.</p>
        </div>
    </footer>


    <script>

        document.addEventListener("DOMContentLoaded", function () {

            // Animate Orders badge
            // Auto scroll after search
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                setTimeout(() => {
                    const targetId = "<?php echo $scrollTarget; ?>";
                    const productSection = document.getElementById(targetId);
                    if (productSection) {
                        productSection.scrollIntoView({
                            behavior: "smooth",
                            block: "start"
                        });
                    }
                }, 300);
            }

            // Show email
            document.getElementById("showEmail").addEventListener("click", function (e) {
                e.preventDefault();
                const user = "smartcarthub";
                const domain = "gmail";
                const domain2 = "com";
                const email = user + "[at]" + domain + "[dot]" + domain2;
                this.href = `mailto:${email}`;
                this.innerHTML = `<i class="fa-solid fa-envelope me-1"></i> ${email}`;
            });

            // ✅ FIXED Start Shopping Button
            // ✅ Boys Shopping Scroll
            const startBtn = document.getElementById("strtshopboys");
            if (startBtn) {
                startBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    document.getElementById("availstockboy").scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                });
            }

            // ✅ Girls Shopping Scroll
            const startBtn2 = document.getElementById("strtshopgirls");
            if (startBtn2) {
                startBtn2.addEventListener("click", function (e) {
                    e.preventDefault();
                    document.getElementById("availstockgirl").scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                });
            }

            // Success alert
            if (window.location.search.includes('added')) {
                alert("✅ Product added to cart!");
            }

        });

        //load_more_products ajax code no srooling 
        document.querySelectorAll('[data-table]').forEach(button => {
            button.addEventListener('click', function () {
                let table = this.dataset.table;
                let limit = parseInt(this.dataset.limit) + 12;
                fetch(
                    'load_more_products.php?table=' +
                    table +
                    '&limit=' +
                    limit
                )
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById(table + '_container').innerHTML = data;
                        this.dataset.limit = limit;
                    });

            });

        });

        // Save scroll position for ALL Add To Cart forms
        document.addEventListener('submit', function (e) {
            if (e.target.classList.contains('add-cart-form')) {

                sessionStorage.setItem(
                    'scrollPos',
                    window.scrollY
                );
            }
        });

        // Restore scroll position after page reload
        window.addEventListener('load', function () {
            let pos = sessionStorage.getItem('scrollPos');
            if (pos !== null) {

                window.scrollTo(0, parseInt(pos));
                sessionStorage.removeItem('scrollPos');
            }
        });

    </script>
</body>

</html>