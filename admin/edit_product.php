<?php
include("../includes/auth_check.php");
include("../config/db.php");
include("../includes/activity_log_functions.php");

// Ensure only admin can access
if ($_SESSION['user']['role_id'] != 1) {
    echo "Access denied.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Product ID not specified.";
    exit();
}

$productId = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM product WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit();
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image'];

    $imgName = $product['image'];  // default to current image

    if ($image['name']) {
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

        $imgName = time() . "_" . basename($image['name']);
        $uploadDir = "../assets/img/";

        if (!move_uploaded_file($image['tmp_name'], $uploadDir . $imgName)) {
            echo "<div class='alert alert-danger'>Failed to upload image.</div>";
            exit();
        }

        // Delete old image if a new one was uploaded
        if ($imgName !== $product['image']) {
            $oldImagePath = $uploadDir . $product['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    // Retrieve the old values before the update
    $oldProduct = $pdo->prepare("SELECT name, description, category_id, image FROM product WHERE id = ?");
    $oldProduct->execute([$productId]);
    $oldProductData = $oldProduct->fetch(PDO::FETCH_ASSOC);

    // Check if any field has changed and log the differences
    $logDetails = [];
    if ($oldProductData['name'] !== $name) {
        $logDetails[] = "Name changed from '{$oldProductData['name']}' to '$name'";
    }
    if ($oldProductData['description'] !== $description) {
        $logDetails[] = "Description changed from '{$oldProductData['description']}' to '{$description}'";
    } 
    if ($oldProductData['category_id'] !== $category_id) {
        // Only log category change if the category was indeed updated (i.e., if the category_id is different)
        $oldCategoryNameStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $oldCategoryNameStmt->execute([$oldProductData['category_id']]);
        $oldCategory = $oldCategoryNameStmt->fetch(PDO::FETCH_ASSOC);
    
        $newCategoryNameStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $newCategoryNameStmt->execute([$category_id]);
        $newCategory = $newCategoryNameStmt->fetch(PDO::FETCH_ASSOC);
    
        // Only log this if the category has actually changed
        if ($oldCategory['name'] !== $newCategory['name']) {
            $logDetails[] = "Category changed from '{$oldCategory['name']}' to '{$newCategory['name']}'";
        }
    }
    if ($oldProductData['image'] !== $imgName) {
        $logDetails[] = "Image updated.";
    }

    // If there were changes, log them
    if (!empty($logDetails)) {
        $logMessage = implode(", ", $logDetails);

        // Log the activity with detailed changes
        $user_id = $_SESSION['user']['id'];
        $user_name = $_SESSION['user']['first_name'] . " " . $_SESSION['user']['last_name']; // Assuming the user's name is available
        $activity = "Updated product (ID: $productId). Changes: $logMessage";
        
        // Log to activity_logs table
        try {
            log_product_edit_activity($_SESSION['user']['id'], $productId, $logMessage);
        } catch (Exception $e) {
            error_log("Error logging product update: " . $e->getMessage());
        }
    }

    // Update product
    $stmt = $pdo->prepare("UPDATE product SET name = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$name, $description, $imgName, $category_id, $productId]);

    $_SESSION['success'] = "Product updated successfully!";
    header("Location: manage_products.php");
    exit();
}

$page_title = "Edit Product";
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Edit Product</h2>

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <div class="mb-3">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" name="name" id="productName" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="productDescription" class="form-label">Description</label>
            <textarea name="description" id="productDescription" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="productCategory" class="form-label">Category</label>
            <select name="category_id" id="productCategory" class="form-select" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <?php if ($product['image']): ?>
                <img src="../assets/img/<?= htmlspecialchars($product['image']) ?>" alt="Product Image" style="max-height: 200px;" class="mb-2 d-block">
            <?php else: ?>
                <span class="text-muted">No image uploaded.</span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="productImage" class="form-label">Upload New Image (optional)</label>
            <input type="file" name="image" id="productImage" accept="image/*" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>

    <div class="mt-4">
        <a href="manage_products.php" class="btn btn-secondary">Back to Manage Products</a>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
