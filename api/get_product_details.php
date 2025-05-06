<?php
require_once("../config/db.php");

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Missing product ID"]);
    exit;
}

$stmt = $pdo->prepare("SELECT name, description, image FROM product WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if ($product) {
    echo json_encode($product);
} else {
    echo json_encode(["error" => "Product not found"]);
}
