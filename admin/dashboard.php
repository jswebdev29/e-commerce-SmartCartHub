<?php
session_start();

// Session timeout = 60 minutes
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


// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Handle search
$search = isset($_GET['q']) ? trim($_GET['q']) : "";

// Escape search input
$search_safe = $conn->real_escape_string($search);

// Boys query
// Boys Clothes
$sql_boys_clothes = "SELECT * FROM boys_clothes WHERE 1";
if ($filter === 'active') {
    $sql_boys_clothes .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_boys_clothes .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_boys_clothes .= " AND name LIKE '%$search_safe%'";
}
$sql_boys_clothes .= " ORDER BY id DESC";
$result_boys_clothes = $conn->query($sql_boys_clothes);


// Boys Shoes
$sql_boys_shoes = "SELECT * FROM boys_shoes WHERE 1";
if ($filter === 'active') {
    $sql_boys_shoes .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_boys_shoes .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_boys_shoes .= " AND name LIKE '%$search_safe%'";
}
$sql_boys_shoes .= " ORDER BY id DESC";
$result_boys_shoes = $conn->query($sql_boys_shoes);

// Boys Fashion Products
$sql_boys_fashion = "SELECT * FROM boys_fashion_product WHERE 1";
if ($filter === 'active') {
    $sql_boys_fashion .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_boys_fashion .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_boys_fashion .= " AND name LIKE '%$search_safe%'";
}
$sql_boys_fashion .= " ORDER BY id DESC";
$result_boys_fashion = $conn->query($sql_boys_fashion);


// Girls query
$sql_girls = "SELECT * FROM girls_clothes WHERE 1";
if ($filter === 'active') {
    $sql_girls .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_girls .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_girls .= " AND name LIKE '%$search_safe%'";
}
$sql_girls .= " ORDER BY id DESC";
$result_girls = $conn->query($sql_girls);


// Girls Footwear
$sql_girls_footwear = "SELECT * FROM girls_footwear WHERE 1";
if ($filter === 'active') {
    $sql_girls_footwear .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_girls_footwear .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_girls_footwear .= " AND name LIKE '%$search_safe%'";
}
$sql_girls_footwear .= " ORDER BY id DESC";
$result_girls_footwear = $conn->query($sql_girls_footwear);


// Girls Fashion Products
$sql_girls_fashion = "SELECT * FROM girls_fashion_product WHERE 1";
if ($filter === 'active') {
    $sql_girls_fashion .= " AND status='active'";
} elseif ($filter === 'inactive') {
    $sql_girls_fashion .= " AND status='inactive'";
}
if (!empty($search_safe)) {
    $sql_girls_fashion .= " AND name LIKE '%$search_safe%'";
}
$sql_girls_fashion .= " ORDER BY id DESC";
$result_girls_fashion = $conn->query($sql_girls_fashion);


/* =========================
   TOTAL PRODUCTS COUNT
========================= */

$statusCondition = "";
if ($filter == "active") {
    $statusCondition = " WHERE status='active'";
} elseif ($filter == "inactive") {
    $statusCondition = " WHERE status='inactive'";
}

// Boys Total Products
$boysClothesCount = $conn->query(
    "SELECT COUNT(*) as total FROM boys_clothes $statusCondition"
)->fetch_assoc()['total'];
$boysShoesCount = $conn->query(
    "SELECT COUNT(*) as total FROM boys_shoes $statusCondition"
)->fetch_assoc()['total'];
$boysFashionCount = $conn->query(
    "SELECT COUNT(*) as total FROM boys_fashion_product $statusCondition"
)->fetch_assoc()['total'];

$totalBoysProducts =
    $boysClothesCount +
    $boysShoesCount +
    $boysFashionCount;

// Girls Total Products
$girlsClothesCount = $conn->query(
    "SELECT COUNT(*) as total FROM girls_clothes $statusCondition"
)->fetch_assoc()['total'];
$girlsFootwearCount = $conn->query(
    "SELECT COUNT(*) as total FROM girls_footwear $statusCondition"
)->fetch_assoc()['total'];
$girlsFashionCount = $conn->query(
    "SELECT COUNT(*) as total FROM girls_fashion_product $statusCondition"
)->fetch_assoc()['total'];

$totalGirlsProducts =
    $girlsClothesCount +
    $girlsFootwearCount +
    $girlsFashionCount;

?>

<!DOCTYPE html>
<html>

<head>
    <title>Clothes Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        .navbar {
            position: fixed;
            top: 0;
            z-index: 998;
            width: 100%;
        }

        .navbar .nav-link {
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            padding: 6px 12px;
            margin: 2px 6px;
        }

        .navbar .nav-link:hover {
            color: #000 !important;
            background-color: #fff !important;
            transform: scale(1.05);
        }

        .navbar-brand:hover {
            color: gold;
        }

        .welcom-section {
            margin-top: 30px;
            padding-left: 50px;
        }

        /* --------------------------------------------------------- */

        /* Container */
        .search-container {
            display: flex;
            align-items: center;
            background: #fff;
            overflow: hidden;
            border: 2px solid #568bfb85;
            width: 300px;
            transition: 0.3s;
            position: fixed;
            top: 70px;
            /* right: 10px; */
            z-index: 996;
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
            padding: 4px 8px;
            flex: 1;
            font-size: 14px;
        }

        /* Button */
        .search-button {
            background: #007bff;
            border: none;
            color: white;
            padding: 4px 10px;
            cursor: pointer;
            transition: 0.3s;
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

        /* main heading */
        h1 {
            font-weight: 900;
            color: #0056b3;
            text-align: center;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 10px;
            scroll-margin-top: 120px;
        }

        h2 {
            font-size: 27px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 10px;
        }

        h3 {
            margin: 18px;
        }

        #main-heading th {
            text-transform: capitalize;
            font: 1.4em sans-serif;
            font-weight: bold;
            background-color: #4a6078 !important;
        }

        .mt-5 {
            margin-top: 3.4rem !important;
        }

        /* ======MODERN GRAPH CARDS=================== */

        canvas {
            max-height: 300px;
        }

        body {
            background: #f4f7fb;
            overflow-x: hidden;
        }

        .form-control {
            width: 100%;
        }

        .table-box {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid rgba(13, 110, 253, .2);
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(13, 110, 253, .08);
            background: #fff;
            padding-top: 12px;
            padding-bottom: 2px;
        }

        .table-box table {
            min-width: 1000px;
        }

        #boys_clothes,
        #girls_clothes {
            scroll-margin-top: 20px;
            padding-top: 100px;
        }

        #boys_shoes,
        #boys_fashion_product,
        #girls_footwear,
        #girls_fashion_product {
            scroll-margin-top: 80px;
            padding-top: 20px;
            margin-top: 40px;
        }

        /* -------------------------------------------------------------------------------- */
        /* Tablet */
        /* Mobile Responsive */
        @media (max-width:768px) {

            .search-container {
                left: 50%;
                transform: translateX(-50%);
                width: 95%;
                top: 65px;
            }

            .row {
                margin: 15px;
            }

            .card-body {
                padding: 12px;
            }

            canvas {
                max-height: 220px;
            }

            .card h2 {
                font-size: 18px;
            }

            .card h4 {
                font-size: 16px;
            }

            .welcom-section {
                font-size: 14px;
                line-height: 14px;
                margin-top: 100px;
            }

            .logout {
                width: 300px;
                margin: 20px;
            }

            .btn-primary {
                margin: 2px !important;
                padding-left: 8px;
            }

            .text-warning {
                margin: 10px;
            }


            h3 {
                margin: 4px;
            }

            #boys_clothes,
            #girls_clothes {
                scroll-margin-top: 65px !important;
                padding-top: 50px !important;
            }

            .table-box table {
                min-width: 800px;
            }

            .table-box {
                border: 1px solid rgba(13, 110, 253, .15);
                box-shadow: 0 0 15px rgba(13, 110, 253, .12);
                border-radius: 10px;
            }

            .table th,
            .table td {
                white-space: nowrap;
                vertical-align: middle;
            }

            #main-heading th {
                text-align: left;
                padding-left: 90px;
            }

            img {
                width: 65px !important;
                height: auto;
            }
        }


        /* Mobile */
        @media (max-width:480px) {

            .search-container {
                width: 95%;
                top: 65px;
            }

            .container {
                margin-top: 10px !important;
                padding-left: 5px;
                padding-right: 5px;
            }

            .col-md-5 {
                width: 100%;
            }

            .alert {
                font-size: 13px;
                padding: 8px;
            }

            a {
                font-size: 13px;
                word-break: break-word;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fa-solid fa-shop"></i>
                SmartCartHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link active" href="/e-commerce/index.php">Home-page</a>
                    </li>

                    <!-- Boys Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="boysDropdown" role="button"
                            data-bs-toggle="dropdown">
                            Boys Essentials
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#boys_clothes">Boys Clothes</a></li>
                            <li><a class="dropdown-item" href="#boys_shoes">Boys Shoes</a></li>
                            <li><a class="dropdown-item" href="#boys_fashion_product">Boys Accessories</a></li>
                        </ul>
                    </li>

                    <!-- Girls Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="girlsDropdown" role="button"
                            data-bs-toggle="dropdown">
                            Girls Essentials
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#girls_clothes">Girls Clothes</a></li>
                            <li><a class="dropdown-item" href="#girls_footwear">Girls Footwear</a></li>
                            <li><a class="dropdown-item" href="#girls_fashion_product">Girls Accessories</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="add_product.php">Add Product</a>
                    </li>

                </ul>


                <div id="logoutTimer" class="text-warning fw-bold me-3">
                    Auto Logout In: 01:00:00
                </div>

                <a href="customers_delivered.php" class="btn btn-primary">
                    <i class="fa-solid fa-users"></i>
                    Customers Delivered
                </a>

                <a href="customers.php" class="btn btn-primary ms-3" target="_blank">
                    <i class="fa-solid fa-users"></i>
                    Customers
                </a>

                <a class="btn btn-danger ms-3 logout" href="logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>

            </div>
        </div>
    </nav>

    <!-- Search box -->
    <div class="container mt-5 pt-2">
        <div class="d-flex justify-content-end mb-3">

            <form class="search-container" role="search" action="dashboard.php" method="get">
                <input class="search-input" type="search" placeholder="Search products..." name="q"
                    value="<?php echo htmlspecialchars($search); ?>">

                <!-- filter maintain karan layi -->
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">

                <button class="search-button" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

        </div>
    </div>

    <div class="welcom-section">
        <p class="fs-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! 🎉</p>
        <p>Glad to see you logged in successfully.</p>

        <!-- Filter Buttons -->
        <div class="mb-3">
            <a href="?filter=all<?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                class="btn btn-secondary">All</a>
            <a href="?filter=active<?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                class="btn btn-success">Active</a>
            <a href="?filter=inactive<?php echo $search ? '&q=' . urlencode($search) : ''; ?>"
                class="btn btn-danger">Inactive</a>
        </div>
    </div>
    <!-- ===========DASHBOARD GRAPH CARDS================== -->
    <div class="container mt-4 pt-2">
        <div class="row">

            <!-- Boys -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center">
                        <h2 class="text-primary">
                            <i class="fa-solid fa-person"></i>
                            Boys Products
                        </h2>
                        <h4><?php
                        if ($filter == "active") {
                            echo "Active Products: ";
                        } elseif ($filter == "inactive") {
                            echo "Inactive Products: ";
                        } else {
                            echo "Total Products: ";
                        }
                        echo $totalBoysProducts;
                        ?>
                        </h4>
                        <canvas id="boysChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Girls -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center">
                        <h2 class="text-danger">
                            <i class="fa-solid fa-person-dress"></i>
                            Girls Products
                        </h2>
                        <h4><?php
                        if ($filter == "active") {
                            echo "Active Products: ";
                        } elseif ($filter == "inactive") {
                            echo "Inactive Products: ";
                        } else {
                            echo "Total Products: ";
                        }
                        echo $totalGirlsProducts;
                        ?>
                        </h4>
                        <canvas id="girlsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container mt-2">

        <!-- Boys Products ---------------------------------------------------------------------->
        <h1 id="boys_clothes">
            <i class="fa-solid fa-person text-primary"></i> Boys Collection
        </h1>

        <!-- boys clothes   -->
        <div class="table-box mt-2">

            <table class="table table-bordered table-hover align-middle text-center mb-5">
                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7">
                            Boys Clothes<i class="fa-solid fa-shirt"></i>
                        </th>
                    </tr>
                    <tr>
                        <th>SR No</th>
                        <th>Image</th>
                        <th>Product_ID</th>
                        <th>Name</th>
                        <th>Price (₹)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_boys_clothes->num_rows > 0) { ?>

                        <?php
                        $serial = 1;
                        while ($row = $result_boys_clothes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:80px; height:auto;">
                                </td>

                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <?php
                                    echo $row['status'] == 'active'
                                        ? '<span class="badge bg-success">Active</span>'
                                        : '<span class="badge bg-danger">Inactive</span>';
                                    ?>
                                </td>

                                <td>
                                    <!-- Edit -->
                                    <a href="edit_product.php?section=boys_clothes&id=<?php echo $row['id']; ?>#boys_clothes"
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <!-- Delete -->
                                    <a href="delete_product.php?section=boys_clothes&id=<?php echo $row['id']; ?>#boys_clothes"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                    <!-- Active / Inactive -->
                                    <?php if ($row['status'] == 'active') { ?>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_clothes" data-status="inactive">
                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_clothes" data-status="active">
                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Boys Clothes Found
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="7">
                            <h3 id="boys_shoes"></h3>
                        </td>
                    </tr>
                </tbody>

                <!-- boys shoes  -->
                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7">
                            Boys Shoes<i class="fa-solid fa-shoe-prints"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_boys_shoes->num_rows > 0) { ?>

                        <?php $serial = 1;
                        while ($row = $result_boys_shoes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>" style="width:80px;"></td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td>
                                    <?php
                                    echo $row['status'] == 'active'
                                        ? '<span class="badge bg-success">Active</span>'
                                        : '<span class="badge bg-danger">Inactive</span>';
                                    ?>
                                </td>

                                <td>
                                    <!-- Edit -->
                                    <a href="edit_product.php?section=boys_shoes&id=<?php echo $row['id']; ?>#boys_shoes"
                                        class="btn btn-warning btn-sm ">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <!-- Delete -->
                                    <a href="delete_product.php?section=boys_shoes&id=<?php echo $row['id']; ?>#boys_shoes"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                    <!-- Active / Inactive -->
                                    <?php if ($row['status'] == 'active') { ?>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_shoes" data-status="inactive">
                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_shoes" data-status="active">
                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Boys Shoes Found
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="7">
                            <h3 id="boys_fashion_product"></h3>
                        </td>
                    </tr>
                </tbody>

                <!-- boys fashion product  -->

                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7"> Boys Fashion Product <i class="fa-solid fa-bag-shopping"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_boys_fashion->num_rows > 0) { ?>

                        <?php $serial = 1;
                        while ($row = $result_boys_fashion->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>" style="width:80px;"></td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php if ($row['status'] == 'active') { ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php } ?>
                                </td>

                                <td id="action-<?php echo $row['id']; ?>">
                                    <!-- Edit -->
                                    <a href="edit_product.php?section=boys_fashion_product&id=<?php echo $row['id']; ?>"
                                        class="btn btn-warning btn-sm ">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <!-- Delete -->
                                    <a href="delete_product.php?section=boys_fashion_product&id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                    <?php if ($row['status'] == 'active') { ?>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_fashion_product"
                                            data-status="inactive">
                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="boys_fashion_product"
                                            data-status="active">
                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Boys Fashion Product Found
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>

        <!-- Girls Products ------------------------------------------------------------------------>
        <h1 id="girls_clothes">
            <i class="fa-solid fa-person-dress text-danger"></i> Girls Collection
        </h1>

        <!-- girls clothes   -->
        <div class="table-box mt-2 mb-5">

            <table class="table table-bordered table-hover align-middle text-center mb-5">
                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7">girls clothes <i class="fa-solid fa-person"></i></th>
                    </tr>
                    <tr>
                        <th>SR No</th>
                        <th>Image</th>
                        <th>Product_ID</th>
                        <th>Name</th>
                        <th>Price (₹)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_girls->num_rows > 0) { ?>

                        <?php $serial = 1;
                        while ($row = $result_girls->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:80px; height:auto;">
                                </td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php if ($row['status'] == 'active') { ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php } ?>
                                </td>
                                <td id="action-<?php echo $row['id']; ?>">
                                    <a href="edit_product.php?section=girls_clothes&id=<?php echo $row['id']; ?>"
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_product.php?section=girls_clothes&id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>

                                    <?php if ($row['status'] == 'active') { ?>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_clothes" data-status="inactive">
                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>

                                    <?php } else { ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_clothes" data-status="active">
                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>

                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Girls Clothes Found
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="7">
                            <h3 id="girls_footwear"></h3>
                        </td>
                    </tr>
                </tbody>


                <!-- girls shoes  -->
                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7">girls footwear <i class="fa-solid fa-shoe-prints"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_girls_footwear->num_rows > 0) { ?>

                        <?php $serial = 1;
                        while ($row = $result_girls_footwear->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>" style="width:80px;"></td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php if ($row['status'] == 'active') { ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php } ?>
                                </td>

                                <td id="action-<?php echo $row['id']; ?>">
                                    <a href="edit_product.php?section=girls_footwear&id=<?php echo $row['id']; ?>"
                                        class="btn btn-warning btn-sm ">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_product.php?section=girls_footwear&id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                    <?php if ($row['status'] == 'active') { ?>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_footwear"
                                            data-status="inactive">

                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_footwear" data-status="active">
                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Girls Footwear Found
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="7">
                            <h3 id="girls_fashion_product"></h3>
                        </td>
                    </tr>
                </tbody>


                <!-- girls fashion  -->
                <thead class="table-dark">
                    <tr id="main-heading">
                        <th colspan="7">girls fashion <i class="fa-solid fa-bag-shopping"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_girls_fashion->num_rows > 0) { ?>

                        <?php $serial = 1;
                        while ($row = $result_girls_fashion->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $serial++; ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_path']); ?>" style="width:80px;"></td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo number_format($row['price'], 2); ?></td>
                                <td id="status-<?php echo $row['id']; ?>">
                                    <?php if ($row['status'] == 'active') { ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php } ?>
                                </td>

                                <td id="action-<?php echo $row['id']; ?>">

                                    <a href="edit_product.php?section=girls_fashion_product&id=<?php echo $row['id']; ?>"
                                        class="btn btn-warning btn-sm ">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_product.php?section=girls_fashion_product&id=<?php echo $row['id']; ?>"
                                        class="btn btn-danger btn-sm delete-card" onclick="return confirm('Delete this product?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>

                                    <?php if ($row['status'] == 'active') { ?>

                                        <button type="button" class="btn btn-secondary btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_fashion_product"
                                            data-status="inactive">

                                            <i class="fa fa-toggle-off"></i> Make Inactive
                                        </button>

                                    <?php } else { ?>

                                        <button type="button" class="btn btn-success btn-sm toggle-status"
                                            data-id="<?php echo $row['id']; ?>" data-section="girls_fashion_product"
                                            data-status="active">

                                            <i class="fa fa-toggle-on"></i> Make Active
                                        </button>

                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Girls Fashion Product Found
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        let timeLeft = <?php echo $remainingTime; ?>; // 1 hour
        const timer = document.getElementById("logoutTimer");
        const countdown = setInterval(() => {

            let hours = Math.floor(timeLeft / 3600);
            let minutes = Math.floor((timeLeft % 3600) / 60);
            let seconds = timeLeft % 60;

            hours = hours < 10 ? "0" + hours : hours;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            timer.innerHTML = `Auto Logout In: ${hours}:${minutes}:${seconds}`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                alert("Session expired! Logging out...");
                window.location.href = "logout.php";
            }
            timeLeft--;
        }, 1000);



        // BOYS CHART
        new Chart(document.getElementById('boysChart'), {
            type: 'doughnut',
            data: {
                labels: ['Clothes', 'Shoes', 'Fashion'],

                datasets: [{
                    data: [
                        <?php echo $boysClothesCount; ?>,
                        <?php echo $boysShoesCount; ?>,
                        <?php echo $boysFashionCount; ?>
                    ],
                    backgroundColor: [
                        '#0d6efd',
                        '#20c997',
                        '#ffc107'
                    ],
                    borderWidth: 2
                }]
            },

            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // GIRLS CHART
        new Chart(document.getElementById('girlsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Clothes', 'Footwear', 'Fashion'],
                datasets: [{
                    data: [
                        <?php echo $girlsClothesCount; ?>,
                        <?php echo $girlsFootwearCount; ?>,
                        <?php echo $girlsFashionCount; ?>
                    ],

                    backgroundColor: [
                        '#ff6384',
                        '#ff9f40',
                        '#9966ff'
                    ],
                    borderWidth: 2
                }]
            },

            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });


        // Save scroll position before removing cart item
        document.querySelectorAll('.delete-card').forEach(function (btn) {
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


        // active inactive scroll position no reload page ajex code
        document.addEventListener("click", function (e) {
            if (!e.target.closest(".toggle-status")) return;
            let btn = e.target.closest(".toggle-status");

            let id = btn.dataset.id;
            let section = btn.dataset.section;
            let status = btn.dataset.status;

            fetch(
                "active_inactive.php?id=" +
                id +
                "&section=" +
                section +
                "&status=" +
                status
            )
                .then(res => res.text())
                .then(data => {

                    if (data.trim() === "success") {
                        location.reload(); // sirf current page reload
                        // Agar reload bhi nahi chahiye to niche wala code use kar sakte ho.
                        fetch("load_table.php?table=" + section)
                            .then(res => res.text())
                            .then(html => {
                                document.getElementById(section + "_container").innerHTML = html;
                            });
                    }
                });
        });
    </script>

</body>

</html>