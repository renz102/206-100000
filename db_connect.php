<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "flower_shop";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4"); // Ensures proper encoding
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
