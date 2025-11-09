<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'Cart item ID missing.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item removed from cart.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item.']);
}

$stmt->close();
$conn->close();
?>
