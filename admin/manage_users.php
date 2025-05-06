<?php
// session_start();
include('../includes/auth_check.php');

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Fetch all users
$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, u.last_login, r.role_name AS role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manage Users";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Manage Users</h2>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role_name']) ?></td>
                        <td><?= $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never logged in' ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<?php include('../includes/footer.php'); ?>
