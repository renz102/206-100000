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
    echo json_encode(['success' => false, 'message' => 'Purchase ID missing.']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM purchases WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $purchase_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Purchase not found.']);
    exit;
}

echo json_encode(['success' => true, 'item' => $item]);
$stmt->close();
$conn->close();