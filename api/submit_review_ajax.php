<?php
require_once('../config/db.php');
require_once('../sentiment/analyze.php');
// session_start(); // Needed to use $_SESSION

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    $review = $_POST['review'];
    $stars = $_POST['stars'];

    // Insert the review
    $stmt = $pdo->prepare("INSERT INTO product_review_comments (user_id, product_id, review, stars, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $product_id, $review, $stars]);
    $review_id = $pdo->lastInsertId();

    // Analyze sentiment
    $analysis = analyze_sentiment($review);

    // Insert sentiment
    $stmt = $pdo->prepare("INSERT INTO sentiments (review_id, type, positive_count, negative_count, positive_percentage, negative_percentage) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $review_id,
        $analysis['type'],
        $analysis['positive'],
        $analysis['negative'],
        $analysis['positive_percentage'],
        $analysis['negative_percentage']
    ]);

    // Insert into activity_logs
    $short_review = mb_strimwidth($review, 0, 100, '...'); // Limit to 100 characters
    $activity = sprintf(
        "Submitted a review for product ID: %d with %d star(s): \"%s\"",
        $product_id,
        $stars,
        $short_review
    );
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $activity]);

    echo json_encode([
        "status" => "success",
        "sentiment" => $analysis['type'],
        "details" => $analysis
    ]);
}