<?php
require_once("../config/db.php");

// Default response structure
$response = ["positive" => 0, "negative" => 0, "neutral" => 0];

// Get the product ID from the request (if provided)
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

// Updated SQL with correct join
$query = "
    SELECT s.type, COUNT(*) AS total
    FROM product_review_comments r
    JOIN sentiments s ON s.review_id = r.id
";

if ($product_id) {
    $query .= " WHERE r.product_id = :product_id";
}

$query .= " GROUP BY s.type";

// Prepare and bind if needed
$stmt = $pdo->prepare($query);

if ($product_id) {
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
}

$stmt->execute();
$data = $stmt->fetchAll();

foreach ($data as $row) {
    $type = strtolower($row['type']);
    $response[$type] = (int)$row['total'];
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode($response);
