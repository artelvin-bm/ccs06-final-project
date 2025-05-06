<?php
require_once('../config/db.php');
session_start();

// Check for any error messages
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['error']);  // Clear error message after displaying

// Handle login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Sanitize email to prevent XSS attacks
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    // Prepare the query to get the user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    // Check if the user exists and the password matches
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        
        // Update the last login timestamp
        $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);

        // Insert activity log for user login
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $logStmt->execute([$user['id'], 'User logged in']);

        // Redirect to the homepage or referrer if available
        $redirectUrl = $_SESSION['redirect_url'] ?? '../index.php';
        unset($_SESSION['redirect_url']);  // Clear redirect URL after use
        header("Location: $redirectUrl");
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password.";  // Set error message
        header("Location: login.php");  // Redirect back to login page
        exit();
    }
}

$page_title = "Login";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Login</h2>

    <!-- Display error message -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="" class="form-group">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="Email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Password">
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <div class="mt-3">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
