<?php
session_start();
include 'db_connect.php';

// ---------- STEP 1: Handle token from email ----------
if(isset($_GET['token'])){
    $token = $_GET['token'];

    // Check token in the database
    $stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $tokenExpiry = $row['token_expiry'];

        if(strtotime($tokenExpiry) > time()){
            // Token is valid: store in session
            $_SESSION['reset_user_id'] = $userId;
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_modal_open'] = true;
            header("Location: index.php#resetPasswordModal");
            exit;
        } else {
            $_SESSION['reset_error'] = "Token expired. Please request a new password reset.";
            header("Location: index.php#forgotPasswordModal");
            exit;
        }
    } else {
        $_SESSION['reset_error'] = "Invalid token. Please request a new password reset.";
        header("Location: index.php#forgotPasswordModal");
        exit;
    }
}

// ---------- STEP 2: Handle form submission ----------
if(isset($_POST['new_password']) && isset($_SESSION['reset_user_id'])){
    $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $userId = $_SESSION['reset_user_id'];

    // Update password and clear token
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
    $stmt->bind_param("si", $newPassword, $userId);
    $stmt->execute();

    // Clear session variables
    unset($_SESSION['reset_user_id'], $_SESSION['reset_token']);
    $_SESSION['reset_success'] = "Password has been successfully reset! You can now log in.";
    header("Location: index.php#loginModal");
    exit;
}

// ---------- REDIRECT IF NO TOKEN OR SESSION ----------
header("Location: index.php");
exit;
?>