<?php
require_once __DIR__ . '/../connectfinity.php';

if (!isset($_GET['id'], $_GET['section'], $_GET['status'])) {
    exit("error");
}

$id = (int)$_GET['id'];
$section = $_GET['section'];
$status = ($_GET['status'] == 'active') ? 'active' : 'inactive';

$allowed_tables = [
    "boys_clothes",
    "boys_shoes",
    "boys_fashion_product",
    "girls_clothes",
    "girls_footwear",
    "girls_fashion_product"
];

if (!in_array($section, $allowed_tables)) {
    exit("error");
}

$sql = "UPDATE `$section` SET status='$status' WHERE id=$id";

if ($conn->query($sql)) {
    echo "success";
} else {
    echo "error";
}