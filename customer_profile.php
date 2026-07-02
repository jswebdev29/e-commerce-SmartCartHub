<?php

// 365 days login session
$lifetime = 60 * 60 * 24 * 365;
session_set_cookie_params($lifetime);
ini_set('session.gc_maxlifetime', $lifetime);


session_start();

require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {

    header("Location: customer_login.php");
    exit();
}

$email = $_SESSION['customer_email'];
// Cancel Order
if (isset($_GET['cancel_buy'])) {

    $cancel_id = intval($_GET['cancel_buy']);

    $delete = $conn->query("
        DELETE FROM buy_product
        WHERE id='$cancel_id'
        AND customer_email='$email'
    ");

    if ($delete) {

        echo "<script>
            alert('✅ Order Cancelled Successfully');
            window.location.href='customer_profile.php';
        </script>";

        exit();
    }
}

// Remove Cart Product
if (isset($_GET['remove_cart'])) {

    $remove_id = (int) $_GET['remove_cart'];

    $conn->query("
        DELETE FROM add_to_cart
        WHERE id='$remove_id'
        AND customer_email='$email'
    ");

    header("Location: customer_profile.php");
    exit();
}

// ======================================
// CUSTOMER DETAILS
// ======================================

$customer = $conn->query("
    SELECT * FROM customers
    WHERE email='$email'
    LIMIT 1
");

$cust = $customer->fetch_assoc();

// ======================================
// CART PRODUCTS
// ======================================

$cart = $conn->query("
    SELECT * FROM add_to_cart
    WHERE customer_email='$email'
    ORDER BY id DESC
");

// Buy Products
$buy_products = $conn->query("
    SELECT * FROM buy_product
    WHERE customer_email='$email'
    ORDER BY id DESC
");


?>

<!DOCTYPE html>
<html>

<head>

    <title>Customer Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            padding-top: 60px;
        }

        .profile-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-icon {
            font-size: 80px;
            color: #0d6efd;
        }

        .section-title {
            margin-top: 60px;
            margin-bottom: 25px;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            display: inline-block;
            padding-bottom: 20px;
            text-align: center !important;
        }

        .card {
            transition: 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card img {
            object-fit: cover;
        }

        .price {
            color: green;
            font-weight: bold;
            font-size: 18px;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        .empty-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            color: red;
            font-weight: bold;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.08);
        }

        .navbar-custom {
            background: linear-gradient(45deg, #007bff, #00bfff);
            padding: 15px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
        }

        .navbar-custom a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin-right: 20px;
        }

        .navbar-custom a:hover {
            color: yellow;
        }

        .dropdown-menu {
            background: #ffffff !important;
            border: 1px solid #ddd;
        }

        .dropdown-item {
            color: #000 !important;
        }

        .dropdown-item:hover {
            background: #0d6efd !important;
            color: #fff !important;
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

        #cartproduct,
        #buyproduct {
            scroll-margin-top: 100px;
        }

        /* ============MOBILE FIX======================= */

        @media (max-width:768px) {

            /* Navbar */
            .navbar-custom .container {
                flex-direction: column;
                text-align: center;
            }

            .navbar-custom a {
                font-size: 16px;
                margin: 5px;
            }

            /* Profile */
            .profile-box .row {
                display: block;
                text-align: center;
            }

            .profile-box img {
                width: 100px !important;
                height: 100px !important;
            }

            .profile-icon {
                font-size: 90px;
            }

            .container {
                max-width: 95%;
                padding-left: 8px;
                padding-right: 8px;
            }

            /* 2 Cards Per Row */
            .col-6 {
                width: 50%;
                padding: 4px;
            }

            .card {
                margin-bottom: 10px;
            }

            .card img {
                height: 140px !important;
                width: 100%;
                object-fit: cover;
            }

            .card-body {
                padding: 6px;
            }

            .card-body h4,
            .card-body h5 {
                font-size: 12px;
                margin-bottom: 2px;
                line-height: 1.2;
            }

            .price,
            .total {
                font-size: 12px;
            }

            .card-body p {
                font-size: 10px;
                margin-bottom: 3px;
            }

            .btn {
                font-size: 10px;
                padding: 4px;
            }

            .section-title {
                text-align: center;
                display: block;
                font-size: 20px;
            }

            .row {
                --bs-gutter-x: 6px;
                --bs-gutter-y: 6px;
            }

            .mobile-hide {
                display: none;
            }

        }

        /* Extra Small Devices */
        @media (max-width: 480px) {

            body {
                font-size: 14px;
            }

            .profile-box h2 {
                font-size: 20px !important;
            }

            .card img {
                height: 120px !important;
            }

            .section-title {
                font-size: 20px;
            }

            .navbar-custom {
                padding: 10px;
            }
        }
    </style>

</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar-custom">

        <div class="container d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">

                <a href="index.php" class="text-white me-3">
                    <i class="fa-solid fa-house"></i> Home
                </a>

                <!-- SETTINGS -->
                <div class="dropdown me-3">
                    <a class="text-white dropdown-toggle" href="javascript:void(0)" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-gear"></i> Settings
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item text-dark" href="customer_settings.php">
                                Profile Update
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-dark" href="change_password.php">
                                Change Password
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="customer_logout.php" class="text-white">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>

            </div>

            <div class="text-white fw-bold">
                Welcome : <?php echo $cust['name']; ?>
            </div>

        </div>
    </div>



    <div class="container mt-5">

        <!-- PROFILE -->
        <div class="profile-box">

            <div class="row align-items-center">

                <!-- PROFILE IMAGE -->
                <div class="col-md-3 text-center">

                    <?php if (!empty($cust['profile_pic'])) { ?>

                        <img src="uploads/profile/<?php echo $cust['profile_pic']; ?>" class="rounded-circle shadow"
                            width="160" height="160" style="object-fit:cover; border:4px solid #0d6efd;">

                    <?php } else { ?>

                        <i class="fa-solid fa-circle-user profile-icon"></i>

                    <?php } ?>

                </div>

                <!-- PROFILE DETAILS -->
                <div class="col-md-9">

                    <h2 class="fw-bold text-primary">
                        <?php echo $cust['name']; ?>
                    </h2>

                    <p>
                        <i class="fa-solid fa-envelope text-primary"></i>
                        <strong>Email :</strong>
                        <?php echo $cust['email']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-phone text-success"></i>
                        <strong>Phone :</strong>
                        <?php echo $cust['phone']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-calendar-days text-info"></i>
                        <strong>DOB :</strong>
                        <?php echo $cust['dob']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-location-dot text-danger"></i>
                        <strong>Address :</strong>
                        <?php echo $cust['address']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-map text-warning"></i>
                        <strong>State :</strong>
                        <?php echo $cust['state']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-building text-secondary"></i>
                        <strong>District :</strong>
                        <?php echo $cust['district']; ?>
                    </p>

                    <p>
                        <i class="fa-solid fa-tree-city text-success"></i>
                        <strong>Tehsil :</strong>
                        <?php echo $cust['tehsil']; ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- CART PRODUCTS -->
        <h2 id="cartproduct" class="section-title">
            <i class="fa-solid fa-cart-shopping"></i>
            My Cart Products
        </h2>

        <div class="row">

            <?php if ($cart->num_rows > 0) { ?>
                <?php while ($row = $cart->fetch_assoc()) { ?>
                    <div class="col-6 col-md-4">
                        <div class="card mb-4 position-relative">

                            <a href="customer_profile.php?remove_cart=<?php echo (int) $row['id']; ?>"
                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle remove-cart-btn"
                                style="width:32px;height:32px;z-index:1000;padding:0;line-height:30px;">

                                <i class="fa-solid fa-xmark"></i>

                            </a>

                            <!-- IMAGE -->
                            <div class="position-relative">
                                <img src="admin/<?php echo htmlspecialchars($row['product_image']); ?>" height="250"
                                    class="card-img-top" alt="Product Image">

                                <!-- VIEW BUTTON ON IMAGE -->
                                <?php
                                $section = trim($row['product_table']);
                                $category = (strpos($section, 'girls') === 0)
                                    ? 'girls'
                                    : 'boys';
                                ?>

                                <a href="view_product.php?id=<?php echo (int) $row['product_id']; ?>&category=<?php echo urlencode($category); ?>&section=<?php echo urlencode($section); ?>"
                                    class="product-image-link btn btn-dark btn-sm position-absolute top-50 start-50 translate-middle">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </div>

                            <div class="card-body text-center">
                                <?php
                                switch ($section) {
                                    case 'boys_clothes':
                                        $sectionTitle = 'Boys Clothes';
                                        break;
                                    case 'boys_shoes':
                                        $sectionTitle = 'Boys Shoes';
                                        break;
                                    case 'boys_fashion_product':
                                        $sectionTitle = 'Boys Fashion Products';
                                        break;
                                    case 'girls_clothes':
                                        $sectionTitle = 'Girls Clothes';
                                        break;
                                    case 'girls_footwear':
                                        $sectionTitle = 'Girls Footwear';
                                        break;
                                    case 'girls_fashion_product':
                                        $sectionTitle = 'Girls Fashion Products';
                                        break;
                                    default:
                                        $sectionTitle = 'Products';
                                }
                                ?>

                                <h6 class="text-primary fw-bold mb-2">
                                    <?php echo htmlspecialchars($sectionTitle); ?>
                                </h6>

                                <h5><?php echo htmlspecialchars($row['product_name']); ?></h5>

                                <p class="price">₹ <?php echo htmlspecialchars($row['price']); ?></p>

                                <p>Quantity: <?php echo (int) $row['quantity']; ?></p>

                                <p class="total">Total: ₹ <?php echo htmlspecialchars($row['total_price']); ?></p>

                                <!-- BUY NOW BUTTON -->
                                <a href="remove_cart_and_redirect.php?id=<?php echo (int) $row['product_id']; ?>&table=<?php echo urlencode($row['product_table']); ?>"
                                    class="btn btn-success w-100 mb-2">
                                    <i class="fa-solid fa-bag-shopping"></i> Buy Now
                                </a>
                            </div>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="col-12 text-center">
                    <p class="text-muted">Your cart is empty.</p>
                </div>

            <?php } ?>

        </div>
        <!-- BUY PRODUCTS -->
        <hr class="my-5">

        <h2 id="buyproduct" class="text-center mb-4 section-title">
            <i class="fa-solid fa-bag-shopping"></i> My Buy Products
        </h2>

        <div class="row">

            <?php while ($buy = $buy_products->fetch_assoc()) { ?>

                <?php

                $allowed_tables = [
                    'boys_clothes',
                    'boys_shoes',
                    'boys_fashion_product',
                    'girls_clothes',
                    'girls_footwear',
                    'girls_fashion_product'
                ];

                $table = trim($buy['category']);

                $product = null;

                // Invalid table name check
                if (!in_array($table, $allowed_tables)) {

                    $product = [
                        'name' => 'Invalid Product',
                        'image_path' => 'img/outstock.png'
                    ];

                } else {

                    $check = $conn->query("
        SELECT * FROM `$table`
        WHERE id='" . (int) $buy['product_id'] . "'
        LIMIT 1
    ");

                    if ($check && $check->num_rows > 0) {

                        $product = $check->fetch_assoc();

                    } else {

                        $product = [
                            'name' => 'Product Removed',
                            'image_path' => 'img/outstock.png'
                        ];
                    }
                }
                ?>

                <div class="col-6 col-md-4">

                    <div class="card mb-4 shadow border-0 rounded-4 overflow-hidden">

                        <?php
                        $imagePath = $product['image_path'];

                        if ($imagePath == 'img/outstock.png') {
                            $finalImage = $imagePath;
                        } else {
                            $finalImage = "admin/" . $imagePath;
                        }
                        ?>

                        <img src="<?php echo $finalImage; ?>" height="250" class="card-img-top">

                        <div class="card-body text-center">

                            <h4 class="text-primary">
                                <?php echo $product['name']; ?>
                            </h4>

                            <p class="fw-bold text-dark">
                                Category: <?php echo ucfirst($buy['category']); ?>
                            </p>

                            <p class="text-success fw-bold fs-4">
                                ₹ <?php echo $buy['price']; ?>
                            </p>

                            <p>
                                Quantity: <?php echo $buy['quantity']; ?>
                            </p>

                            <p class="mobile-hide">
                                👤 <?php echo $buy['customer_name']; ?>
                            </p>

                            <p class="mobile-hide">
                                📞 <?php echo $buy['phone']; ?>
                            </p>

                            <p class="mobile-hide text-muted" style="word-break: break-word;">
                                📍
                                <?php
                                echo wordwrap(
                                    $cust['address'] . ", " .
                                    $cust['district'] . ", " .
                                    $cust['state'] . ", " .
                                    $cust['tehsil'],
                                    30,
                                    "<br>"
                                );
                                ?>
                            </p>


                            <?php if ($buy['delivery_status'] == 'Delivered') { ?>

                                <div class="alert alert-success mt-5 rounded-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Delivery Successfully Completed
                                </div>

                            <?php } else { ?>

                                <div class="alert alert-warning mt-3 rounded-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-truck"></i>
                                    Order will be delivered in 5 days. <br>
                                    Your Product is Processing....
                                </div>

                                <a href="customer_profile.php?cancel_buy=<?php echo $buy['id']; ?>"
                                    class="btn btn-danger w-100 rounded-pill"
                                    onclick="return confirm('Are you sure to cancel this order?')">
                                    ❌ Cancel Order
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });


        // Save scroll position before removing cart item
        document.querySelectorAll('.remove-cart-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sessionStorage.setItem('cartScrollPos', window.scrollY);
            });

        });

        // Restore scroll position after reload
        window.addEventListener("beforeunload", function () {
            localStorage.setItem("cartScrollPos", window.scrollY);
        });

        window.addEventListener("load", function () {
            let Pos = localStorage.getItem("cartScrollPos");
            if (Pos !== null) {
                window.scrollTo(0, parseInt(Pos));
            }
        });



        // customer_profile.php de bottom te JavaScript 
        window.onload = function () {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);

                if (target) {
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            }
        };

    </script>
</body>

</html>