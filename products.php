<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flower_shop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<style>
#cart-popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    background: #fff;
    border: 1px solid #ccc;
    padding: 10px;
    display: none;
    z-index: 100;
}
.product-card { border:1px solid #ccc; padding:10px; width:200px; margin:10px; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function addToCart(productId) {
    $.post("cart_action.php", { product_id: productId }, function(data) {
        $("#cart-popup").html(data).show();
    });
}

function refreshCart() {
    $.get("cart_action.php", function(data) {
        $("#cart-popup").html(data);
    });
}

$(document).ready(function() {
    refreshCart(); // Load cart initially
});
</script>
</head>
<body>

<h1>Flower Shop Products</h1>
<div style="display:flex; flex-wrap:wrap;">
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='product-card'>";
        echo "<img src='" . $row['image_url'] . "' style='width:100%; height:150px; object-fit:cover;'><br>";
        echo "<strong>" . $row['name'] . "</strong><br>";
        echo "<em>" . $row['description'] . "</em><br>";
        echo "₱" . number_format($row['price'],2) . "<br>";
        echo "Stock: " . $row['stock'] . "<br>";
        echo "<button onclick='addToCart(" . $row['id'] . ")'>Add to Cart</button>";
        echo "</div>";
    }
}
?>
</div>

<div id="cart-popup"></div>
</body>
</html>

<?php $conn->close(); ?>
