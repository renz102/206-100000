<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$checkout_items = $_POST['checkout_items'] ?? null;
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$paymentMethod = $_POST['paymentMethod'] ?? '';

// Validate input
if (!$checkout_items) {
    echo json_encode(['success' => false, 'message' => 'No items selected for checkout.']);
    exit;
}

$items = json_decode($checkout_items, true);
if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'No valid items selected.']);
    exit;
}

// Begin transaction to ensure atomicity
$conn->begin_transaction();

try {
    foreach ($items as $item) {
        $product_name = $item['name'];
        $price = floatval($item['price']);
        $quantity = intval($item['quantity'] ?? 1); // default to 1 if not provided
        $total = $price * $quantity;

        // Insert into purchases table
        $stmt = $conn->prepare("
            INSERT INTO purchases 
            (user_id, product_name, price, quantity, total, status, fullname, email, address, payment_method) 
            VALUES (?, ?, ?, ?, ?, 'Purchased', ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdidssss", $user_id, $product_name, $price, $quantity, $total, $fullname, $email, $address, $paymentMethod);
        $stmt->execute();
        $stmt->close();

        // Remove from cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_name=?");
        $stmt->bind_param("is", $user_id, $product_name);
        $stmt->execute();
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Purchase completed successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
}

$conn->close();
exit;
?>
