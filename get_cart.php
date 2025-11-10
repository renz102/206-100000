<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'cart' => [], 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, product_name, price, quantity, total_price FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[] = [
        'id' => $row['id'],
        'product_name' => $row['product_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'total_price' => $row['total_price']
    ];
}

echo json_encode(['success' => true, 'cart' => $cart]);

$stmt->close();
$conn->close();
?>
