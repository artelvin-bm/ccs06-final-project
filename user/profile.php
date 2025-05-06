<?php
// session_start();
include('../includes/auth_check.php');
include('../config/db.php');

// Fetch user details from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Profile";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Your Profile</h2>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success mt-3">
            Your profile has been updated successfully!
        </div>
    <?php endif; ?>

    <form action="update_profile.php" method="POST">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
