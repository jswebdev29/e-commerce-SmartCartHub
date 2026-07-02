<?php

// Session 1 day tak valid
$lifetime = 43200; // 12 hours

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$timeout = 43200; // 12 hours

if (
    !isset($_SESSION['admin_customer']) ||
    !isset($_SESSION['LOGIN_TIME_CUSTOMER'])
) {
    header("Location: indexCustomers.php");
    exit();
}

$remainingTime = $timeout - (time() - $_SESSION['LOGIN_TIME_CUSTOMER']);
if ($remainingTime <= 0) {

    session_unset();
    session_destroy();
    header("Location: indexCustomers.php?timeout=1");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");


require_once __DIR__ . '/../connectfinity.php';


/* =========================
   DELIVERY UPDATE
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['deliver_id'])
) {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid CSRF Token");
    }

    $deliver_id = (int) $_POST['deliver_id'];

    $update = $conn->prepare("
        UPDATE buy_product
        SET delivery_status = 'Delivered',
            delivered_on = NOW()
        WHERE id = ?
    ");

    $update->bind_param("i", $deliver_id);

    if ($update->execute()) {

        header("Location: customers.php?success=1");
        exit();
    }
}


/* =========================
   FILTER BUTTONS
========================= */

$statusFilter = "";


/* =========================
   FETCH DATA
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

WHERE buy_product.delivery_status = 'Pending'

ORDER BY buy_product.id DESC
";

$result = $conn->query($sql);


/* =========================
   TOTAL PENDING ORDERS
========================= */

$totalPending = ($result) ? $result->num_rows : 0;


/* =========================
   LAST 7 DAYS DELIVERED
========================= */

$deliveredQuery = $conn->query("
    SELECT COUNT(id) AS total_delivered
    FROM buy_product
    WHERE delivery_status = 'Delivered'
    AND DATE(delivered_on) >= CURDATE() - INTERVAL 7 DAY
");

$deliveredData = $deliveredQuery->fetch_assoc();

$totalDelivered = $deliveredData['total_delivered'];

/* =========================
   TOTAL ORDERS
========================= */

$totalOrdersQuery = $conn->query("
    SELECT COUNT(*) AS total_orders
    FROM buy_product
    WHERE delivery_status = 'Pending'
");

$totalOrdersData = $totalOrdersQuery->fetch_assoc();

$totalOrders = $totalOrdersData['total_orders'];


/* =========================
   TOTAL CUSTOMERS
========================= */

$totalCustomerQuery = $conn->query("
    SELECT COUNT(DISTINCT customer_email) AS total_customers
    FROM buy_product
    WHERE delivery_status = 'Pending'
");

$totalCustomerData = $totalCustomerQuery->fetch_assoc();

$totalCustomers = $totalCustomerData['total_customers'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customers Orders</title>

    <link rel="stylesheet" href="assets/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fc;
            font-family: Arial, sans-serif;
        }

        .main-container {
            width: 100%;
            padding: 25px;
            margin-top: 80px;
        }

        .page-title {
            background: linear-gradient(90deg, #007bff, #00bfff);
            color: white;
            padding: 18px;
            border-radius: 14px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .top-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        /* =========================
   MODERN DASHBOARD CARDS
========================= */

        .info-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            width: 100%;
            margin-top: 10px;
        }

        .info-card {
            position: relative;
            overflow: hidden;
            padding: 15px 20px;
            border-radius: 24px;
            text-align: left;
            text-decoration: none;
            color: white;
            transition: 0.4s ease;
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.12),
                inset 0 1px 1px rgba(255, 255, 255, 0.2);

            backdrop-filter: blur(10px);
        }

        .info-card:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .info-card::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 160px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
        }

        .info-card::after {
            content: "";
            position: absolute;
            bottom: -70px;
            right: -20px;
            width: 150px;
            height: 160px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-top i {
            font-size: 35px;
            opacity: 0.9;
        }

        .card-top span {
            font-size: 14px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.18);
        }

        .info-card h3 {
            font-size: 35px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .info-card p {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-customers {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .card-orders {
            background: linear-gradient(135deg, #232526, #414345);
        }

        .card-delivered {
            background: linear-gradient(135deg, #0061ff, #60efff);
        }

        .card-pending {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
        }

        /* MINI GRAPH EFFECT */

        .graph-line {
            width: 100%;
            height: 40px;
            margin-top: 10px;
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }

        .graph-line span {
            flex: 1;
            border-radius: 10px 10px 0 0;
            background: rgba(255, 255, 255, 0.35);
            animation: graphMove 2s infinite ease-in-out;
        }

        .graph-line span:nth-child(1) {
            height: 30%;
        }

        .graph-line span:nth-child(2) {
            height: 65%;
        }

        .graph-line span:nth-child(3) {
            height: 45%;
        }

        .graph-line span:nth-child(4) {
            height: 90%;
        }

        .graph-line span:nth-child(5) {
            height: 70%;
        }

        @keyframes graphMove {
            0% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        .table-box {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1200px;
        }

        thead {
            background: #0d6efd;
            color: white;
        }

        th,
        td {
            padding: 14px;
            text-align: center;
            vertical-align: middle !important;
        }

        tbody tr:hover {
            background: #eef5ff;
        }

        .product-img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .badge-pending {
            background: #dc3545;
            color: white;
            padding: 7px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-delivered {
            background: #198754;
            color: white;
            padding: 7px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-deliver {
            background: #198754;
            color: white;
            border-radius: 25px;
            padding: 7px 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
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

        .completed-btn {
            background: gray;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 7px 14px;
        }

        .btn-back {
            background: #212529;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar-custom {
            background: linear-gradient(90deg, #007bff, #00bfff);
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 998;
        }

        .navbar-custom a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar-custom a:hover {
            color: yellow;
        }

        h2 {
            color: white;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        h2:hover {
            color: yellow;
        }
    </style>

</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar-custom">

        <div class="container d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">

                <a href="customers.php" class="text-white fw-bold me-3">
                    <h2><i class="fa-solid fa-cart-shopping"></i>
                        Customer Orders Management</h2>
                </a>

            </div>
            <a href="logoutCustomers.php" class="btn-back">
                <i class="fa-solid fa-right-from-bracket"></i>Logout
            </a>

        </div>
    </div>

    <div class="main-container">


        <!-- ALERTS -->
        <?php if (isset($_GET['success'])) { ?>

            <div class="alert alert-success">
                Delivery Status Updated Successfully.
            </div>

        <?php } ?>

        <?php if (isset($_GET['deleted'])) { ?>

            <div class="alert alert-danger">
                Order Deleted Successfully.
            </div>

        <?php } ?>

        <!-- TOP -->
        <div class="top-box">

            <div class="info-boxes">

                <!-- TOTAL CUSTOMERS -->
                <a href="customers.php" class="info-card card-customers">

                    <div class="card-top">
                        <i class="fa-solid fa-users"></i>
                        <span>LIVE</span>
                    </div>

                    <h3><?php echo $totalCustomers; ?></h3>

                    <p>Total Customers</p>

                    <div class="graph-line">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </a>

                <!-- TOTAL ORDERS -->
                <a href="customers.php" class="info-card card-orders">

                    <div class="card-top">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>ORDERS</span>
                    </div>

                    <h3><?php echo $totalOrders; ?></h3>

                    <p>Total Orders</p>

                    <div class="graph-line">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </a>

                <!-- DELIVERED -->
                <a href="customers.php?status=delivered" class="info-card card-delivered">

                    <div class="card-top">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>SUCCESS</span>
                    </div>

                    <h3><?php echo $totalDelivered; ?></h3>

                    <p>Delivered Orders</p>

                    <div class="graph-line">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </a>

                <!-- PENDING -->
                <a href="customers.php?status=pending" class="info-card card-pending">

                    <div class="card-top">
                        <i class="fa-solid fa-clock"></i>
                        <span>PENDING</span>
                    </div>

                    <h3><?php echo $totalPending; ?></h3>

                    <p>Pending Orders</p>

                    <div class="graph-line">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </a>

            </div>
        </div>

        <div id="logoutTimer" style="color:red;font-weight:bold;text-align:center;margin:10px;">
        </div>

        <!-- TABLE -->
        <div class="table-box">

            <table class="table table-bordered table-hover">

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
                        <th>Delivery</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result->num_rows > 0) { ?>

                        <?php
                        $serial_no = 1;
                        while ($row = $result->fetch_assoc()) {

                            // DEFAULT IMAGE
                            $productImage = "uploads/no-image.png";

                            // CATEGORY IMAGE
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

                            // EMPTY IMAGE CHECK
                            if (empty($productImage)) {

                                $productImage = "uploads/no-image.png";
                            }
                            ?>

                            <tr>

                                <!-- SERIAL -->
                                <td><?php echo $serial_no++; ?></td>
                                <!-- CUSTOMER -->
                                <td>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </td>

                                <!-- PHONE -->
                                <td>
                                    <?php echo htmlspecialchars($row['phone']); ?>
                                </td>

                                <!-- ADDRESS -->
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
                                    <?php echo (int) $row['product_id']; ?>
                                </td>

                                <!-- PRODUCT IMAGE -->
                                <td>
                                    <img src="../admin/<?php echo htmlspecialchars($productImage); ?>" class="product-img"
                                        alt="Product">
                                </td>

                                <!-- CATEGORY -->
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

                                <!-- PRICE -->
                                <td>
                                    ₹<?php echo number_format($row['price'], 2); ?>
                                </td>

                                <!-- QTY -->
                                <td>
                                    <?php echo $row['quantity']; ?>
                                </td>

                                <!-- DATE -->
                                <td>
                                    <?php echo htmlspecialchars($row['added_on']); ?>
                                </td>

                                <!-- STATUS -->
                                <td>

                                    <?php if ($row['delivery_status'] == 'Delivered') { ?>

                                        <span class="badge-delivered">
                                            Delivered
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge-pending">
                                            Pending
                                        </span>

                                    <?php } ?>

                                </td>

                                <!-- DELIVERY -->
                                <td>

                                    <?php if ($row['delivery_status'] != 'Delivered') { ?>

                                        <form method="post" onsubmit="return confirm('Delivery completed?')">

                                            <input type="hidden" name="deliver_id" value="<?php echo (int) $row['cart_id']; ?>">

                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                            <button type="submit" class="btn-deliver">
                                                Delivered
                                            </button>

                                        </form>

                                    <?php } else { ?>

                                        <button class="completed-btn" disabled>
                                            Completed
                                        </button>

                                    <?php } ?>

                                </td>


                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="12" class="text-danger fw-bold">
                                No Orders Found!
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <script src="assets/bootstrap.bundle.min.js"></script>

    <script>
        let timeLeft = <?php echo $remainingTime; ?>;

        const countdown = setInterval(() => {

            let hours = Math.floor(timeLeft / 3600);
            let minutes = Math.floor((timeLeft % 3600) / 60);
            let seconds = timeLeft % 60;

            document.getElementById("logoutTimer").innerHTML =
                `Auto Logout In: ${hours.toString().padStart(2, '0')}:` +
                `${minutes.toString().padStart(2, '0')}:` +
                `${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = "logoutCustomers.php";
            }

            timeLeft--;

        }, 1000);
    </script>
</body>

</html>