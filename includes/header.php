<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Sentiment Analysis System</title> -->
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . " | Sentiment Analysis" : "Sentiment Analysis System" ?></title>
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/sentiment-analysis/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header>
    <link rel="stylesheet" href="/sentiment-analysis/assets/css/nav.css">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="/sentiment-analysis/index.php">Sentiment Analysis</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/sentiment-analysis/index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/sentiment-analysis/user/submit_review.php">Submit Review</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/sentiment-analysis/view_products.php">View Products</a>
                        </li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/sentiment-analysis/user/my_reviews.php">My Reviews</a>
                            </li>
                            <?php if ($_SESSION['user']['role_id'] == 1): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/sentiment-analysis/admin/dashboard.php">Admin Dashboard</a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/sentiment-analysis/user/profile.php">My Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/sentiment-analysis/auth/logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/sentiment-analysis/auth/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/sentiment-analysis/auth/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
