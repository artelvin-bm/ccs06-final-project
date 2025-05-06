<?php
include('../includes/auth_check.php');

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Fetch all unique sentiment types
$stmt = $pdo->prepare("SELECT DISTINCT type FROM sentiments");
$stmt->execute();
$sentiments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sentiment filter
$sentimentFilter = '';
if (isset($_GET['sentiment']) && $_GET['sentiment'] !== '') {
    $sentimentFilter = $_GET['sentiment'];
    $stmt = $pdo->prepare("SELECT r.id, p.name AS product_name, r.review, r.stars, s.type AS sentiment_type, r.created_at, u.first_name, u.last_name
                           FROM product_review_comments r
                           JOIN product p ON r.product_id = p.id
                           JOIN sentiments s ON r.id = s.review_id
                           JOIN users u ON r.user_id = u.id
                           WHERE s.type = ?");
    $stmt->execute([$sentimentFilter]);
} else {
    // Fetch all reviews
    $stmt = $pdo->prepare("SELECT r.id, p.name AS product_name, r.review, r.stars, s.type AS sentiment_type, r.created_at, u.first_name, u.last_name
                           FROM product_review_comments r
                           JOIN product p ON r.product_id = p.id
                           JOIN sentiments s ON r.id = s.review_id
                           JOIN users u ON r.user_id = u.id");
    $stmt->execute();
}

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manage Reviews";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Manage Reviews</h2>

    <!-- Sentiment Filter -->
    <form method="get" class="form-inline mb-3">
        <label for="sentimentFilter" class="mr-2">Filter by Sentiment</label>
        <select name="sentiment" id="sentimentFilter" class="form-control">
            <option value="">All Sentiments</option>
            <?php foreach ($sentiments as $sentiment): ?>
                <option value="<?= $sentiment['type'] ?>" <?= $sentiment['type'] == $sentimentFilter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sentiment['type']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary ml-2">Filter</button>
    </form>

    <!-- Review Table -->
    <?php if (empty($reviews)): ?>
        <p>No reviews found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Review</th>
                    <th>Stars</th>
                    <th>Sentiment</th>
                    <th>Created At</th>
                    <th>User</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['product_name']) ?></td>
                        <td><?= htmlspecialchars(substr($review['review'], 0, 50)) ?>...</td>
                        <td><?= $review['stars'] ?></td>
                        <td><?= htmlspecialchars($review['sentiment_type']) ?></td>
                        <td><?= $review['created_at'] ?></td>
                        <td><?= htmlspecialchars($review['first_name']) ?> <?= htmlspecialchars($review['last_name']) ?></td>
                        <td>
                            <a href="edit_review.php?id=<?= $review['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_review.php?id=<?= $review['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
