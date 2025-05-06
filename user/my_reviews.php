<?php
// session_start(); // Assuming session is already started in auth_check.php
include('../includes/auth_check.php');
include('../config/db.php');

// Fetch reviews by the logged-in user
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name, s.type AS sentiment_type 
    FROM product_review_comments r
    JOIN product p ON r.product_id = p.id
    JOIN sentiments s ON s.review_id = r.id
    WHERE r.user_id = ?
");
$stmt->execute([$_SESSION['user']['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "My Reviews";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Your Reviews</h2>

    <?php if (empty($reviews)): ?>
        <p>You haven't submitted any reviews yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Review</th>
                    <th>Stars</th>
                    <th>Sentiment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><?= htmlspecialchars($review['product_name']) ?></td>
                    <td><?= htmlspecialchars($review['review']) ?></td>
                    <td><?= $review['stars'] ?></td>
                    <td><?= $review['sentiment_type'] ?></td>
                    <td><?= $review['created_at'] ?></td>
                    <td>
                        <a href="edit_review.php?id=<?= $review['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <!-- <a href="delete_review.php?id=<?= $review['id'] ?>" class="btn btn-danger btn-sm">Delete</a> -->
                        <a href="delete_review.php?id=<?= $review['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
