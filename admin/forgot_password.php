<?php
session_start();
require_once __DIR__ . '/../connectfinity.php';

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $input = mysqli_real_escape_string($conn, trim($_POST['username']));

    $sql = "SELECT * FROM login_owner
            WHERE username='$input'
            OR email='$input'
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $username = $row['username'];
        $email = $row['email'];

        $token = bin2hex(random_bytes(32));
        $expiry = time() + 300; // 5 min

        if(!$conn->query("
            UPDATE login_owner
            SET reset_token='$token',
                token_expiry='$expiry'
            WHERE id='{$row['id']}'
        ")){
            die($conn->error);
        }

        $reset_link =
            "http://localhost/e-commerce/admin/forgot_password_verify.php?token=$token";

            // "https://yourdomain.infinityfreeapp.com/admin/forgot_password_verify.php?token=$token";
            

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jswebdev29@gmail.com';
            $mail->Password = 'nwnsxclvvrohrwlg';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom(
                'jswebdev29@gmail.com',
                'SmartCartHub'
            );

            $mail->addAddress($email);

            $mail->isHTML(true);

            $mail->Subject = "Password Reset";

            $mail->Body = "
            <h2>Password Reset</h2>

            <p>Hello $username,</p>

            <p>
            Click button below:
            </p>

            <a href='$reset_link'
               style='padding:12px 20px;
                      font-size: 20px;
                      background:#2575fc;
                      color:white;
                      text-decoration:none'>
               Reset Password
            </a>

            <br><br>

            <small>
            Link expires in 5 minutes
            </small>";

            $mail->send();

            $msg = "✅ Reset link sent to email.";

        } catch (Exception $e) {

            $msg = "❌ Mail Error: " . $mail->ErrorInfo;
        }

    } else {

        $msg = "❌ User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #63bae5;
            text-align: center;
        }

        .card-container {
            align-items: center;
            display: inline-block;
            text-align: center;
            justify-content: center;
            margin: 50px;
            vertical-align: top;
        }

        .card {
            background: rgba(255, 255, 255, 0.97);
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            width: 500px;
            width: 90%;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            z-index: 2;
        }


        .logo {
            font-size: 50px;
            color: #2575fc;
            margin-bottom: 2px;
            animation: zoomIn 0.8s ease-in-out;
        }

        h2 {
            margin-bottom: 5px;
            color: #333;
            animation: fadeIn 0.8s ease-in-out;
        }

        .subtitle {
            color: #4d5a85;
            line-height: 1.5;
            margin-bottom: 10px;
            font-size: 14px;
        }

        input[type="text"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #2575fc;
            box-shadow: 0 0 8px rgba(37, 117, 252, 0.5);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2575fc;
            border: none;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
        }

        button:hover {
            background: #6a11cb;
            transform: scale(1.05);
        }

        p {
            margin-top: 15px;
            font-size: 14px;
        }

        p a {
            color: #2575fc;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .divider {
            margin: 2px;
            display: flex;
            align-items: center;
            text-align: center;
            color: #999;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }

        .divider span {
            margin: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .back-login a {
            text-decoration: none;
            color: #2575fc;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
        }

        .back-login a:hover {
            color: #6a11cb;
        }

        .back-login i {
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <div class="card-container">
        <div class="card">
            <!-- Font Awesome Logo -->
            <i class="fas fa-lock logo"></i>

            <h2>Forgot Your Password</h2>
            <p class="subtitle">
                Enter your registered email address and <br>we'll send you a password reset link.
            </p>
            <form method="post">
                <input type="text" name="username" placeholder="Enter Username or Email" required>
                <button type="submit">Send Reset Link</button>
            </form>
            <p style="color:red">
                <?= $msg ?>
            </p>
            <!-- LINE -->
            <div class="divider">
                <span>OR</span>
            </div>

            <!-- BACK TO LOGIN -->
            <div class="back-login">
                <a href="index.php">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
    <div class="card-container">
        <img src="/e-commerce/admin/assets/img/forgotbg2.jpg" alt="" width="600px">
    </div>
</body>

</html>