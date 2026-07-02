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

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../connectfinity.php';

$message = "";
$redirect_link = "dashboard.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid CSRF Token");
    }

    $name = htmlspecialchars(trim($_POST["name"]), ENT_QUOTES, 'UTF-8');
    $price = (float) $_POST["price"];
    $section = $_POST["section"];
    $image = $_FILES["image"]["name"];

    // Allowed tables
    $allowed_tables = [
        "boys_clothes",
        "boys_shoes",
        "boys_fashion_product",
        "girls_clothes",
        "girls_footwear",
        "girls_fashion_product"
    ];

    if (!in_array($section, $allowed_tables)) {
        die("❌ Invalid section selected.");
    }

    $table = $section;

    // Redirect links
    $section_links = [
        "boys_clothes" => "dashboard.php#boys_clothes",
        "boys_shoes" => "dashboard.php#boys_shoes",
        "boys_fashion_product" => "dashboard.php#boys_fashion_product",

        "girls_clothes" => "dashboard.php#girls_clothes",
        "girls_footwear" => "dashboard.php#girls_footwear",
        "girls_fashion_product" => "dashboard.php#girls_fashion_product"
    ];

    $redirect_link = $section_links[$table];

    // Upload folder
    $target_dir = "uploads/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Unique image name
    $target_file = $target_dir . time() . "_" . basename($image);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allowed image types
    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

    // Check upload error first
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $message = "❌ Please select a valid image.";
    } else {

        // Check if actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $message = "❌ Invalid image file.";
        }
        // Check image size (5MB max)
        elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $message = "❌ Image size must be less than 5MB.";
        } elseif (!in_array($imageFileType, $allowed)) {

            $message = "❌ Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
        } else {

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

                //Par security lai prepared statement best aa.
                $stmt = $conn->prepare("
                INSERT INTO $table
                (name, price, image_path, status)
                VALUES (?, ?, ?, 'active')
            ");

                $stmt->bind_param(
                    "sds",
                    $name,
                    $price,
                    $target_file
                );

                if ($stmt->execute()) {
                    $message = "✅ Product added successfully!";
                } else {
                    $message = "❌ Database Error: " . $stmt->error;
                }

                $stmt->close();

            } else {
                $message = "❌ Image upload failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ================= MOBILE VIEW ================= */
        @media screen and (max-width: 576px) {

            .product-container {
                margin: 40px 30px 200px 30px !important;
                padding: 0 !important;
            }

            .col-md-6 {
                width: 100%;
                padding: 0;
            }

            .card {
                width: 95%;
            }

            .card-header h4 {
                font-size: 20px;
            }

            .form-control {
                font-size: 14px;
            }

            .btn {
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="bg-light">

    <div class="container product-container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-6">

                <!-- Message -->
                <?php if ($message): ?>

                    <div class="alert alert-info text-center">
                        <?php echo $message; ?>
                    </div>

                <?php endif; ?>

                <!-- Card -->
                <div class="card shadow-lg border-0">

                    <div class="card-header bg-primary text-white text-center">
                        <h4>Add New Product</h4>
                    </div>

                    <div class="card-body">

                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <!-- Name -->
                            <div class="mb-3">

                                <label class="form-label">Product Name</label>

                                <input type="text" name="name" class="form-control" placeholder="Enter product name"
                                    required oninput="this.value=this.value.replace(/[^a-zA-Z0-9 ]/g,'');">

                            </div>

                            <!-- Price -->
                            <div class="mb-3">

                                <label class="form-label">Price (₹)</label>

                                <input type="number" name="price" step="0.01" class="form-control"
                                    placeholder="Enter price" required>

                            </div>

                            <!-- Section -->
                            <div class="mb-3">

                                <label class="form-label">Select Section</label>

                                <select name="section" class="form-control" required>

                                    <option value="" disabled selected>
                                        Select Product Section
                                    </option>

                                    <optgroup label="Boys">

                                        <option value="boys_clothes">
                                            Boys Clothes
                                        </option>

                                        <option value="boys_shoes">
                                            Boys Shoes
                                        </option>

                                        <option value="boys_fashion_product">
                                            Boys Fashion Product
                                        </option>

                                    </optgroup>

                                    <optgroup label="Girls">

                                        <option value="girls_clothes">
                                            Girls Clothes
                                        </option>

                                        <option value="girls_footwear">
                                            Girls Footwear
                                        </option>

                                        <option value="girls_fashion_product">
                                            Girls Fashion Product
                                        </option>

                                    </optgroup>

                                </select>

                            </div>

                            <!-- Image -->
                            <div class="mb-3">

                                <label class="form-label">Upload Image</label>

                                <input type="file" name="image" class="form-control" accept="image/*" required>

                            </div>

                            <!-- Button -->
                            <button type="submit" class="btn btn-success w-100">
                                Add Product
                            </button>

                        </form>

                    </div>
                </div>

                <!-- View Button -->
                <div class="text-center mt-3">
                    <a href="<?php echo $redirect_link; ?>" class="btn btn-dark">
                        View Product List
                    </a>
                </div>

            </div>

        </div>

    </div>

</body>

</html>