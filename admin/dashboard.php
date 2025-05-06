<?php
include("../includes/auth_check.php");
include("../config/db.php");

if ($_SESSION['user']['role_id'] != 1) {
    echo "Access denied.";
    exit();
}

// Fetch all products for the product filter
$products = $pdo->query("SELECT id, name FROM product")->fetchAll(PDO::FETCH_ASSOC);

// Counts
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM product")->fetchColumn();
$reviewCount = $pdo->query("SELECT COUNT(*) FROM product_review_comments")->fetchColumn();

// Latest 5 reviews
$latestReviews = $pdo->query("
    SELECT r.id, r.review, r.stars, s.type AS sentiment_type, r.created_at, u.first_name, u.last_name, p.name AS product_name
    FROM product_review_comments r
    JOIN users u ON r.user_id = u.id
    JOIN product p ON r.product_id = p.id
    LEFT JOIN sentiments s ON s.review_id = r.id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

$productFilter = $_GET['product_id'] ?? '';

$page_title = "Admin Dashboard";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container py-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <form method="get" class="d-flex align-items-center">
                <label for="productFilter" class="me-2 mb-0 fw-bold">Filter by Product:</label>
                <select name="product_id" id="productFilter" class="form-select me-2">
                    <option value="">All Products</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= $product['id'] == $productFilter ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row text-white mb-5">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-6"><?= $userCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Products</h5>
                    <p class="display-6"><?= $productCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-warning h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Reviews</h5>
                    <p class="display-6"><?= $reviewCount ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sentiment Overview Chart -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Sentiment Overview</h5>
                    <canvas id="sentimentChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Links -->
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body d-grid gap-2">
                    <a href="manage_users.php" class="btn btn-outline-primary">Manage Users</a>
                    <a href="manage_products.php" class="btn btn-outline-primary">Manage Products</a>
                    <a href="manage_reviews.php" class="btn btn-outline-primary">Manage Reviews</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body d-grid gap-2">
                    <a href="add_product.php" class="btn btn-outline-primary">Add New Product</a>
                    <a href="activity_logs.php" class="btn btn-outline-primary">Activity Logs</a>
                    <a href="export_data.php" class="btn btn-outline-primary">Export Sentiment Data</a>
                    <a href="edit_lexicon.php" class="btn btn-outline-primary">Edit Lexicon</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Recent Reviews</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($latestReviews as $review): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($review['first_name']) ?></strong> on 
                                        <em><?= htmlspecialchars($review['product_name']) ?></em>: 
                                        <?= htmlspecialchars(mb_strimwidth($review['review'], 0, 50, '...')) ?>
                                        <div><small class="text-muted"><?= date('M d, Y H:i', strtotime($review['created_at'])) ?></small></div>
                                    </div>
                                    <?php if ($review['sentiment_type']): ?>
                                        <span class="badge rounded-pill 
                                            <?= $review['sentiment_type'] === 'positive' ? 'bg-success' : 
                                                 ($review['sentiment_type'] === 'negative' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                            <?= ucfirst($review['sentiment_type']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const PRODUCT_FILTER_ID = "<?= htmlspecialchars($productFilter) ?>";
</script>
<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/dashboard.js"></script>

<?php include('../includes/footer.php'); ?>
