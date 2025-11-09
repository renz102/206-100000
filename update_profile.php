<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: index.php#loginModal");
    exit;
}
// ---------- Helper function ----------
function redirect_with_message($message, $type = 'success') {
    $_SESSION[$type] = $message;
    $_SESSION['stay_on_profile'] = true;
    header("Location: index.php#profile");
    exit;
}

// ---------- 1. PROFILE PHOTO UPLOAD ----------
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];

    if (!in_array($fileType, $allowedTypes)) {
        redirect_with_message("Invalid file type. Only JPG, PNG, and GIF allowed.", 'error');
    }

    if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        redirect_with_message("Failed to upload photo.", 'error');
    }

    $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
    $stmt->bind_param("si", $targetFile, $user_id);
    $stmt->execute();

    $_SESSION['profile_pic'] = $targetFile;
    redirect_with_message("Profile photo updated successfully!");
}

// ---------- 2. BASIC INFO UPDATE ----------
if (isset($_POST['update_basic'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $birthday = $_POST['birthday'] ?? null;
    $gender = $_POST['gender'] ?? null;

    if (!$full_name || !$username) {
        redirect_with_message("Full name and username cannot be empty.", 'error');
    }

    $nameParts = explode(' ', $full_name, 2);
    $first_name = $nameParts[0];
    $last_name = $nameParts[1] ?? '';

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, username=?, birthday=?, gender=? WHERE id=?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $username, $birthday, $gender, $user_id);
    $stmt->execute();

    $_SESSION['full_name'] = $full_name;
    $_SESSION['username'] = $username;
    $_SESSION['birthday'] = $birthday;
    $_SESSION['gender'] = $gender;
    redirect_with_message("Basic info updated successfully!");
}

// ---------- 3. CONTACT INFO UPDATE ----------
if (isset($_POST['update_contact'])) {
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_message("Invalid email format.", 'error');
    }

    if ($phone_number && !preg_match('/^[0-9]{10,15}$/', $phone_number)) {
        redirect_with_message("Phone number must be 10-15 digits.", 'error');
    }

    $stmt = $conn->prepare("UPDATE users SET email=?, phone_number=? WHERE id=?");
    $stmt->bind_param("ssi", $email, $phone_number, $user_id);
    $stmt->execute();

    $_SESSION['email'] = $email;
    $_SESSION['phone_number'] = $phone_number;
    redirect_with_message("Contact info updated successfully!");
}

// ---------- 4. PASSWORD CHANGE ----------
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    $username = $_SESSION['username'];

    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $_SESSION['error'] = "Please fill out all password fields.";
        $_SESSION['stay_on_profile'] = true;
        header("Location: index.php#profile");
        exit;
    }

    if ($new_password !== $confirm_new_password) {
        $_SESSION['error'] = "New password and confirmation do not match.";
        $_SESSION['stay_on_profile'] = true;
        header("Location: index.php#profile");
        exit;
    }

    // Fetch current hashed password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify old password
    if (!password_verify($current_password, $hashed_password)) {
        $_SESSION['error'] = "Current password is incorrect.";
        $_SESSION['stay_on_profile'] = true;
        header("Location: index.php#profile");
        exit;
    }

    // Hash new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in DB
    $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $update->bind_param("ss", $new_hashed_password, $username);
    $update->execute();
    $update->close();

    $_SESSION['success'] = "Password updated successfully!";
    $_SESSION['stay_on_profile'] = true;
    header("Location: index.php#profile");
    exit;
}

// ---------- Default fallback ----------
redirect_with_message("No action performed.", 'error');
?>
