<?php
session_start();

/* AUTO LOGOUT TIMER */
$timeout = 3600; // 1 hour

if (!isset($_SESSION['admin_username'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['LOGIN_TIME_ADMIN'])) {
    $_SESSION['LOGIN_TIME_ADMIN'] = time();
}

$remainingTime = $timeout - (time() - $_SESSION['LOGIN_TIME_ADMIN']);
if ($remainingTime <= 0) {

    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}


require_once __DIR__ . '/../connectfinity.php';

/* =========================
   DELETE ORDER
========================= */

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete = $conn->prepare("
        DELETE FROM buy_product
        WHERE id = ?
    ");
    $delete->bind_param("i", $delete_id);
    if ($delete->execute()) {
        header("Location: customers_delivered.php?deleted=1");
        exit();
    }
}

/* =========================
   DELETE CUSTOMER
========================= */
if (isset($_GET['delete_customer'])) {
    $delete_customer = intval($_GET['delete_customer']);
    $deleteCustomer = $conn->prepare("
        DELETE FROM customers
        WHERE id = ?
    ");

    $deleteCustomer->bind_param("i", $delete_customer);
    if ($deleteCustomer->execute()) {
        header("Location: customers_delivered.php?customer_deleted=1");
        exit();
    }
}

/* =========================
   FETCH ONLY DELIVERED DATA
========================= */

$sql = "
SELECT
    buy_product.id AS cart_id,
    buy_product.product_id,
    buy_product.category,
    buy_product.price,
    buy_product.quantity,
    buy_product.delivery_status,
    buy_product.added_on,

    customers.name,
    customers.phone,
    customers.address,
    customers.district,
    customers.state,
    customers.tehsil,

    boys_clothes.image_path AS boys_clothes_image,
    boys_shoes.image_path AS boys_shoes_image,
    boys_fashion_product.image_path AS boys_fashion_image,

    girls_clothes.image_path AS girls_clothes_image,
    girls_footwear.image_path AS girls_footwear_image,
    girls_fashion_product.image_path AS girls_fashion_image

FROM buy_product

LEFT JOIN customers
ON customers.email = buy_product.customer_email

LEFT JOIN boys_clothes
ON boys_clothes.id = buy_product.product_id
AND buy_product.category = 'boys_clothes'

LEFT JOIN boys_shoes
ON boys_shoes.id = buy_product.product_id
AND buy_product.category = 'boys_shoes'

LEFT JOIN boys_fashion_product
ON boys_fashion_product.id = buy_product.product_id
AND buy_product.category = 'boys_fashion_product'

LEFT JOIN girls_clothes
ON girls_clothes.id = buy_product.product_id
AND buy_product.category = 'girls_clothes'

LEFT JOIN girls_footwear
ON girls_footwear.id = buy_product.product_id
AND buy_product.category = 'girls_footwear'

LEFT JOIN girls_fashion_product
ON girls_fashion_product.id = buy_product.product_id
AND buy_product.category = 'girls_fashion_product'

WHERE buy_product.delivery_status = 'Delivered'

ORDER BY buy_product.id DESC
";

$result = $conn->query($sql);


/* =========================
   TOTAL DELIVERED
========================= */

$totalDeliveredQuery = $conn->query("
    SELECT COUNT(*) AS total_delivered
    FROM buy_product
    WHERE delivery_status = 'Delivered'
");

$totalDeliveredData = $totalDeliveredQuery->fetch_assoc();
$totalDelivered = $totalDeliveredData['total_delivered'];

/* =========================
   FETCH CUSTOMERS DATA
========================= */

$customersQuery = $conn->query("
    SELECT *
    FROM customers
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivered Orders</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #e5efff;
            font-family: Arial, sans-serif;
        }


        html {
            scroll-behavior: smooth;
        }

        .navbar {
            position: fixed;
            top: 0;
            z-index: 998;
            width: 100%;
            background: #222fce;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar .nav-link {
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            padding: 6px 12px;
            margin: 6px 12px;
        }

        .navbar .nav-link:hover {
            color: #000 !important;
            background-color: #fff !important;
            transform: scale(1.05);
        }


        .top-navbar {
            background: #222fce !important;
        }

        .navbar .nav-link {
            position: relative;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            padding: 8px 14px;
            margin: 6px 12px;
        }

        .nav-count {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #52c927;
            color: #fff;
            min-width: 20px;
            height: 20px;
            line-height: 20px;
            border-radius: 50%;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            padding: 0 5px;
        }


        .rounded-pill {
            font-size: 16px;
            margin-top: 30px;
        }

        .btn-back {
            background: #52c927;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-back:hover {
            background: #3f9e1c;
            color: white;
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-logout:hover {
            background: #9e1e2b;
            color: white;
        }

        .alert {
            margin-top: 90px;
        }



        .table-box {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 18px rgba(0, 0, 0, .08);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            margin-bottom: 0;
        }

        table th {
            white-space: nowrap;
        }

        .product-img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }


        .btn-delete {
            background: #dc3545;
            color: white;
            border-radius: 25px;
            padding: 7px 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-delete:hover {
            background: #bb2d3b;
            color: white;
        }

        #customers-table,
        #delivered-table {
            scroll-margin-top: 200px;
        }

        /* ================MOBILE RESPONSIVE====================== */

        /* MOBILE */
        @media (max-width: 768px) {

            .container {
                max-width: 100% !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .table-box {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding: 12px;
                margin-top: 20px;
            }

            .table {
                min-width: 1100px;
            }

            .rounded-pill {
                font-size: 12px;
            }


            .navbar-brand {
                font-size: 16px;
            }

            .nav-link {
                font-size: 14px;
            }

            .btn-back,
            .btn-logout {
                width: 100%;
                margin-top: 10px;
                text-align: center;
            }

            h3 {
                font-size: 20px;
            }

            .product-img {
                width: 55px;
                height: 55px;
            }

            .btn-delete {
                font-size: 12px;
                padding: 5px 10px;
            }

            .nav-count {
                font-size: 10px;
                min-width: 18px;
                height: 18px;
                line-height: 18px;
            }

            #logoutTimer {
                font-size: 14px;
                margin-bottom: 10px;
            }
        }
    </style>

</head>

<body>

    <!-- FIXED NAVBAR -->

    <nav class="navbar navbar-expand-lg navbar-dark top-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fa-solid fa-shop"></i>Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link active" href="customers_delivered.php"> <i
                                class="fa-solid fa-house"></i>Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#customers-table">
                            <i class="fa-solid fa-users"></i>
                            Customers
                            <span class="nav-count">
                                <?php echo $customersQuery->num_rows; ?>
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#delivered-table">
                            <i class="fa-solid fa-circle-check"></i>
                            Delivered Orders
                            <span class="nav-count">
                                <?php echo $totalDelivered; ?>
                            </span>
                        </a>
                    </li>

                </ul>



                <ul class="navbar-nav mx-2 mb-2 mb-lg-0">
                    <div id="logoutTimer" class="text-warning fw-bold me-3">
                        Auto Logout In: 60:00
                    </div> <br>

                    <li class="nav-item"><a class="btn-logout ms-3" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a></li> <br>
                    <li class="nav-item"> <a href="dashboard.php" class="btn-back ms-3">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back
                        </a></li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- ALERT -->
    <?php if (isset($_GET['deleted'])) { ?>
        <div class="alert alert-danger">
            Order Deleted Successfully.
        </div>
    <?php } ?>

    <?php if (isset($_GET['customer_deleted'])) { ?>
        <div class="alert alert-warning">
            Customer Deleted Successfully.
        </div>
    <?php } ?>


    <div class="container mt-4 pt-5">
        <div class="d-flex justify-content-end">
            <span class="badge rounded-pill bg-success px-3 py-2 fs-6">
                Total Customers :
                <?php echo $customersQuery->num_rows; ?>
            </span>
        </div>
    </div>

    <!-- CUSTOMERS TABLE -->
    <div class="container mt-2">
        <div id="customers-table" class="table-box mt-6">
            <h3 class="mb-4 text-primary">
                <i class="fa-solid fa-users"></i>
                Customers Data
            </h3>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-5">
                    <thead class="table-light">
                        <tr>
                            <th>SR No</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>District</th>
                            <th>State</th>
                            <th>Tehsil</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customersQuery->num_rows > 0) { ?>
                            <?php
                            $customer_serial = 1;
                            while ($customer = $customersQuery->fetch_assoc()) {
                                ?>

                                <tr>
                                    <td>
                                        <?php echo $customer_serial++; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['email']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['address']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['district']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['state']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['tehsil']); ?>
                                    </td>
                                    <td>
                                        <a href="?delete_customer=<?php echo $customer['id']; ?>" class="btn-delete"
                                            onclick="return confirm('Delete this customer?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>

                            <?php } ?>
                        <?php } else { ?>

                            <tr>
                                <td colspan="9" class="text-danger fw-bold">
                                    No Customers Found!
                                </td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- ---------------------------------------------------------------------------- -->
        <div class="mt-4 pt-5">
            <div class="d-flex justify-content-end">
                <span class="badge rounded-pill bg-success px-3 py-2 fs-6">
                    Total Delivered Orders :
                    <?php echo $totalDelivered; ?>
                </span>
            </div>
        </div>

        <!-- DELIVERED TABLE -->
        <div id="delivered-table" class="table-box">
            <h3 class="mb-4 text-primary">
                <i class="fa-solid fa-circle-check"></i>
                Delivered Orders
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-5">
                    <thead class="table-light">
                        <tr>
                            <th>SR No</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>PRODUCT_ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Delete</th>

                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($result->num_rows > 0) { ?>

                            <?php
                            $serial_no = 1;

                            while ($row = $result->fetch_assoc()) {
                                $productImage = "uploads/no-image.png";

                                if ($row['category'] == 'boys_clothes') {
                                    $productImage = $row['boys_clothes_image'];

                                } elseif ($row['category'] == 'boys_shoes') {

                                    $productImage = $row['boys_shoes_image'];

                                } elseif ($row['category'] == 'boys_fashion_product') {

                                    $productImage = $row['boys_fashion_image'];

                                } elseif ($row['category'] == 'girls_clothes') {

                                    $productImage = $row['girls_clothes_image'];

                                } elseif ($row['category'] == 'girls_footwear') {

                                    $productImage = $row['girls_footwear_image'];

                                } elseif ($row['category'] == 'girls_fashion_product') {

                                    $productImage = $row['girls_fashion_image'];
                                }

                                if (empty($productImage)) {

                                    $productImage = "uploads/no-image.png";
                                }
                                ?>

                                <tr>
                                    <td>
                                        <?php echo $serial_no++; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['phone']); ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $row['address'] . ', ' .
                                            $row['district'] . ', ' .
                                            $row['state'] . ', ' .
                                            $row['tehsil']
                                        );
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $row['product_id']; ?>
                                    </td>
                                    <td>
                                        <img src="../admin/<?php echo htmlspecialchars($productImage); ?>" class="product-img"
                                            alt="Product">
                                    </td>
                                    <td>
                                        <?php

                                        $categoryName = '';

                                        if ($row['category'] == 'boys_clothes') {

                                            $categoryName = 'Boys Clothes';

                                        } elseif ($row['category'] == 'boys_shoes') {

                                            $categoryName = 'Boys Shoes';

                                        } elseif ($row['category'] == 'boys_fashion_product') {

                                            $categoryName = 'Boys Fashion';

                                        } elseif ($row['category'] == 'girls_clothes') {

                                            $categoryName = 'Girls Clothes';

                                        } elseif ($row['category'] == 'girls_footwear') {

                                            $categoryName = 'Girls Footwear';

                                        } elseif ($row['category'] == 'girls_fashion_product') {

                                            $categoryName = 'Girls Fashion';

                                        } else {

                                            $categoryName = ucfirst($row['category']);
                                        }

                                        echo htmlspecialchars($categoryName);

                                        ?>

                                    </td>
                                    <td>
                                        ₹<?php echo number_format($row['price'], 2); ?>
                                    </td>
                                    <td>
                                        <?php echo $row['quantity']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['added_on']; ?>
                                    </td>
                                    <td>
                                        <span class="badge-delivered">
                                            Delivered
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?delete_id=<?php echo $row['cart_id']; ?>" class="btn-delete"
                                            onclick="return confirm('Delete this order?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="12" class="text-danger fw-bold">
                                    No Delivered Orders Found!
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        let timeLeft = <?php echo $remainingTime; ?>; // 1 hour
        const timer = document.getElementById("logoutTimer");
        const countdown = setInterval(() => {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;

            seconds = seconds < 10 ? "0" + seconds : seconds;
            timer.innerHTML = `Auto Logout In: ${minutes}:${seconds}`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                alert("Session expired! Logging out...");
                window.location.href = "logout.php";
            }
            timeLeft--;
        }, 1000);

    </script>

</body>

</html>