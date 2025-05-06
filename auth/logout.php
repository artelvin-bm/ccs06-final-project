<?php
session_start();
require_once('../config/db.php');

if (isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $activity = "User logged out";

    // Log the logout activity
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$userId, $activity]);
}

// Unset and destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
