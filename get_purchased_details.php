<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$purchase_id = $_GET['id'] ?? null;

if (!$purchase_id) {
    echo json_encode(['success' => false, 'message' => 'No purchase ID provided.']);
    exit;
}

// Fetch the purchase details
$stmt = $conn->prepare("
    SELECT product_name, price, status, fullname, email, address, payment_method, created_at
    FROM purchases 
    WHERE id=? AND user_id=?
");
$stmt->bind_param("ii", $purchase_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'purchase' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Purchase not found.']);
}

$stmt->close();
$conn->close();
?>
