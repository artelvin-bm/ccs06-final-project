<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/activity_log_functions.php");

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    echo "Access denied.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Product ID not specified.";
    exit();
}

$productId = $_GET['id'];

// First, get the product's image filename from the database
$stmt = $pdo->prepare("SELECT name, image FROM product WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if ($product) {
    // Get the product image file path
    $imagePath = "../assets/img/" . $product['image'];

    // Get all associated reviews BEFORE deleting
    $stmt = $pdo->prepare("SELECT id, review FROM product_review_comments WHERE product_id = ?");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete related product votes (dependent records)
    foreach ($reviews as $review) {
        $stmt = $pdo->prepare("DELETE FROM product_votes WHERE review_id = ?");
        $stmt->execute([$review['id']]);
    }

    // Delete the reviews
    $stmt = $pdo->prepare("DELETE FROM product_review_comments WHERE product_id = ?");
    $stmt->execute([$productId]);

    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM product WHERE id = ?");
    $stmt->execute([$productId]);

    // Delete the product image if it exists
    if (file_exists($imagePath)) {
        unlink($imagePath); // Delete the image file from the server
    }

    // Log the activity using the function
    log_product_deletion($_SESSION['user']['id'], $product['name'], $productId, $product['image'], $reviews);

    // Redirect to manage products page
    header("Location: manage_products.php");
    exit();
} else {
    echo "Product not found.";
    exit();
}
?>
