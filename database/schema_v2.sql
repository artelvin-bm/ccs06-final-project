-- schema.sql

CREATE DATABASE IF NOT EXISTS sentiment_analysis;
USE sentiment_analysis;

-- Roles (admin, user)
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);

-- Insert default roles (Admin, User)
INSERT INTO roles (role_name) VALUES 
('Admin'), 
('User');

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255),
    role_id INT,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

-- Products
CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    description TEXT,
    image VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Product Reviews
CREATE TABLE product_review_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    review TEXT,
    stars INT CHECK (stars BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES product(id)
);

-- Product Votes (likes/dislikes/etc.)
CREATE TABLE product_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT,
    user_id INT,
    vote_type ENUM('like', 'dislike'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES product_review_comments(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Sentiment (Positive, Negative, Neutral) - One-to-One relationship with Reviews
CREATE TABLE sentiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT UNIQUE, -- Ensure one-to-one relationship with review
    type VARCHAR(50), -- Positive, Negative, Neutral
    positive_count INT,
    negative_count INT,
    percentage DECIMAL(5,2),
    FOREIGN KEY (review_id) REFERENCES product_review_comments(id) ON DELETE CASCADE
);

-- Activity Logs (last login, user actions)
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity TEXT,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
