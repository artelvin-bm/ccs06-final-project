<?php 
include("../includes/auth_check.php");
include("../config/db.php");
include("../sentiment/analyze.php");
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

// Fetch the review data
$stmt = $pdo->prepare("SELECT r.id, r.review, r.stars, r.product_id, p.name AS product_name
                       FROM product_review_comments r
                       JOIN product p ON r.product_id = p.id
                       WHERE r.id = ?");
$stmt->execute([$reviewId]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    echo "Review not found.";
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewText = $_POST['review'];
    $stars = $_POST['stars'];

    // Analyze updated review sentiment
    $analysis = analyze_sentiment($reviewText);

    // Check if the review text or stars have changed
    $originalReview = $review['review'];
    $originalStars = $review['stars'];
    $changes = [];

    if ($originalReview !== $reviewText) {
        $changes[] = "Review text changed from '" . htmlspecialchars($originalReview) . "' to '" . htmlspecialchars($reviewText) . "'";
    }

    if ($originalStars != $stars) {
        $changes[] = "Rating changed from $originalStars stars to $stars stars";
    }

    // Update the review
    $stmt = $pdo->prepare("UPDATE product_review_comments SET review = ?, stars = ? WHERE id = ?");
    $stmt->execute([$reviewText, $stars, $reviewId]);

    // Check if sentiment exists
    $checkSentiment = $pdo->prepare("SELECT id FROM sentiments WHERE review_id = ?");
    $checkSentiment->execute([$reviewId]);
    $existingSentimentId = $checkSentiment->fetchColumn();

    if ($existingSentimentId) {
        // Update sentiment with new columns
        $stmt = $pdo->prepare("UPDATE sentiments SET type = ?, positive_count = ?, negative_count = ?, positive_percentage = ?, negative_percentage = ? WHERE review_id = ?");
        $stmt->execute([ 
            $analysis['type'],
            $analysis['positive'],
            $analysis['negative'],
            $analysis['positive_percentage'],
            $analysis['negative_percentage'],
            $reviewId
        ]);
    } else {
        // Insert sentiment with new columns
        $stmt = $pdo->prepare("INSERT INTO sentiments (review_id, type, positive_count, negative_count, positive_percentage, negative_percentage) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $reviewId,
            $analysis['type'],
            $analysis['positive'],
            $analysis['negative'],
            $analysis['positive_percentage'],
            $analysis['negative_percentage']
        ]);
    }

    // Log activity: Only log the changes that were actually made
    $user_id = $_SESSION['user']['id'];
    $activity = "Updated review (ID: $reviewId) on product '{$review['product_name']}'";

    $changeDescription = !empty($changes) ? implode(', ', $changes) : 'No changes';

    // Log the details of the update (with sentiment recalculation if applicable)
    try {
        log_review_edit_activity($user_id, $reviewId, $review['product_name'], $changeDescription);
    } catch (Exception $e) {
        error_log("Error logging review update: " . $e->getMessage());
    }

    // Redirect to avoid resubmission
    header("Location: edit_review.php?id=$reviewId&success=1");
    exit();
}

$page_title = "Edit Review";
?>

<?php include('../includes/header.php'); ?>
<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Edit Review</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Review updated and sentiment recalculated successfully!</div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="reviewText" class="form-label">Review</label>
            <textarea name="review" id="reviewText" class="form-control" rows="4" maxlength="1000" required><?= htmlspecialchars($review['review']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Rating</label>
            <div class="stars" id="starRating" role="radiogroup" aria-label="Rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="star" data-value="<?= $i ?>" aria-label="<?= $i ?> star">★</button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="stars" id="selectedStars" value="<?= $review['stars'] ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Review</button>
    </form>

    <div class="mt-4">
        <a href="manage_reviews.php" class="btn btn-secondary">Back to Manage Reviews</a>
    </div>
</div>

<link rel="stylesheet" href="../assets/css/starsTwo.css">

<script src="../assets/js/edit_review_admin.js"></script>

<?php include('../includes/footer.php'); ?>
