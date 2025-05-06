<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../sentiment/analyze.php");
include("../includes/activity_log_functions.php");

// Ensure only the user who owns the review can access it
// if ($_SESSION['user']['id'] != $_GET['user_id']) {
//     echo "Access denied.";
//     exit();
// }

if (!isset($_GET['id'])) {
    echo "Review ID not specified.";
    exit();
}

$reviewId = $_GET['id'];

// Fetch the review data
$stmt = $pdo->prepare("SELECT r.id, r.review, r.stars, r.product_id, p.name AS product_name
                       FROM product_review_comments r
                       JOIN product p ON r.product_id = p.id
                       WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$reviewId, $_SESSION['user']['id']]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    echo "Review not found or you don't have permission to edit it.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewText = $_POST['review'];
    $stars = $_POST['stars'];

    // Analyze updated review sentiment
    $analysis = analyze_sentiment($reviewText);

    // Update the review
    $stmt = $pdo->prepare("UPDATE product_review_comments SET review = ?, stars = ? WHERE id = ?");
    $stmt->execute([$reviewText, $stars, $reviewId]);

    // Check if sentiment exists for this review
    $checkSentiment = $pdo->prepare("SELECT id FROM sentiments WHERE review_id = ?");
    $checkSentiment->execute([$reviewId]);
    $existingSentimentId = $checkSentiment->fetchColumn();

    if ($existingSentimentId) {
        // Update sentiment
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
        // Insert sentiment
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

    // Log activity
    $user_id = $_SESSION['user']['id'];
    $changes = [];

    if ($reviewText !== $review['review']) {
        $old = htmlspecialchars($review['review'], ENT_QUOTES);
        $new = htmlspecialchars($reviewText, ENT_QUOTES);
        $changes[] = "review changed from: \"$old\" to: \"$new\"";
    }
    
    if ($stars != $review['stars']) {
        $changes[] = "stars changed from {$review['stars']} to $stars";
    }
    
    if (!empty($changes)) {
        $changeSummary = implode(", ", $changes);
        $activity = "Updated review (ID: $reviewId) on product '{$review['product_name']}': $changeSummary.";
        log_activity($pdo, $user_id, $activity);
    }

    header("Location: edit_review.php?id=$reviewId&success=1");
    exit();
    // echo "<div class='alert alert-success'>Review updated and sentiment recalculated successfully!</div>";
}

$page_title = "Edit Review";
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-5">
    <h2>Edit Review</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Review updated and sentiment recalculated successfully!</div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="reviewText" class="form-label">Review</label>
            <textarea name="review" id="reviewText" class="form-control" rows="4" required><?= htmlspecialchars($review['review']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="stars" class="form-label">Rating</label>
            <div class="stars" id="starRating">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <input type="hidden" name="stars" id="selectedStars" value="<?= $review['stars'] ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Review</button>
    </form>

    <div class="mt-4">
        <a href="my_reviews.php" class="btn btn-secondary">Back to My Reviews</a>
    </div>
</div>


<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/stars.css">

<script src="../assets/js/edit_review_user.js"></script>

<?php include('../includes/footer.php'); ?>
