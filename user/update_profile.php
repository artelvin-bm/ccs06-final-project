<?php
// Include necessary files
include('../includes/auth_check.php');
include('../config/db.php');

// Fetch current user details
$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Track changes
    $changes = [];

    if ($firstName !== $user['first_name']) {
        $changes[] = "First name changed from '{$user['first_name']}' to '{$firstName}'";
    }

    if ($lastName !== $user['last_name']) {
        $changes[] = "Last name changed from '{$user['last_name']}' to '{$lastName}'";
    }

    if ($email !== $user['email']) {
        $changes[] = "Email changed from '{$user['email']}' to '{$email}'";
    }

    // Only log if there are changes
    if (!empty($changes)) {
        $logMessage = "Updated profile: " . implode(", ", $changes);
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $logStmt->execute([$userId, $logMessage]);
    }

    // Update the user in the database
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $email, $userId]);

    // Update session
    $_SESSION['user']['first_name'] = $firstName;
    $_SESSION['user']['last_name'] = $lastName;
    $_SESSION['user']['email'] = $email;

    // Redirect to profile
    header("Location: profile.php?status=success");
    exit();
}
?>
