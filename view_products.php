<?php
session_start();
include("config/db.php");

// Fetch all products with their category names
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM product p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

$page_title = "Products";
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/view_products.css">

<div class="container mt-5">
    <h2 class="mb-4 text-center">All Products</h2>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 product-card position-relative">
                    <img src="assets/img/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        <span class="badge bg-secondary"><?= htmlspecialchars($product['category_name']) ?></span>
                    </div>
                    <div class="product-overlay d-flex flex-column justify-content-center align-items-center gap-2">
                        <a href="user/submit_review.php?product_id=<?= $product['id'] ?>" class="btn btn-primary w-75">Review Product</a>
                        <a href="view_reviews.php?product_id=<?= $product['id'] ?>" class="btn btn-light text-dark w-75">View Reviews</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
