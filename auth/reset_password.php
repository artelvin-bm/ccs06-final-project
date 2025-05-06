<?php
require_once('../config/db.php');
session_start();

// Ensure the time zone is set to UTC to avoid any discrepancies
date_default_timezone_set('UTC');

// Get the token from the URL
$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Invalid reset link.";
    exit;
}

// Fetch the reset request from the database
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    echo "Reset link is invalid or does not exist in the database.";
    exit;
}

// Check if the token has expired
$expiresAt = $reset['expires_at'];
$currentTime = gmdate('Y-m-d H:i:s');
if ($expiresAt < $currentTime) {
    echo "Reset link has expired.";
    exit;
}

// Handle the password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([$hash, $reset['user_id']]);

        // Delete the password reset request
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")
            ->execute([$reset['user_id']]);

        // Log the password reset activity
        $activity = "User reset their password.";
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $stmt->execute([$reset['user_id'], $activity]);

        $_SESSION['success'] = "Password reset successful. You can now login.";
        header("Location: login.php");
        exit;
    }
}
?>

<!-- HTML Part -->
<?php include('../includes/header.php'); ?>
<div class="container mt-5">
    <h2>Reset Password</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
