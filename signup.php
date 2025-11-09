<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $gender = trim($_POST["gender"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone_number = trim($_POST["phone_number"] ?? '');

    // Check if username or email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['signup_error'] = "Username or email already exists. Please try another.";
        header("Location: index.php#signupModal");
        exit;
    }
    $check->close();

    // Corrected bind_param count and types
    $sql = "INSERT INTO users (first_name, last_name, gender, username, email, password, phone_number)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $last_name, $gender, $username, $email, $password, $phone_number);

    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Account created successfully! You can now log in.";
        header("Location: index.php#loginModal");
        exit;
    } else {
        $_SESSION['signup_error'] = "An unexpected error occurred. Please try again.";
        header("Location: index.php#signupModal");
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>
