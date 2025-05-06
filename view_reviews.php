<?php
session_start();
include("config/db.php");

if (!isset($_GET['product_id'])) {
    die("Product ID not provided.");
}

$product_id = $_GET['product_id'];

// Fetch product details
$product_stmt = $pdo->prepare("SELECT * FROM product WHERE id = ?");
$product_stmt->execute([$product_id]);
$product = $product_stmt->fetch();

if (!$product) {
    die("Product not found.");
}

$page_title = htmlspecialchars($product['name']);

// Get average rating and review count
$stmt = $pdo->prepare("SELECT AVG(stars) AS avg_rating, COUNT(*) AS total_reviews FROM product_review_comments WHERE product_id = ?");
$stmt->execute([$product_id]);
$stats = $stmt->fetch();
$avg_rating = round($stats['avg_rating'], 1);
$total_reviews = $stats['total_reviews'];

// Fetch reviews with user info
$review_stmt = $pdo->prepare("
    SELECT pr.*, u.first_name, u.last_name 
    FROM product_review_comments pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.product_id = ? 
    ORDER BY pr.created_at DESC
");
$review_stmt->execute([$product_id]);
$reviews = $review_stmt->fetchAll();
?>

<?php include('includes/header.php'); ?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/view_reviews.css">

<div class="container mt-5">
    <div class="product-header mb-4 text-center">
        <img src="assets/img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
        <h2 class="mt-3"><?= htmlspecialchars($product['name']) ?></h2>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <?php if ($total_reviews > 0): ?>
            <p>
                <strong><?= $avg_rating ?> ⭐</strong> average rating from 
                <strong><?= $total_reviews ?></strong> review<?= $total_reviews > 1 ? 's' : '' ?>
            </p>
        <?php else: ?>
            <p class="text-muted">No reviews yet.</p>
        <?php endif; ?>
    </div>

    <h4 class="mb-3">Customer Reviews</h4>

    <?php if (count($reviews) === 0): ?>
        <p class="text-muted">No reviews yet for this product.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <!-- <div class="review-card">
                <h5><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></h5>
                <p class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= $review['stars'] ? '★' : '☆' ?>
                    <?php endfor; ?>
                </p>
                <p><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                <small class="text-muted">Posted on <?= date("F j, Y", strtotime($review['created_at'])) ?></small>
            </div> -->
            <div class="review-card">
                <h5><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></h5>
                <p class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= $review['stars'] ? '★' : '☆' ?>
                    <?php endfor; ?>
                </p>
                <p><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                <small class="text-muted">Posted on <?= date("F j, Y", strtotime($review['created_at'])) ?></small>

                <?php
                // Fetch like/dislike counts
                $vote_stmt = $pdo->prepare("SELECT 
                                                SUM(vote_type = 'like') AS likes,
                                                SUM(vote_type = 'dislike') AS dislikes 
                                            FROM product_votes 
                                            WHERE review_id = ?");
                $vote_stmt->execute([$review['id']]);
                $votes = $vote_stmt->fetch();

                $likes = $votes['likes'] ?? 0;
                $dislikes = $votes['dislikes'] ?? 0;
                ?>

                <div class="mt-2 vote-buttons" data-review-id="<?= $review['id'] ?>">
                    <button class="btn btn-sm btn-outline-success like-btn">👍 <span class="like-count"><?= $likes ?></span></button>
                    <button class="btn btn-sm btn-outline-danger dislike-btn">👎 <span class="dislike-count"><?= $dislikes ?></span></button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<script src="assets/js/view_reviews.js"></script>

<?php include('includes/footer.php'); ?>
