<?php
$host = "localhost";
$dbname = "sentiment_analysis";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set character set
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
