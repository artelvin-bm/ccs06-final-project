# Sentiment Analysis System for Product Reviews

A PHP + MySQL web application where users submit product reviews, receive automatic sentiment classification (Positive / Negative / Neutral), and interact with other reviews through likes/dislikes. Admins can manage users, products, reviews, activity logs, lexicon files, and export sentiment data to CSV.

## Codebase Analysis Summary

This project is a **server-rendered PHP application** with:

- **Authentication flow** (`auth/`) for register, login, logout, and password reset.
- **Role-based access control** using `users.role_id`:
  - `1 = Admin`
  - `2 = User`
- **Review sentiment classification** using a **lexicon-based algorithm** in `sentiment/analyze.php`.
- **AJAX-powered interactions**:
  - submit review + sentiment response (`api/submit_review_ajax.php`)
  - review voting (`api/vote_review.php`)
  - product details lookup (`api/get_product_details.php`)
  - dashboard chart data (`api/get_sentiment_chart.php`)
- **Admin tooling** (`admin/`) for product/user/review CRUD, sentiment dashboard, lexicon editor, logs, and CSV export.
- **Activity logging** in `activity_logs` for major user/admin actions.

## Features

### User Features

- Register and login
- Submit product reviews with 1–5 star rating
- Get immediate sentiment result after submission
- Edit and delete own reviews
- View own review history and sentiment labels
- Update profile
- Vote (like/dislike) on published reviews
- Request password reset (reset link is displayed in-app for demo purposes)

### Admin Features

- View dashboard with totals and sentiment chart
- Filter sentiment chart by product
- Manage users (edit/delete)
- Manage products (add/edit/delete with image upload)
- Manage reviews (filter by sentiment, edit/delete)
- Edit positive/negative lexicon word lists
- View activity logs
- Export review + sentiment dataset to CSV

## Sentiment Analysis Logic

The classifier in `sentiment/analyze.php` works as follows:

1. Loads words from:
   - `sentiment/positive-words.txt`
   - `sentiment/negative-words.txt`
2. Tokenizes review text using regex split.
3. Counts exact token matches in both lexicons.
4. Classifies sentiment:
   - `Positive` if positive count > negative count
   - `Negative` if negative count > positive count
   - `Neutral` otherwise
5. Computes positive/negative percentages based on matched sentiment tokens.

## Tech Stack

- PHP (PDO for database access)
- MySQL / MariaDB
- Bootstrap 5 (CDN)
- Chart.js (CDN)
- Vanilla JavaScript

## Project Structure

```text
.
├── admin/        # Admin dashboard and management modules
├── api/          # AJAX endpoints
├── assets/       # CSS, JS, and uploaded images
├── auth/         # Authentication pages
├── config/       # Database connection
├── database/     # SQL schemas
├── includes/     # Shared layout/auth/logging helpers
├── sentiment/    # Sentiment analyzer + lexicon files
├── user/         # User-facing review/profile pages
├── index.php
├── view_products.php
└── view_reviews.php
```

## Requirements

- PHP 8.0+ recommended
- MySQL 8+ or MariaDB
- Web server (Apache recommended)
- Browser with JavaScript enabled

## Setup Instructions

### 1) Clone in your local web root

Because internal links are currently hardcoded with `/sentiment-analysis/...`, place the project folder as:

- `htdocs/sentiment-analysis` (XAMPP), or
- adjust base paths in `includes/header.php` and `includes/footer.php`.

### 2) Create the database

Use the latest schema:

- `database/schema_v3_05-04-2025.sql`

This creates database `sentiment_analysis`, required tables, and default roles.

### 3) Configure DB credentials

Edit `config/db.php`:

```php
$host = "localhost";
$dbname = "sentiment_analysis";
$username = "root";
$password = "";
```

### 4) Start services

- Start Apache and MySQL.
- Open: `http://localhost/sentiment-analysis/`

### 5) Create an admin account (optional)

Register normally, then promote the account:

```sql
UPDATE users SET role_id = 1 WHERE email = 'your-admin-email@example.com';
```

## Database Notes

- Multiple schema files exist in `database/`:
  - `schema.sql` (initial)
  - `schema_v2.sql` (intermediate)
  - `schema_v3_05-04-2025.sql` (current recommended)
- `sentiments` stores one row per review via unique `review_id`.
- `product_votes` stores like/dislike votes per review and user.

## Important Implementation Notes

- Password reset is demo-style: the reset link is generated and shown in-app instead of email delivery.
- Lexicon editing changes text files directly:
  - `sentiment/positive-words.txt`
  - `sentiment/negative-words.txt`
- Product image upload writes to `assets/img/`.

## Group Members

- **Uwitonze, Kevin**
- **Pangilinan, Rick Dale Thomas Y.**
- **Miranda, Art Elvin B.**
