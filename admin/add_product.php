<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/activity_log_functions.php");

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    echo "Access denied.";
    exit();
}

// Fetch all categories for the dropdown
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $newCategory = trim($_POST['new_category']);
    $image = $_FILES['image'];

    // If new category is entered
    if ($newCategory) {
        // Check if it already exists (case insensitive)
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$newCategory]);
        $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCategory) {
            $category_id = $existingCategory['id']; // Use existing category ID
        } else {
            // Insert new category
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$newCategory]);
            $category_id = $pdo->lastInsertId();
        }
    } elseif (!$category_id) {
        echo "<div class='alert alert-danger'>Please select a category or enter a new one.</div>";
        exit();
    }

    // Image validation
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imageExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExtension, $allowedExtensions)) {
        echo "<div class='alert alert-danger'>Invalid image format! Only JPG, JPEG, PNG, and GIF are allowed.</div>";
        exit();
    }

    if ($image['size'] > 5000000) {
        echo "<div class='alert alert-danger'>Image size is too large. Max size is 5MB.</div>";
        exit();
    }

    $imgName = time() . "_" . $image['name'];
    $uploadDir = "../assets/img/";

    if (!move_uploaded_file($image['tmp_name'], $uploadDir . $imgName)) {
        echo "<div class='alert alert-danger'>Failed to upload image.</div>";
        exit();
    }

    // Insert product
    $stmt = $pdo->prepare("INSERT INTO product (name, description, image, category_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $imgName, $category_id]);

    // Log the activity
    $user_id = $_SESSION['user']['id'];
    $product_id = $pdo->lastInsertId();

    // Get category name for logging
    $categoryName = '';
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $catResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($catResult) {
        $categoryName = $catResult['name'];
    }
    
    $activity = "Added new product '$name' (ID: $product_id) under category '$categoryName'";
    if (!empty($newCategory) && strtolower($newCategory) === strtolower($categoryName)) {
        $activity .= " (new category created)";
    }
    
    log_activity($pdo, $user_id, $activity);

    echo "<div class='alert alert-success'>Product added successfully!</div>";
}

$page_title = "Add Product";
?>

<?php include('../includes/header.php'); ?>

<!-- <link rel="stylesheet" href="../assets/css/style.css"> -->

<div class="container mt-5">
    <h2 class="mb-4">Add Product</h2>

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <div class="mb-3">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" name="name" id="productName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="productDescription" class="form-label">Description</label>
            <textarea name="description" id="productDescription" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="productCategory" class="form-label">Category</label>
            <select name="category_id" id="productCategory" class="form-select">
                <option value="">Select a Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="newCategory" class="form-label">Or Add New Category</label>
            <input type="text" name="new_category" id="newCategory" class="form-control" placeholder="Enter new category name">
        </div>

        <div class="mb-3">
            <label for="productImage" class="form-label">Product Image</label>
            <input type="file" name="image" id="productImage" accept="image/*" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>

    <div class="mt-4">
        <a href="manage_products.php" class="btn btn-secondary">Back to Manage Products</a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
