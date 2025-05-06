<?php
// session_start();
include('../includes/auth_check.php');

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Log export activity
$stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity, log_time) VALUES (?, ?, NOW())");
$stmt->execute([$_SESSION['user']['id'], 'Exported review data']);

// Fetch reviews with sentiment
$stmt = $pdo->prepare("
    SELECT r.id, p.name AS product_name, r.review, r.stars, s.type AS sentiment_type, 
           r.created_at, u.first_name, u.last_name, s.positive_count, s.negative_count, s.positive_percentage, s.negative_percentage
    FROM product_review_comments r
    JOIN product p ON r.product_id = p.id
    JOIN sentiments s ON r.id = s.review_id  -- Fix: Joining based on review_id in sentiments table
    JOIN users u ON r.user_id = u.id
");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If there are no reviews, return an error
if (empty($reviews)) {
    echo "No reviews available to export.";
    exit();
}

// Output CSV with timestamped filename
$timestamp = time(); // Adds timestamp to the filename
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"sentiment_data_$timestamp.csv\"");
$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, ['ID', 'Product Name', 'Review', 'Stars', 'Sentiment', 'Created At', 'User First Name', 'User Last Name', 'Positive Count', 'Negative Count', 'Positive Percentage', 'Negative Percentage']);

// Add reviews data to CSV
foreach ($reviews as $review) {
    fputcsv($output, [
        $review['id'],
        $review['product_name'],
        $review['review'],
        $review['stars'],
        $review['sentiment_type'],
        $review['created_at'],
        $review['first_name'],
        $review['last_name'],
        $review['positive_count'],
        $review['negative_count'],
        $review['positive_percentage'],
        $review['negative_percentage']
    ]);
}

fclose($output);
exit();
