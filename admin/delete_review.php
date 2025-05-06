<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/activity_log_functions.php");

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Review ID not specified.";
    exit();
}

$reviewId = $_GET['id'];

// Fetch review details
$stmt = $pdo->prepare("SELECT prc.review, p.name AS product_name 
                       FROM product_review_comments prc 
                       JOIN product p ON prc.product_id = p.id 
                       WHERE prc.id = ?");
$stmt->execute([$reviewId]);
$review = $stmt->fetch();

if (!$review) {
    echo "Review not found.";
    exit();
}

// Count deleted votes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM product_votes WHERE review_id = ?");
$stmt->execute([$reviewId]);
$voteCount = $stmt->fetchColumn();

// Check if sentiment existed (optional, based on if it’s important to log)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sentiments WHERE review_id = ?");
$stmt->execute([$reviewId]);
$sentimentExisted = $stmt->fetchColumn() > 0;

// Log the activity using the function from activity_log_functions.php
log_review_deletion(
    $_SESSION['user']['id'], 
    $reviewId, 
    $review['product_name'], 
    $review['review'], 
    $voteCount, 
    $sentimentExisted
);

// Delete related votes
$deleteVotes = $pdo->prepare("DELETE FROM product_votes WHERE review_id = ?");
$deleteVotes->execute([$reviewId]);

// Delete the sentiment (one-to-one, safe to delete directly)
$deleteSentiment = $pdo->prepare("DELETE FROM sentiments WHERE review_id = ?");
$deleteSentiment->execute([$reviewId]);

// Delete the review
$deleteReview = $pdo->prepare("DELETE FROM product_review_comments WHERE id = ?");
$deleteReview->execute([$reviewId]);

// Redirect to manage reviews page
header("Location: manage_reviews.php");
exit();
?>
