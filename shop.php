<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $checkout_items = $_POST['checkout_items'] ?? '';

    if (empty($checkout_items)) {
        echo json_encode(['success' => false, 'message' => 'No items selected for checkout.']);
        exit;
    }

    $items = json_decode($checkout_items, true);
    if (!is_array($items)) {
        echo json_encode(['success' => false, 'message' => 'Invalid checkout data.']);
        exit;
    }

    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $paymentMethod = $_POST['paymentMethod'] ?? '';

    // Prepare once, execute multiple times
    $stmt = $conn->prepare("
    UPDATE purchases 
        SET status='Purchased', fullname=?, email=?, address=?, payment_method=?, price=? 
        WHERE user_id=? AND product_name=? AND status='In Cart'
    ");
    $stmt->bind_param("ssssdis", $fullname, $email, $address, $paymentMethod, $price, $user_id, $product_name);


    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    foreach ($items as $item) {
        $product_name = $item['name'] ?? '';
        $price = $item['price'] ?? 0;

        $stmt = $conn->prepare("
            UPDATE purchases 
            SET status='Purchased', fullname=?, email=?, address=?, payment_method=?, price=? 
            WHERE user_id=? AND product_name=? AND status='In Cart'
        ");
        $stmt->bind_param("ssssdis", $fullname, $email, $address, $paymentMethod, $price, $user_id, $product_name);
        $stmt->execute();
    }


    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Purchase completed successfully.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop | BLOOMS</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #faf6ef;
      color: #3e3b32;
      overflow-x: hidden;
    }

    /* HEADER */
    header {
      background: #e4d9c5;
      padding: 25px 35px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #c8bfa9;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
    }

    footer {
      background: #e4d9c5;
      text-align: center;
      padding: 15px;
      font-size: 14px;
      color: #4a443b;
      border-top: 1px solid #c8bfa9;
      margin-top: 50px;
    }

    .burger { width: 30px; height: 22px; cursor: pointer; position: relative; }
    .burger span {
      position: absolute; width: 30px; height: 4px;
      background: #4f5a3d; left: 0; transition: all 0.4s ease;
    }
    .burger span:nth-child(1){ top:0; }
    .burger span:nth-child(2){ top:8px; }
    .burger span:nth-child(3){ top:16px; }
    .burger.active span:nth-child(1){ transform:rotate(45deg); top:8px; }
    .burger.active span:nth-child(2){ opacity:0; }
    .burger.active span:nth-child(3){ transform:rotate(-45deg); top:8px; }

    .logo {
      position: absolute; left: 50%; transform: translateX(-50%);
      font-family: 'Playfair Display', serif;
      color: #4f5a3d; font-size: 50px; letter-spacing: 1px;
      cursor: pointer; transition: color 0.3s;
    }
    .logo:hover { color: #7b8e5c; }

    .auth-buttons { display: flex; gap: 10px; }
    .auth-buttons button {
      background: #4f5a3d; color: #fff; border: none;
      padding: 7px 14px; border-radius: 5px; cursor: pointer;
      font-size: 14px; transition: background 0.3s;
    }
    .auth-buttons button:hover { background: #7b8e5c; }

    .profile-circle {
      width: 40px; height: 40px; border-radius: 50%;
      border: 2px solid #4f5a3d; object-fit: cover;
    }
    .user-info { display: flex; align-items: center; gap: 10px; margin-top: -3px; }

    /* NAV MENU */
    .nav-menu {
      position: fixed; top: 0; left: -15%; width: 15%; height: 100%;
      background: #e4d9c5; display: flex; flex-direction: column;
      align-items: flex-start; padding-top: 100px; padding-left: 25px;
      transition: left 0.4s ease; z-index: 900; border-right: 1px solid #c8bfa9;
    }
    .nav-menu.active { left: 0; }
    .nav-menu a {
      color: #4f5a3d; text-decoration: none; font-size: 20px;
      margin: 15px 0; transition: color 0.3s;
    }
    .nav-menu a:hover { color: #7b8e5c; }

    /* SHOP SECTION */
    .shop-container {
      padding-top: 140px;
      max-width: 1000px;
      margin: auto;
      text-align: center;
    }
    .shop-container h2 { color: #4f5a3d; font-size: 32px; margin-bottom: 40px; }

    .product-grid {
      display: grid; grid-template-columns: repeat(2, 1fr);
      gap: 30px; justify-items: center;
    }
    .product {
      background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      overflow: hidden; width: 90%; transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
    }
    .product:hover { transform: translateY(-5px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
    .product img { width: 100%; height: 250px; object-fit: cover; }
    .product p { padding: 15px; font-size: 16px; color: #3e3b32; }

    /* OVERLAY */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      backdrop-filter: blur(6px);
      background: rgba(0, 0, 0, 0.3);
      z-index: 1800;
    }

    /* POPUP */
    .popup {
      display: none;
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.3);
      z-index: 2000;
      width: 80%;
      max-width: 1100px;
      display: flex;
      gap: 25px;
      animation: fadeIn 0.3s ease;
    }
    .popup img { width: 45%; border-radius: 10px; object-fit: cover; }
    .popup-content { flex: 1; text-align: justify; }
    .popup-buttons { margin-top: 20px; display: flex; gap: 10px; }
    .popup-buttons button {
      background: #4f5a3d; color: #fff; border: none;
      padding: 10px 20px; border-radius: 5px;
      cursor: pointer; font-size: 16px; transition: background 0.3s;
    }
    .popup-buttons button:hover { background: #7b8e5c; }
    .close-popup {
      position: absolute; top: 15px; right: 20px;
      cursor: pointer; font-size: 20px;
    }

    /* CART ICON */
    .cart-icon {
      position: fixed; bottom: 25px; right: 25px;
      background: #4f5a3d;
      color: #fff;
      padding: 15px 18px;
      border-radius: 50%;
      font-size: 22px;
      cursor: pointer;
      z-index: 1200;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
      transition: background 0.3s;
    }
    .cart-icon:hover { background: #7b8e5c; }

    /* CART POPUP */
    .cart-popup {
      display: none;
      position: fixed;
      bottom: 90px; right: 25px;
      background: #fff;
      border: 1px solid #c8bfa9;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.25);
      padding: 20px;
      width: 300px;
      z-index: 1500;
    }
    .cart-popup h4 { margin-bottom: 10px; }
    .cart-item { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; }
    .cart-item button {
      background: none; border: none; color: red; cursor: pointer; font-size: 14px;
    }
    .cart-popup button.checkout {
      width: 100%; margin-top: 10px;
      background: #4f5a3d; color: white; border: none;
      padding: 8px; border-radius: 6px; cursor: pointer;
    }
    .cart-popup button.checkout:hover { background: #7b8e5c; }

    @keyframes fadeIn {
      from { opacity: 0; transform: translate(-50%, -48%); }
      to { opacity: 1; transform: translate(-50%, -50%); }
    }

    @media (max-width: 768px) {
      .product-grid { grid-template-columns: 1fr; }
      .popup { flex-direction: column; align-items: center; }
      .popup img { width: 100%; }
    }

    #paymentPopup {
      width: 90%;
      max-width: 600px; /* adjust as needed */
      flex-direction: column;
      padding: 20px;
    }


  .left-header {
    display: flex;
    align-items: center;
    gap: 8px; /* space between burger and button */
  }

.purchased-btn {
  background: #4f5a3d;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
  margin-left: 10px;
}

.purchased-btn:hover {
  background: #7b8e5c;
}

/* PURCHASED ITEMS STYLING */
.purchased-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f5f1e9;
  border: 1px solid #c8bfa9;
  border-radius: 10px;
  padding: 10px 15px;
  margin-bottom: 10px;
}

.status {
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 5px;
}

.status.preparing { background: #f7e6b5; color: #5c4a00; }
.status.shipping { background: #b5e6f7; color: #004a5c; }
.status.delivered { background: #b5f7c1; color: #004c18; }

.status-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #4f5a3d;
  transition: color 0.3s;
}
.status-btn:hover {
  color: #7b8e5c;
}

.status.preparing { color: white; font-weight: bold; }
.status.shipping { color: white; font-weight: bold; }
.status.delivered { color: white; font-weight: bold; }
.status.cancelled { color: white; font-weight: bold; }

.status {
  padding: 2px 6px;
  border-radius: 4px;
  color: #d4d3f2ff;
  font-weight: bold;
}

.status.preparing {
  background-color: orange;
}

.status.shipping {
  background-color: blue;
}

.status.delivered {
  background-color: green;
}

.status.cancelled {
  background-color: red;
}

/* PURCHASE DETAILS POPUP */
#purchaseDetails {
  display: flex;
  flex-direction: column;
  gap: 15px;
  padding: 15px;
  background: #faf6ef;
  border-radius: 10px;
  max-height: 70vh;
  overflow-y: auto;
}

#purchaseInfo h3 {
  margin-bottom: 10px;
  color: #4f5a3d;
}

#purchaseInfo p {
  margin-bottom: 8px;
  line-height: 1.5;
  color: #3e3b32;
}

#purchaseInfo label {
  font-weight: bold;
  margin-right: 8px;
}

#purchaseInfo select {
  padding: 6px 10px;
  border-radius: 5px;
  border: 1px solid #c8bfa9;
  background: #fff;
  margin-right: 10px;
}

#purchaseInfo button {
  padding: 6px 12px;
  border-radius: 5px;
  border: none;
  background: #4f5a3d;
  color: #fff;
  cursor: pointer;
  transition: background 0.3s;
}

#purchaseInfo button:hover {
  background: #7b8e5c;
}

#purchaseDetails .status {
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: bold;
  margin-left: 20px;
}

/* Scrollbar styling for popup if content is long */
#purchaseDetails::-webkit-scrollbar {
  width: 6px;
}
#purchaseDetails::-webkit-scrollbar-thumb {
  background-color: rgba(79, 90, 61, 0.5);
  border-radius: 3px;
}

</style>
</head>
<body>

<header>
  <div class="left-header">
  <div class="burger" id="burger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>

  <?php if(isset($_SESSION['username'])): ?>
    <button onclick="showPurchasedPopup()" class="purchased-btn">Purchased</button>
  <?php endif; ?>
</div>

  <h1 class="logo" onclick="window.location='index.php#home'">BLOOMS</h1>

  <div class="auth-buttons">
  <?php if(isset($_SESSION['username'])): ?>
    <div class="user-info">
      <?php
        $gender = $_SESSION['gender'] ?? 'Male';
        $img = $_SESSION['profile_pic'] ?? (($gender === 'Female') ? 'female1.png' : 'male1.png');
      ?>
      <img src="<?= htmlspecialchars($img); ?>" alt="Profile" class="profile-circle">
      <span>Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?: $_SESSION['username']); ?>!</span>
      <a href="logout.php"><button>Logout</button></a>
    </div>
    
  <?php else: ?>
    <button onclick="window.location='index.php#loginPopup'">Log In</button>
    <button onclick="window.location='index.php#signupPopup'">Sign Up</button>
  <?php endif; ?>
  </div>
</header>

<div class="nav-menu" id="navMenu">
  <?php if(isset($_SESSION['username'])): ?><a href="index.php#profile">Profile</a><?php endif; ?>
  <a href="index.php#home">Home</a>
  <a href="index.php#about">About Us</a>
  <a href="shop.php" class="active">Shop</a>
  <a href="index.php#contact">Contacts</a>
</div>

<section class="shop-container">
  <h2>Our Collection 🌸</h2><br>
  <div class="product-grid">
    <div class="product" onclick="openPopup('Rare Orchid', '₱5,999', 'https://als-gardencenter.com/cdn/shop/articles/moth_orchid.jpg?v=1704745423', 'A rare orchid kissed by moonlight, its petals whisper secrets of solitude and grace. Nurtured under veils of dew, it blooms only for those who listen to silence.')">
      <img src="https://als-gardencenter.com/cdn/shop/articles/moth_orchid.jpg?v=1704745423" alt="Rare Orchid">
      <p><strong>Rare Orchid</strong><br>Exotic bloom nurtured with care. ₱5,999</p>
    </div>
    <div class="product" onclick="openPopup('Blue Passion Flower', '₱6,499', 'https://www.gardenia.net/wp-content/uploads/2023/05/Passiflora-Blue-Bouquet-Passion-Flower.webp', 'A tangle of blue dreams—wild, burning, eternal. The passion flower carries the scent of forgotten heavens, daring to be seen by mortal eyes.')">
      <img src="https://www.gardenia.net/wp-content/uploads/2023/05/Passiflora-Blue-Bouquet-Passion-Flower.webp" alt="Blue Passion Flower">
      <p><strong>Blue Passion Flower</strong><br>Unique, vibrant, and mesmerizing. ₱6,499</p>
    </div>
    <div class="product" onclick="openPopup('Juniper-leaf Grevillea', '₱2,999', 'https://www.petalrepublic.com/wp-content/uploads/2023/02/Juniper-leaf-Grevillea.jpeg', 'Born from dust, unbroken by drought, this cactus thrives where others fade. Its thorns are its poetry—written in defiance of the desert.')">
      <img src="https://www.petalrepublic.com/wp-content/uploads/2023/02/Juniper-leaf-Grevillea.jpeg" alt="Cactus Succulent">
      <p><strong>Juniper-leaf Grevillea</strong><br>Hardy, easy to care for, and rare. ₱2,999</p>
    </div>
    <div class="product"><img src="https://png.pngtree.com/png-clipart/20190520/original/pngtree-question-mark-vector-icon-png-image_3722522.jpg"><p><strong>Coming Soon...</strong><br>Stay tuned for our next exotic wonder.</p></div>
    <div class="product"><img src="https://png.pngtree.com/png-clipart/20190520/original/pngtree-question-mark-vector-icon-png-image_3722522.jpg"><p><strong>Coming Soon...</strong><br>Stay tuned for our next exotic wonder.</p></div>
    <div class="product"><img src="https://png.pngtree.com/png-clipart/20190520/original/pngtree-question-mark-vector-icon-png-image_3722522.jpg"><p><strong>Coming Soon...</strong><br>Stay tuned for our next exotic wonder.</p></div>
  </div>
</section>

<div class="overlay" id="overlay" onclick="closeAllPopups()"></div>

<!-- PRODUCT POPUP -->
<div class="popup" id="popup">
  <span class="close-popup" onclick="closePopup()">✕</span>
  <img id="popupImg" src="">
  <div class="popup-content">
    <h3 id="popupTitle"></h3>
    <br>
    <p id="popupDesc"></p>
    <br><p><strong id="popupPrice"></strong></p>
    <br><br><div class="popup-buttons">
      <button onclick="buyNow()">Buy Now</button>
      <button onclick="addToCart(selectedProduct)">Add to Cart</button>
    </div>
  </div>
</div>

<!-- CART ICON -->
<div class="cart-icon" onclick="toggleCart()">🛒</div>

<!-- CART POPUP -->
<div class="cart-popup" id="cartPopup">
  <h4>Your Cart</h4>
  <div id="cartItems"></div>
  <button class="checkout" onclick="proceedToPayment()">Proceed to Payment</button>
</div>

<!-- PAYMENT POPUP -->
<div class="overlay" id="paymentOverlay" onclick="closePaymentPopup()"></div>

<div class="popup" id="paymentPopup" style="flex-direction: column;">
  <span class="close-popup" onclick="closePaymentPopup()">✕</span>
  <h3>Confirm Your Purchase</h3>

  <div id="selectedItemsList" style="margin-bottom:15px;"></div>

  <!-- Hidden input for selected cart items -->
  <input type="hidden" name="checkout_items" id="checkoutItems">
  
  <form id="paymentForm" method="POST" style="width:100%;">
    <label>Full Name:</label>
    <input type="text" id="fullname" name="fullname" required style="width:100%;padding:8px;margin:6px 0;"><br>

    <label>Email:</label>
    <input type="email" id="email" name="email" required style="width:100%;padding:8px;margin:6px 0;"><br>

    <label>Address:</label>
    <textarea id="address" name="address" required style="width:100%;padding:8px;margin:6px 0;"></textarea><br>

    <label>Payment Method:</label>
    <select id="paymentMethod" name="paymentMethod" required style="width:100%;padding:8px;margin:6px 0;">
      <option value="">Select</option>
      <option value="gcash">GCash</option>
      <option value="card">Credit/Debit Card</option>
      <option value="cod">Cash on Delivery</option>
    </select><br><br>

    <button type="submit" style="background:#4f5a3d;color:white;border:none;padding:10px 15px;border-radius:6px;cursor:pointer;width:100%;">Confirm & Pay</button>
  </form>
</div>

<!-- PURCHASED ITEMS POPUP -->
<div class="overlay" id="purchasedOverlay" onclick="closePurchasedPopup()"></div>

<div class="popup" id="purchasedPopup" style="flex-direction: column; max-width: 600px;">
  <span class="close-popup" onclick="closePurchasedPopup()">✕</span>
  <h2 id="purchasedTitle">Purchased Items</h2>

  <!-- List of purchased items -->
  <div id="purchasedItemsList" style="margin-top:10px;"></div>

  <!-- Detailed view -->
  <div id="purchaseDetails" style="display:none; margin-top: 10px;">
    <div id="purchaseInfo"></div>
  </div>
</div>

<!-- Toast notification -->
<div id="toast" style="
  display: none;
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #333;
  color: #fff;
  padding: 12px 20px;
  border-radius: 5px;
  font-size: 15px;
  z-index: 9999;
  opacity: 0;
  transition: opacity 0.4s ease;
"></div>

<footer>
  &copy; 2025 Exotic Blooms. All Rights Reserved.
</footer>

<script>
// ---------- MENU ----------
function toggleMenu() {
  document.getElementById("burger").classList.toggle("active");
  document.getElementById("navMenu").classList.toggle("active");
}

// ---------- GLOBAL ----------
let selectedProduct = null;
let currentPurchaseId = null;

// ---------- POPUPS ----------
function openPopup(name, price, img, desc) {
  selectedProduct = { name, price, image: img };
  document.getElementById("popupTitle").textContent = name;
  document.getElementById("popupPrice").textContent = price;
  document.getElementById("popupImg").src = img;
  document.getElementById("popupDesc").textContent = desc;
  document.getElementById("overlay").style.display = "block";
  document.getElementById("popup").style.display = "flex";
}

function closePopup() {
  document.getElementById("popup").style.display = "none";
  document.getElementById("overlay").style.display = "none";
}

function closePaymentPopup() {
  document.getElementById("paymentOverlay").style.display = "none";
  document.getElementById("paymentPopup").style.display = "none";
}

function closePurchasedPopup() {
  document.getElementById("purchasedPopup").style.display = "none";
  document.getElementById("purchasedOverlay").style.display = "none";
}

// ---------- CART ----------
function toggleCart() {
  const cartPopup = document.getElementById("cartPopup");
  cartPopup.style.display = (cartPopup.style.display === "block") ? "none" : "block";
  loadCartItems(); // always fetch fresh data from server
}

// Load cart items
async function loadCartItems() {
  const container = document.getElementById("cartItems");
  container.innerHTML = "Loading...";

  try {
    const res = await fetch('get_cart.php');
    const data = await res.json();

    if (!data.success || !data.cart || data.cart.length === 0) {
      container.innerHTML = "<p>Your cart is empty.</p>";
      return;
    }

    container.innerHTML = "";

    data.cart.forEach(item => {
      const div = document.createElement("div");
      div.className = "cart-item";
      div.id = `cart-item-${item.id}`;

      const totalPrice = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);

      div.innerHTML = `
        <input type="checkbox" class="cart-check" style="margin-right:10px;">
        <div style="display:flex;flex-direction:column;width:100%;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span>${item.product_name}</span>
            <button onclick="removeFromCart(${item.id})" style="color:red;">✕</button>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
            <div>
              <button onclick="changeQuantity(${item.id}, -1, this)" style="padding:2px 6px;">−</button>
              <span id="qty-${item.id}">${item.quantity}</span>
              <button onclick="changeQuantity(${item.id}, 1, this)" style="padding:2px 6px;">+</button>
            </div>
            <span id="total-${item.id}" data-price="${item.price}">₱${totalPrice}</span>
          </div>
        </div>
      `;

      container.appendChild(div);
    });

  } catch (err) {
    container.innerHTML = "<p>Error loading cart.</p>";
    console.error(err);
  }
}

// Dynamic quantity change
async function changeQuantity(cartId, change, btn) {
  const qtySpan = document.getElementById(`qty-${cartId}`);
  const totalSpan = document.getElementById(`total-${cartId}`);
  if (!qtySpan || !totalSpan) return;

  let currentQty = parseInt(qtySpan.textContent);
  let newQty = currentQty + change;
  if (newQty < 1) return;

  const pricePerItem = parseFloat(totalSpan.dataset.price);

  // Disable buttons while updating
  const parentDiv = btn.closest('div');
  const buttons = parentDiv.querySelectorAll('button');
  buttons.forEach(b => b.disabled = true);

  try {
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('quantity', newQty);

    const res = await fetch('update_cart_quantity.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      qtySpan.textContent = newQty;
      totalSpan.textContent = `₱${(pricePerItem * newQty).toFixed(2)}`;
    } else {
      showToast(data.message || "Failed to update quantity.", "error");
    }

  } catch (err) {
    console.error(err);
    showToast("Network error while updating quantity.", "error");
  } finally {
    buttons.forEach(b => b.disabled = false);
  }
}

// Initial load
document.addEventListener('DOMContentLoaded', loadCartItems);

// Remove item from database cart
async function removeFromCart(cartId) {
  try {
    const formData = new FormData();
    formData.append('cart_id', cartId);

    const res = await fetch('remove_from_cart.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (!data.success) {
      alert(data.message);
      return;
    }

    loadCartItems(); // refresh cart
  } catch (err) {
    alert("Failed to remove item.");
    console.error(err);
  }
}

// ---------- BUY NOW ----------
function buyNow() {
  if (!<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
    showToast("Please log in to buy this item.", "error");
    return;
  }

  // Prefill the payment popup
  const summary = document.getElementById("selectedItemsList");
  summary.innerHTML = `<p>${selectedProduct.name} - ${selectedProduct.price}</p>`;

  // Set hidden input for checkout
  document.getElementById("checkoutItems").value = JSON.stringify([{
    name: selectedProduct.name,
    price: parseFloat(selectedProduct.price.replace(/[^0-9.-]+/g,""))
  }]);

  // Reset form fields
  document.getElementById('fullname').value = '';
  document.getElementById('email').value = '';
  document.getElementById('address').value = '';
  document.getElementById('paymentMethod').value = '';

  // Show payment popup
  document.getElementById("paymentOverlay").style.display = "block";
  document.getElementById("paymentPopup").style.display = "flex";

  closePopup();
}

// ---------- ADD TO CART ----------
function addToCart(product) {
  if (!<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
    showToast("Please log in to add items to cart.", "error");
    return;
  }

  const formData = new FormData();
  formData.append('product_name', product.name);
  formData.append('price', parseFloat(selectedProduct.price.replace(/[^0-9.-]+/g,"").replace(/,/g,"")));

  fetch('add_to_cart.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast("Successfully added to cart!");
      loadCartItems(); // refresh cart
    } else {
      showToast(data.message || "Failed to add to cart.", "error");
    }
  })
  .catch(() => showToast("Network error occurred.", "error"));
}

// ---------- TOAST ----------
function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  toast.textContent = message;

  if (type === "error") {
    toast.style.background = "#e74c3c";
  } else if (type === "info") {
    toast.style.background = "#3498db";
  } else {
    toast.style.background = "#2ecc71";
  }

  toast.style.display = "block";
  setTimeout(() => (toast.style.opacity = "1"), 50);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => (toast.style.display = "none"), 400);
  }, 3000);
}

// ---------- CART CHECKOUT ----------
function proceedToPayment() {
  // Get all checked items
  const checkedItems = document.querySelectorAll('.cart-item .cart-check:checked');

  if (checkedItems.length === 0) {
    alert("Please select at least one item to purchase.");
    return;
  }

  const selectedItems = [];

  checkedItems.forEach(check => {
    const div = check.closest('.cart-item');
    const name = div.querySelector('span').innerText; // product name

    // Get quantity
    const qtySpan = div.querySelector('span[id^="qty"]');
    const quantity = qtySpan ? parseInt(qtySpan.textContent) : 1;

    // Get price per item from data-price attribute
    const totalSpan = div.querySelector(`#total-${div.id.split('-')[2]}`);
    const pricePerItem = totalSpan ? parseFloat(totalSpan.dataset.price) : 0;

    selectedItems.push({ name, quantity, price: pricePerItem });
  });

  // Show summary in payment popup
  const summary = document.getElementById("selectedItemsList");
  summary.innerHTML = "";
  selectedItems.forEach(item => {
    summary.innerHTML += `<p>${item.name} - Qty: ${item.quantity} - ₱${(item.price * item.quantity).toFixed(2)}</p>`;
  });

  // Save items for checkout (JSON string)
  document.getElementById("checkoutItems").value = JSON.stringify(selectedItems);

  // Reset form fields
  document.getElementById('fullname').value = '';
  document.getElementById('email').value = '';
  document.getElementById('address').value = '';
  document.getElementById('paymentMethod').value = '';

  // Show payment popup
  document.getElementById("paymentOverlay").style.display = "block";
  document.getElementById("paymentPopup").style.display = "flex";
}

// ---------- CHECKOUT FUNCTION ----------
async function checkoutItems() {
  const form = document.getElementById("paymentForm");
  const formData = new FormData(form);
  formData.append("checkout_items", document.getElementById("checkoutItems").value);

  try {
    const response = await fetch("checkout.php", {
      method: "POST",
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      showToast(data.message || "Purchase completed successfully!");
      closePaymentPopup();
      loadCartItems(); // refresh cart
      document.getElementById("checkoutItems").value = ""; // reset
      form.reset(); // clear form fields
    } else {
      showToast(data.message || "Checkout failed.", "error");
    }
  } catch (err) {
    console.error(err);
    showToast("An error occurred during checkout.", "error");
  }
}

document.getElementById("paymentForm").addEventListener("submit", function(e) {
  e.preventDefault();
  if (!<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
    showToast("Please log in to purchase items.");
    return;
  }
  checkoutItems(); // call our reusable function
});

// ---------- PURCHASED POPUP ----------
async function showPurchasedPopup() {
  document.getElementById("purchasedPopup").style.display = "flex";
  document.getElementById("purchasedOverlay").style.display = "block";
  document.getElementById("purchaseDetails").style.display = "none";
  document.getElementById("purchasedItemsList").style.display = "block";

  const listContainer = document.getElementById("purchasedItemsList");
  listContainer.innerHTML = "Loading...";

  try {
    const res = await fetch('get_purchased.php');
    const data = await res.json();

    if (!data.success || data.items.length === 0) {
      listContainer.innerHTML = "<p>You haven't purchased anything yet.</p>";
      return;
    }

    listContainer.innerHTML = "";
    data.items.forEach(item => {
      const div = document.createElement("div");
      div.className = "purchased-item";

      div.innerHTML = `
        <span>${item.product_name}</span>
        <span class="status ${item.status.toLowerCase()}">${item.status}</span>
      `;

      const viewBtn = document.createElement("button");
      viewBtn.className = "status-btn";
      viewBtn.textContent = "View Details";
      viewBtn.onclick = () => viewPurchased(item.id);

      div.appendChild(viewBtn);
      listContainer.appendChild(div);
    });
  } catch (err) {
    listContainer.innerHTML = "<p>Error loading purchased items.</p>";
    console.error(err);
  }
}

// ---------- VIEW PURCHASED ITEM DETAILS ----------
async function viewPurchased(purchaseId) {
  currentPurchaseId = purchaseId;

  try {
    const res = await fetch(`get_purchase_details.php?id=${purchaseId}`);
    const data = await res.json();

    if (!data.success) {
      alert(data.message);
      return;
    }

    const item = data.item;

    const randomTexts = [
      "Blooming beautifully 🌸",
      "Petals whisper 🌸",
      "Sun-kissed and lovely 🌸",
      "All Government are Corrupt 🌸",
      "Nature's gift 🌸"
    ];
    const randomText = randomTexts[Math.floor(Math.random() * randomTexts.length)];

    const infoDiv = document.getElementById("purchaseInfo");
    infoDiv.innerHTML = `
      <h3>${item.product_name}</h3><br>
      <p><strong>Price:</strong> ₱${parseFloat(item.price).toFixed(2)}</p>
      <p><strong>Purchased on:</strong> ${item.date_purchased}</p>
      <p><strong>Full Name:</strong> ${item.fullname}</p>
      <p><strong>Email:</strong> ${item.email}</p>
      <p><strong>Address:</strong> ${item.address}</p>
      <p><strong>Payment Method:</strong> ${item.payment_method}</p>
      <br><p><strong>Status:</strong></p>
      <span class="status ${item.status.toLowerCase()}">${item.status}</span>
      <div style="font-size: 14px; color: #7b8e5c; margin-top: 3px;">${randomText}</div>
    `;

    document.getElementById("purchasedItemsList").style.display = "none";
    document.getElementById("purchaseDetails").style.display = "block";
  } catch (err) {
    console.error(err);
    alert("Failed to fetch purchase details.");
  }
}

// Hide details and show list again
function showPurchasedList() {
  document.getElementById("purchaseDetails").style.display = "none";
  document.getElementById("purchasedItemsList").style.display = "block";
}

async function updateStatus() {
  const newStatus = document.getElementById("statusSelect").value;

  try {
    const formData = new FormData();
    formData.append('purchase_id', currentPurchaseId);
    formData.append('status', newStatus);

    const res = await fetch('update_status.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      alert("Status updated!");
      showPurchasedPopup(); // Refresh the purchased list with updated status
    } else {
      alert(data.message);
    }
  } catch (err) {
    console.error(err);
    alert("Failed to update status.");
  }
}

// ---------- ONLOAD ----------
window.onload = function() {
  ["popup", "paymentPopup", "overlay", "paymentOverlay", "purchasedPopup", "purchasedOverlay"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = "none";
  });
};

</script>

</body>
</html>
