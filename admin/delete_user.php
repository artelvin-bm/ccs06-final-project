<?php
include('../includes/auth_check.php');
include('../config/db.php');
include('../includes/activity_log_functions.php'); 

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userIdToDelete = $_GET['id'];
    $adminId = $_SESSION['user']['id'];

    // Check if the user is deleting their own account
    $isSelf = $userIdToDelete == $adminId;

    $email = null;

    if (!$isSelf) {
        // Fetch email for logging
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userIdToDelete);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $email = $user['email'];
        }

        // Delete related data
        $deleteReviewsStmt = $pdo->prepare("DELETE FROM product_review_comments WHERE user_id = :user_id");
        $deleteReviewsStmt->bindParam(':user_id', $userIdToDelete);
        $deleteReviewsStmt->execute();

        $deleteVotesStmt = $pdo->prepare("DELETE FROM product_votes WHERE user_id = :user_id");
        $deleteVotesStmt->bindParam(':user_id', $userIdToDelete);
        $deleteVotesStmt->execute();

        $deleteLogsStmt = $pdo->prepare("DELETE FROM activity_logs WHERE user_id = :user_id");
        $deleteLogsStmt->bindParam(':user_id', $userIdToDelete);
        $deleteLogsStmt->execute();
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userIdToDelete);
    $stmt->execute();

    // Log deletion
    log_user_deletion($adminId, $userIdToDelete, $isSelf, $email);

    if ($isSelf) {
        session_destroy();
        header("Location: ../index.php");
    } else {
        header("Location: manage_users.php");
    }
    exit();
} else {
    header("Location: manage_users.php");
    exit();
}
?>
