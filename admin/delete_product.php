<?php
require_once __DIR__ . '/../connectfinity.php';

// Get ID & section
$id = intval($_GET['id']);
$section = $_GET['section'] ?? '';

// ✅ Allowed tables (security)
$allowed_tables = [
    "boys_clothes",
    "boys_shoes",
    "boys_fashion_product",
    "girls_clothes",
    "girls_footwear",
    "girls_fashion_product"
];

// Validate section
if (!in_array($section, $allowed_tables)) {
    die("❌ Invalid section.");
}

$table = $section;

// 🔍 Get image path before delete (for cleanup)
$result = $conn->query("SELECT image_path FROM $table WHERE id = $id");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image_path = $row['image_path'];

    // 🗑️ Delete product
    $sql = "DELETE FROM $table WHERE id = $id";

    if ($conn->query($sql) === TRUE) {

        // OPTIONAL: delete image file
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }

        header("Location: dashboard.php#$section");
        exit();

    } else {
        echo "Error deleting record: " . $conn->error;
    }

} else {
    echo "❌ Product not found.";
}

$conn->close();
?>