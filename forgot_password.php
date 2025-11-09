<?php
session_start();

// Include PHPMailer manually
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
require 'db_connect.php';

if(isset($_POST['email'])){
    $email = $_POST['email'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $userId = $row['id'];

        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE id=?");
        $stmt->bind_param("ssi", $token, $expiry, $userId);
        $stmt->execute();

        $resetLink = "http://localhost/websystem/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'valenzuelarenzpio@gmail.com';
            $mail->Password = 'kynpywvkshjilntj';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('valenzuelarenzpio@gmail.com', 'Flower Shop');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi,<br><br>Click the link below to reset your password:<br>
                           <a href='$resetLink'>$resetLink</a><br><br>
                           This link will expire in 1 hour.";

            $mail->send();
            $_SESSION['forgot_success'] = "Password reset email has been sent!";
        } catch (Exception $e) {
            $_SESSION['forgot_error'] = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['forgot_error'] = "Email not found in our system.";
    }
} else {
    $_SESSION['forgot_error'] = "Please enter an email.";
}

// Redirect back to index.php and open forgot password modal
header("Location: index.php#forgotPasswordModal");
exit;
?>