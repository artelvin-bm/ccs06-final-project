<?php
require_once('../config/db.php');
session_start();

// Ensure the time zone is set to UTC to avoid any discrepancies
date_default_timezone_set('UTC');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a random token and expiration time (1 hour from now)
        $token = bin2hex(random_bytes(16));
        $expires = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
        $createdAt = gmdate('Y-m-d H:i:s');

        // Insert the reset data into the database
        $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, ?)")
            ->execute([$user['id'], $token, $expires, $createdAt]);

        // Simulate sending the email by showing the reset link
        $reset_link = "http://localhost/sentiment-analysis/auth/reset_password.php?token=$token";
        $_SESSION['success'] = "Password reset link (simulate email): <a href='$reset_link'>$reset_link</a>";
    } else {
        $_SESSION['error'] = "Email not found.";
    }

    header('Location: forgot_password.php');
    exit;
}

$page_title = "Forgot Password";
?>

<!-- HTML Part -->
<?php include('../includes/header.php'); ?>
<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Forgot Password</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php elseif (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="email">Enter your email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Request Reset</button>
    </form>
</div>
<?php include('../includes/footer.php'); ?>
<?php unset($_SESSION['error'], $_SESSION['success']); ?>
