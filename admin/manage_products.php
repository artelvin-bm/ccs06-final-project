<?php
include("../includes/auth_check.php");
include("../config/db.php");

if ($_SESSION['user']['role_id'] != 1) {
    echo "Access denied.";
    exit();
}

// Fetch all categories for filtering
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Handle search and filter
$searchQuery = '';
$categoryFilter = isset($_GET['category_id']) ? $_GET['category_id'] : '';
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $stmt = $pdo->prepare("SELECT * FROM product WHERE name LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$searchQuery%"]);
} elseif ($categoryFilter) {
    $stmt = $pdo->prepare("SELECT * FROM product WHERE category_id = ? ORDER BY created_at DESC");
    $stmt->execute([$categoryFilter]);
} else {
    $stmt = $pdo->query("SELECT * FROM product ORDER BY created_at DESC");
}

$products = $stmt->fetchAll();

$page_title = "Manage Products";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2>Manage Products</h2>

    <!-- Search and Filter Form -->
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="get" class="form-inline">
                <input type="text" name="search" class="form-control" placeholder="Search by product name" value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btn btn-primary ml-2">Search</button>
            </form>
        </div>
        <div class="col-md-4">
            <select class="form-control" id="categoryFilter">
                <option value="">Filter by Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $categoryFilter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 text-end">
            <a href="add_product.php" class="btn btn-success">Add New Product</a>
        </div>
    </div>

    <!-- Product Table -->
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <?php
                    // Fetch category name
                    $category = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                    $category->execute([$product['category_id']]);
                    $categoryName = $category->fetchColumn();
                ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</td>
                    <td><?= htmlspecialchars($categoryName) ?></td>
                    <td><?= date("F j, Y", strtotime($product['created_at'])) ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Script for Category Filter -->
 <script src="../assets/js/manage_products.js"></script>


<?php include('../includes/footer.php'); ?>
