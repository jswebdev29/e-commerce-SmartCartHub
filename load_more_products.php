<?php

require_once 'connectfinity.php';

//AJAX naal hi: Othe hi rehoge Scroll nahi hovega Niche products add hunde jaan ge create code

$allowed_tables = [
    'boys_clothes',
    'boys_shoes',
    'boys_fashion_product',
    'girls_clothes',
    'girls_footwear',
    'girls_fashion_product'
];

$table = $_GET['table'] ?? '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;

if (!in_array($table, $allowed_tables)) {
    exit('Invalid table');
}

$query = "SELECT * FROM $table ORDER BY id DESC LIMIT $limit";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $fullImagePath = "admin/" . $row['image_path'];
    ?>
    <div class="card mx-2 my-3 <?php echo ($row['status'] == 'inactive') ? 'stock-out-card' : ''; ?>" style="width:18rem;">
        <?php if ($row['status'] == 'inactive') { ?>
            <div class="stock-label">STOCK OUT</div>
        <?php } ?>

        <div class="position-relative">
            <img src="<?php echo htmlspecialchars($fullImagePath); ?>" class="card-img-top" height="250">

            <?php
            $category = (strpos($table, 'boys') !== false) ? 'boys' : 'girls';
            $section = $table;
            if ($table == 'boys_fashion_product') {
                $section = 'boys_fashion';
            }
            if ($table == 'girls_fashion_product') {
                $section = 'girls_fashion';
            }
            ?>

            <a href="view_product.php?id=<?php echo $row['id']; ?>&category=<?php echo $category; ?>&section=<?php echo $section; ?>"
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
                <button type="button" class="btn btn-secondary w-100" onclick="alert('❌ Product not available right now');">
                    <i class="fa-solid fa-ban"></i> Not Available
                </button>

            <?php } else { ?>

                <form method="post" action="index.php" class="add-cart-form d-inline">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="category"
                        value="<?php echo (strpos($table, 'boys') !== false) ? 'boys' : 'girls'; ?>">
                    <input type="hidden" name="product_table" value="<?php echo $table; ?>">
                    <input type="hidden" name="return_url"
                        value="index.php?<?php echo $table; ?>_show=<?php echo $limit; ?>#<?php echo $table; ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                        <i class="fa-solid fa-cart-plus"></i>
                        Add to Cart
                    </button>
                </form>
                <a href="checkout.php?id=<?php echo $row['id']; ?>&table=<?php echo $table; ?>" class="btn btn-success">
                    <i class="fa-solid fa-bag-shopping"></i>
                    Buy Now
                </a>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>