<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Please log in first.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_name = $_POST['product_name'] ?? '';
$price = $_POST['price'] ?? 0;

if(!$product_name || !$price){
    echo json_encode(['success'=>false,'message'=>'Product data missing.']);
    exit;
}

// Check if item already exists in cart
$stmt = $conn->prepare("SELECT id FROM cart WHERE user_id=? AND product_name=?");
$stmt->bind_param("is", $user_id, $product_name);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    $stmt->close();
    echo json_encode(['success'=>false,'message'=>'Item already in cart!']);
    exit;
}
$stmt->close();

// Insert into cart
$stmt = $conn->prepare("INSERT INTO cart (user_id, product_name, price) VALUES (?, ?, ?)");
$stmt->bind_param("isd", $user_id, $product_name, $price);
if($stmt->execute()){
    echo json_encode(['success'=>true,'message'=>'Added to cart!']);
} else {
    echo json_encode(['success'=>false,'message'=>'Failed to add to cart.']);
}
$stmt->close();
$conn->close();
?>
