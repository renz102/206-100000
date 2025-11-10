<?php
session_start();
include('db_connect.php');

// Ensure only admins can access this page
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

// === Determine active tab ===
$active_tab = 'purchases'; // default
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['active_tab'])) {
    $active_tab = $_POST['active_tab'];
}

// === Handle form submissions ===

// Update purchase status
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['update_status'])) {
            $purchase_id = $_POST['purchase_id'];
            $new_status = $_POST['status'];
            $update = $conn->prepare("UPDATE purchases SET status = ? WHERE id = ?");
            $update->bind_param("si", $new_status, $purchase_id);
            $update->execute();
            $update->close();
        }

        // Delete user
        if (isset($_POST['delete_user'])) {
            $user_id = $_POST['user_id'];
            $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delete->bind_param("i", $user_id);
            $delete->execute();
            $delete->close();
        }

        // Update user info
        if (isset($_POST['update_user'])) {
        $user_id    = $_POST['user_id'];
        $username   = $_POST['username'];
        $email      = $_POST['email'];
        $first_name = $_POST['first_name'];
        $last_name  = $_POST['last_name'];
        $is_admin   = (int)$_POST['is_admin'];
        $phone_number = $_POST['phone_number'] ?? '';

        if (!preg_match('/^\+?\d{7,15}$/', $phone_number)) {
            $phone_number = '';
        }

        // Safe gender handling
        $allowed_genders = ['Male','Female','Other'];
        $gender = $_POST['gender'] ?? 'Other';
        if (!in_array($gender, $allowed_genders)) {
            $gender = 'Other';
        }

        $update = $conn->prepare("
            UPDATE users
            SET username=?, email=?, first_name=?, last_name=?, gender=?, is_admin=?, phone_number=?
            WHERE id=?
        ");
        $update->bind_param("sssssisi", $username, $email, $first_name, $last_name, $gender, $is_admin, $phone_number, $user_id);

        try {
            $update->execute();
            $update->close();
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), "Duplicate entry") !== false) {
                echo "<script>alert('Error: The email \"$email\" is already used by another account.');</script>";
            } else {
                echo "<script>alert('Error: ".$e->getMessage()."');</script>";
            }
        }
    }

    // Delete cart item
    if (isset($_POST['delete_cart'])) {
        $cart_id = $_POST['cart_id'];
        $delete = $conn->prepare("DELETE FROM cart WHERE id=?");
        $delete->bind_param("i", $cart_id);
        $delete->execute();
        $delete->close();
    }

    // Clear all cart items for a user
    if (isset($_POST['clear_user_cart'])) {
        $user_id = $_POST['user_id'];
        $delete = $conn->prepare("DELETE FROM cart WHERE user_id=?");
        $delete->bind_param("i", $user_id);
        $delete->execute();
        $delete->close();
    }
}

// Fetch all purchases
$purchases = $conn->query("
    SELECT 
        p.id, 
        p.product_name, 
        p.price, 
        p.quantity, 
        (p.price * p.quantity) AS total, 
        p.status, 
        p.date_purchased,
        u.username, 
        u.email
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.date_purchased DESC
");

// Fetch all users
$users = $conn->query("
    SELECT id, username, email, first_name, last_name, gender, is_admin, phone_number FROM users ");

// Fetch all cart items
$cart = $conn->query("
    SELECT 
        c.id, 
        c.product_name, 
        c.price, 
        c.quantity, 
        (c.price * c.quantity) AS total_price, 
        c.user_id, 
        u.username, 
        u.email 
    FROM cart c 
    JOIN users u ON c.user_id = u.id
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f4f6f8; }
h1 { text-align: center; }
.nav { text-align: center; margin-bottom: 20px; }
.nav button { padding: 10px 20px; margin: 0 5px; cursor: pointer; border: none; background: #333; color: #fff; border-radius: 4px; }
.nav button.active { background: #4CAF50; }
table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
th { background: #333; color: white; }
form { margin: 0; }
select, input[type=text], input[type=email] { padding: 5px; }
button.update, button.delete { padding: 6px 12px; cursor: pointer; border: none; border-radius: 4px; }
button.update { background: #4CAF50; color: white; }
button.update:hover { background: #45a049; }
button.delete { background: #f44336; color: white; }
button.delete:hover { background: #d32f2f; }
.logout { text-align: right; margin-bottom: 10px; }
.toggle-cart { background: #333; color: white; padding: 8px 12px; margin-top: 10px; border-radius: 4px; cursor: pointer; }
.cart-items { margin-top: 10px; }
.logout-btn {
    background: #333;  /* matches your delete buttons */
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.logout-btn:hover {
    background: #d32f2f;  /* darker red on hover */
}

</style>
</head>
<body>

<div class="logout">
    <form method="post" action="logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<h1>🌿 Admin Dashboard</h1>

<div class="nav">
    <button class="tab-btn" onclick="showTab('purchases')">Purchases</button>
    <button class="tab-btn" onclick="showTab('users')">Users</button>
    <button class="tab-btn" onclick="showTab('cart')">Cart</button>
</div>

<!-- Purchases Table -->
<div id="purchases" class="tab-content">
    <table>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $purchases->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>₱<?= number_format($row['price'], 2) ?> × <?= $row['quantity'] ?> = ₱<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= $row['date_purchased'] ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="purchase_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="active_tab" value="purchases">
                    <select name="status">
                        <option value="Preparing" <?= $row['status']=='Preparing'?'selected':'' ?>>Preparing</option>
                        <option value="Shipping" <?= $row['status']=='Shipping'?'selected':'' ?>>Shipping</option>
                        <option value="Delivered" <?= $row['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                    </select>
                    <button type="submit" name="update_status" class="update">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Users Table -->
<div id="users" class="tab-content" style="display:none;">
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Admin</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $users->fetch_assoc()): ?>
        <?php 
            // Use 'other' if gender is empty
            $current_gender = $row['gender'];
        ?>
        <tr>
            <form method="POST">
                <td>
                    <?= $row['id'] ?>
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                </td>
                <td>
                    <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>
                </td>
                <td>
                    <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                </td>
                <td>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" placeholder="First Name">
                    <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" placeholder="Last Name">
                </td>
                <td>
                    <input type="text" name="phone_number" value="<?= htmlspecialchars($row['phone_number']) ?>" placeholder="Phone Number">
                <td>
                    <select name="gender" required>
                        <?php
                        $options = ['Male', 'Female', 'Other'];
                        $current_gender = in_array($row['gender'], $options) ? $row['gender'] : 'Other';
                        foreach ($options as $opt) {
                            $selected = ($current_gender === $opt) ? 'selected' : '';
                            echo "<option value=\"$opt\" $selected>$opt</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select name="is_admin">
                        <option value="0" <?= $row['is_admin']==0?'selected':'' ?>>No</option>
                        <option value="1" <?= $row['is_admin']==1?'selected':'' ?>>Yes</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="active_tab" value="users">
                    <button type="submit" name="update_user" class="update">Update</button>
                    <button type="submit" name="delete_user" class="delete" onclick="return confirm('Are you sure?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Cart Table -->
<div id="cart" class="tab-content" style="display:none;">
    <?php
    $user_cart = [];
    while ($row = $cart->fetch_assoc()) {
        $user_cart[$row['user_id']][] = $row;
    }
    ?>

    <?php foreach ($user_cart as $uid => $items): ?>
        <div class="user-cart">
            <button class="toggle-cart" onclick="toggleCart(<?= $uid ?>)">
                <?= htmlspecialchars($items[0]['username']) ?>'s Cart (<?= count($items) ?> items) ▼
            </button>
            <div id="cart-<?= $uid ?>" class="cart-items" style="display:none;">
                <form method="POST" onsubmit="return confirm('Clear all items from <?= htmlspecialchars($items[0]['username']) ?>\'s cart?');">
                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                    <input type="hidden" name="active_tab" value="cart">
                    <button type="submit" name="clear_user_cart" class="delete">Clear Cart</button>
                </form>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>₱<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?> = ₱<?= number_format($item['total_price'], 2) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="active_tab" value="cart">
                                <button type="submit" name="delete_cart" class="delete" onclick="return confirm('Delete this cart item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function showTab(tabId){
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';

    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    document.querySelector('.tab-btn[onclick="showTab(\'' + tabId + '\')"]').classList.add('active');
}

// Open the last active tab after page reload
showTab('<?= $active_tab ?>');

function toggleCart(userId){
    const cartDiv = document.getElementById('cart-' + userId);
    cartDiv.style.display = (cartDiv.style.display === 'none') ? 'block' : 'none';
}

window.addEventListener('DOMContentLoaded', (event) => {
    showTab('<?= $active_tab ?>');
});

</script>

</body>
</html>
