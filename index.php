<?php
session_start(); 

include'includes/header.php';
?>

<!-- <link rel="stylesheet" href="assets/css/style.css"> -->

<div class="container mt-5">
    <h1>Welcome to the Sentiment Analysis System</h1>
    <p>This system analyzes product reviews to determine the sentiment (positive, negative, or neutral) based on a lexicon-based approach.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <!-- <img src="assets/img/placeholder.jpg" class="card-img-top" alt="Review"> -->
                <div class="card-body">
                    <h5 class="card-title">Submit a Review</h5>
                    <p class="card-text">Share your thoughts on a product and let us analyze the sentiment of your review.</p>
                    <a href="user/submit_review.php" class="btn btn-primary">Submit Review</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <!-- <img src="assets/img/placeholder.jpg" class="card-img-top" alt="Reviews"> -->
                <div class="card-body">
                    <h5 class="card-title">My Reviews</h5>
                    <p class="card-text">View and manage the reviews you’ve submitted.</p>
                    <a href="user/my_reviews.php" class="btn btn-secondary">My Reviews</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <!-- <img src="assets/img/placeholder.jpg" class="card-img-top" alt="Admin"> -->
                <div class="card-body">
                    <h5 class="card-title">Admin Dashboard</h5>
                    <p class="card-text">Manage products, users, and review sentiment data.</p>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role_id'] == 1): ?>
                        <a href="admin/dashboard.php" class="btn btn-danger">Go to Admin</a>
                    <?php else: ?>
                        <p>Admin access required.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
