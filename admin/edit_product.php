<?php
require_once __DIR__ . '/../connectfinity.php';

// Get ID & section
$id = intval($_GET['id']);
$section = $_GET['section'] ?? '';

// ✅ Allowed tables (IMPORTANT SECURITY)
$allowed_tables = [
    "boys_clothes",
    "boys_shoes",
    "boys_fashion_product",
    "girls_clothes",
    "girls_footwear",
    "girls_fashion_product"
];

if (!in_array($section, $allowed_tables)) {
    die("❌ Invalid section.");
}

$table = $section;

// Get existing product
$result = $conn->query("SELECT * FROM $table WHERE id = $id");
if ($result->num_rows === 0) {
    echo "Product not found.";
    exit();
}
$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $status = ($_POST['status'] === 'active') ? 'active' : 'inactive';

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $new_name = time() . "_" . basename($_FILES["image"]["name"]);
        $image_path = $target_dir . $new_name;

        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

        // OPTIONAL: delete old image
        if (!empty($product['image_path']) && file_exists($product['image_path'])) {
            unlink($product['image_path']);
        }

    } else {
        $image_path = $product['image_path'];
    }

    // Update query
    // New ID
    $new_id = intval($_POST['id']);

    // Check duplicate ID
    $check = $conn->query("SELECT id FROM $table WHERE id = $new_id AND id != $id");

    if ($check->num_rows > 0) {
        die("❌ ID already exists.");
    }

    // Update query
    $update_sql = "UPDATE $table SET 
                id = $new_id,
                name = '$name',
                price = $price,
                status = '$status',
                image_path = '$image_path'
               WHERE id = $id";


    if ($conn->query($update_sql) === TRUE) {

        header("Location: dashboard.php?updated=1&section=$section#$section");
        exit();

    } else {

        echo "Error: " . $conn->error;

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        .card {
            width: 600px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
        }

        img.preview {
            border: 2px solid #ddd;
            border-radius: 6px;
            padding: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <div class="card p-4 mt-1">
            <h2 class="mb-1 text-center text-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit <?php echo ucfirst($section); ?> Product
            </h2>
            <form id="editForm" method="post" enctype="multipart/form-data" class="mt-1">

                <div class="mb-1">
                    <label class="form-label">Product ID</label>
                    <input type="number" name="id" value="<?php echo $product['id']; ?>" class="form-control" required>
                </div>

                <div class="mb-1">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>"
                        class="form-control" required oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');">
                </div>
                <div class="mb-1">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>"
                        class="form-control" required>
                </div>
                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php if ($product['status'] === 'active')
                            echo 'selected'; ?>>Active
                        </option>
                        <option value="inactive" <?php if ($product['status'] === 'inactive')
                            echo 'selected'; ?>>Inactive
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Image</label><br>
                    <img src="<?php echo $product['image_path']; ?>" class="preview" width="120">
                    <input type="file" name="image" class="form-control mt-2">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="javascript:void(0);" onclick="goBack()" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Cancel
                    </a>
                    <button type="button" id="updateBtn" class="btn btn-success">
                        <i class="fa-solid fa-floppy-disk"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        //cancel back button
        function goBack() {
            history.back();
        }


        // update button back dashboard code
        document.getElementById("updateBtn").addEventListener("click", function () {
            let form = document.getElementById("editForm");
            let formData = new FormData(form);

            fetch("edit_product.php?section=<?php echo $section; ?>&id=<?php echo $id; ?>", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {

                    alert("✅ Product Updated Successfully");
                    history.back();
                })
                .catch(error => {
                    alert("Update Failed");
                });
        });

    </script>
</body>

</html>