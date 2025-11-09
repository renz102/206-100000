<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Only allow admins to update
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$purchase_id = $_POST['purchase_id'] ?? null;
$new_status = $_POST['status'] ?? null;

if (!$purchase_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$stmt = $conn->prepare("UPDATE purchases SET status=? WHERE id=?");
$stmt->bind_param("si", $new_status, $purchase_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Status updated.']);
