<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$review_id = $_POST['review_id'] ?? null;
$vote_type = $_POST['vote_type'] ?? null;

if (!$review_id || !in_array($vote_type, ['like', 'dislike'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Check if user already voted
$stmt = $pdo->prepare("SELECT id FROM product_votes WHERE user_id = ? AND review_id = ?");
$stmt->execute([$user_id, $review_id]);
$existing_vote = $stmt->fetch();

if ($existing_vote) {
    // Update vote
    $update = $pdo->prepare("UPDATE product_votes SET vote_type = ? WHERE id = ?");
    $update->execute([$vote_type, $existing_vote['id']]);
} else {
    // Insert new vote
    $insert = $pdo->prepare("INSERT INTO product_votes (user_id, review_id, vote_type) VALUES (?, ?, ?)");
    $insert->execute([$user_id, $review_id, $vote_type]);
}

// Insert activity log for the vote
$activity = ($vote_type == 'like') ? "Liked review ID: $review_id" : "Disliked review ID: $review_id";
$logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
$logStmt->execute([$user_id, $activity]);

// Return updated counts
$count_stmt = $pdo->prepare("SELECT 
                                SUM(vote_type = 'like') AS likes,
                                SUM(vote_type = 'dislike') AS dislikes 
                             FROM product_votes 
                             WHERE review_id = ?");
$count_stmt->execute([$review_id]);
$counts = $count_stmt->fetch();

echo json_encode($counts);
?>
