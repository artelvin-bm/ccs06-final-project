<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/activity_log_functions.php");

// Ensure only the user who owns the review can access it
// if ($_SESSION['user']['role_id'] != 1 && $_SESSION['user']['id'] != $_GET['user_id']) {
//     echo "Access denied.";
//     exit();
// }

if (!isset($_GET['id'])) {
    echo "Review ID not specified.";
    exit();
}

$reviewId = $_GET['id'];

// Fetch review details
$stmt = $pdo->prepare("SELECT prc.review, p.name AS product_name 
                       FROM product_review_comments prc 
                       JOIN product p ON prc.product_id = p.id 
                       WHERE prc.id = ? AND prc.user_id = ?");
$stmt->execute([$reviewId, $_SESSION['user']['id']]);
$review = $stmt->fetch();

if (!$review) {
    echo "Review not found or you don't have permission to delete it.";
    exit();
}

// Prepare activity log details
$user_id = $_SESSION['user']['id'];
// $activity = "Deleted review (ID: $reviewId) on product '{$review['product_name']}' with content: '{$review['review']}'";

// Delete related votes
$deleteVotes = $pdo->prepare("DELETE FROM product_votes WHERE review_id = ?");
$deleteVotes->execute([$reviewId]);

// Delete the sentiment (one-to-one, safe to delete directly)
$deleteSentiment = $pdo->prepare("DELETE FROM sentiments WHERE review_id = ?");
$deleteSentiment->execute([$reviewId]);

// Delete the review
$deleteReview = $pdo->prepare("DELETE FROM product_review_comments WHERE id = ?");
$deleteReview->execute([$reviewId]);

// Log the activity
// Get vote and sentiment info for logging
$voteCountStmt = $pdo->prepare("SELECT COUNT(*) FROM product_votes WHERE review_id = ?");
$voteCountStmt->execute([$reviewId]);
$voteCount = $voteCountStmt->fetchColumn();

$sentimentCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM sentiments WHERE review_id = ?");
$sentimentCheckStmt->execute([$reviewId]);
$sentimentExisted = $sentimentCheckStmt->fetchColumn() > 0;

log_review_deletion($user_id, $reviewId, $review['product_name'], $review['review'], $voteCount, $sentimentExisted);

// Redirect to the user's review page
header("Location: my_reviews.php");
exit();
?>
