<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_name = $_POST['product_name'] ?? '';
$price = $_POST['price'] ?? 0;

if (!$product_name || !$price) {
    echo json_encode(['success' => false, 'message' => 'Product data missing.']);
    exit;
}

// Check if item already exists in the cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_name=?");
$stmt->bind_param("is", $user_id, $product_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Item exists — increase quantity
    $item = $result->fetch_assoc();
    $new_qty = $item['quantity'] + 1;

    $update = $conn->prepare("UPDATE cart SET quantity=?, total_price=? WHERE id=?");
    $total_price = $price * $new_qty;
    $update->bind_param("idi", $new_qty, $total_price, $item['id']);

    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity updated in cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
    }

    $update->close();
} else {
    // Insert new item
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_name, price, quantity, total_price) VALUES (?, ?, ?, 1, ?)");
    $stmt->bind_param("isdd", $user_id, $product_name, $price, $price);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to cart!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart.']);
    }
}

$stmt->close();
$conn->close();
?>
