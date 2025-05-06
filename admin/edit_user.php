<?php
// session_start();
include('../includes/auth_check.php');
include('../includes/activity_log_functions.php');

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Get the user ID from the URL
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = $_GET['id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, r.role_name AS role_name 
                        FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found
    header("Location: manage_users.php");
    exit();
}

// Fetch roles for the role dropdown
$roles = $pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    // Update user details
    $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, role_id = :role_id WHERE id = :id");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role_id', $role_id);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();

    // Log activity: User edit
    // Generate detailed activity log
    $changes = [];

    if ($user['first_name'] !== $first_name) {
        $changes[] = "first name '{$user['first_name']}' to '$first_name'";
    }
    if ($user['last_name'] !== $last_name) {
        $changes[] = "last name '{$user['last_name']}' to '$last_name'";
    }
    if ($user['email'] !== $email) {
        $changes[] = "email '{$user['email']}' to '$email'";
    }
    if ($user['role_id'] != $role_id) {
        // Get role names
        $old_role = $user['role_name'];
        $new_role = '';
        foreach ($roles as $role) {
            if ($role['id'] == $role_id) {
                $new_role = $role['role_name'];
                break;
            }
        }
        $changes[] = "role '$old_role' to '$new_role'";
    }

    if (!empty($changes)) {
        $change_summary = implode(', ', $changes);
        try {
            log_user_edit_activity($_SESSION['user']['id'], $user_id, $change_summary);
        } catch (Exception $e) {
            error_log("Error logging user edit: " . $e->getMessage());
        }
    }

    // Redirect to manage_users.php
    header("Location: manage_users.php");
    exit();
}

$page_title = "Edit User";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Edit User: <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h2>

    <form method="POST">
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
        <div class="mb-3">
            <label for="role_id" class="form-label">Role</label>
            <select class="form-select" id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
