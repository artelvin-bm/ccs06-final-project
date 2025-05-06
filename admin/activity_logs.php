<?php
include('../includes/auth_check.php');
include('../config/db.php');

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Fetch activity logs
$stmt = $pdo->prepare("SELECT al.*, u.first_name, u.last_name 
                       FROM activity_logs al 
                       JOIN users u ON al.user_id = u.id 
                       ORDER BY al.log_time DESC");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Activity Logs";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4 text-center">Activity Logs</h2>

            <?php if (empty($logs)): ?>
                <p class="text-center text-muted">No activity logs found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">User</th>
                                <th style="width: 60%;">Activity</th>
                                <th style="width: 20%;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['first_name']) ?> <?= htmlspecialchars($log['last_name']) ?></td>
                                    <td><?= htmlspecialchars($log['activity']) ?></td>
                                    <td><?= date("F j, Y, g:i a", strtotime($log['log_time'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
