<!-- includes/navbar.php (cancelled) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sentiment Analysis</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/sentiment-analysis/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/sentiment-analysis/user/submit_review.php">Submit Review</a>
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
