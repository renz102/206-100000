<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
    }

    // Fetch user info including profile_pic and is_admin
    $stmt = $conn->prepare("SELECT id, username, first_name, last_name, gender, password, profile_pic, email, phone_number, birthday, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['failed_attempts'] = 0;

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['gender'] = $user['gender'];
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['phone_number'] = $user['phone_number'] ?? '';
            $_SESSION['birthday'] = $user['birthday'] ?? '';
            $_SESSION['is_admin'] = $user['is_admin']; // ✅ store admin flag

            if (!empty($user['profile_pic'])) {
                $_SESSION['profile_pic'] = $user['profile_pic'];
            } else {
                $_SESSION['profile_pic'] = ($user['gender'] === 'Male') 
                    ? 'male' . rand(1,3) . '.png' 
                    : 'female' . rand(1,3) . '.png';
            }

            $_SESSION['just_logged_in'] = true;

            // ✅ Redirect admin to dashboard, users to index.php
            if ($user['is_admin'] == 1) {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;

        } else {
            $_SESSION['failed_attempts'] += 1;
        }
    } else {
        $_SESSION['failed_attempts'] += 1;
    }

    if ($_SESSION['failed_attempts'] >= 3) {
        $_SESSION['login_error'] = "It seems you forgot your password. You might want to reset it.";
        $_SESSION['show_forgot_suggestion'] = true;
    } else {
        $_SESSION['login_error'] = "Incorrect username or password. Try again.";
    }

    header("Location: index.php#loginModal");
    exit;
}
?>
