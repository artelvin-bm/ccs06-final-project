<?php 
include("../includes/auth_check.php"); 
include("../config/db.php"); 
include("../sentiment/analyze.php"); // Include the sentiment analysis function

$page_title = "Submit Review";
?>

<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../assets/css/stars.css">

<div class="container mt-5">
    <h2>Submit a Product Review</h2>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success mt-3">
            Your profile has been updated successfully!
        </div>
    <?php endif; ?>

    <form id="reviewForm" method="POST">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id'] ?>">

        <!-- Product Dropdown -->
        <div class="mb-3">
            <label for="product_id" class="form-label">Product</label>
            <select name="product_id" class="form-control" required>
                <option value="">Select a product</option>
                <?php
                $products = $pdo->query("SELECT * FROM product")->fetchAll();
                foreach ($products as $p) {
                    // Check if the product_id is passed in the URL, and select the corresponding product
                    $selected = (isset($_GET['product_id']) && $_GET['product_id'] == $p['id']) ? 'selected' : '';
                    echo "<option value='{$p['id']}' $selected>{$p['name']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Product Details Card -->
        <div id="productDetails" class="card mb-3 d-none">
            <div class="row g-0">
                <div class="col-md-4">
                    <img id="productImage" src="" class="img-fluid rounded-start" alt="Product image">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title" id="productName"></h5>
                        <p class="card-text" id="productDescription"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Text -->
        <div class="mb-3">
            <label for="review" class="form-label">Review</label>
            <textarea name="review" class="form-control" placeholder="Your review..." required></textarea>
        </div>

        <!-- Interactive Star Rating -->
        <div class="mb-3">
            <label for="stars" class="form-label">Rating</label>
            <div class="stars" id="starRating">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <input type="hidden" name="stars" id="selectedStars" value="" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <div id="reviewResult" class="mt-3"></div>
</div>

<script src="../assets/js/main.js"></script>
<script src="../assets/js/product_dropdown.js"></script>
<script src="../assets/js/star_rating.js"></script>

<?php include("../includes/footer.php"); ?>
