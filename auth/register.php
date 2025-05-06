<?php
require_once('../config/db.php');
session_start();

// Check for error or success messages
$error_message = $_SESSION['error'] ?? null;
$success_message = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validate password
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already exists.";
            header("Location: register.php");
            exit();
        }

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute([$fname, $lname, $email, $hash]);

        $newUserId = $pdo->lastInsertId();

        // Log activity
        $activity = "User registered: $fname $lname ($email)";
        $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $logStmt->execute([$newUserId, $activity]);

        $_SESSION['success'] = "Registration successful. Please log in.";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$page_title = "Register";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Register</h2>

    <!-- Display Error or Success Messages -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="" class="form-group">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="First Name">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Last Name">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="Email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Password">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm Password">
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <!-- Login Link -->
    <div class="mt-3">
        <p>Already have an account? <a href="../auth/login.php">Login here</a></p>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
