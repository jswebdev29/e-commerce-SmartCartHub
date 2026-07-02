<?php
session_start();

require_once __DIR__ . '/../connectfinity.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = trim($_POST["username"]);
    $input = mysqli_real_escape_string($conn, $input);
    $sql = "SELECT * FROM login_customers_db
            WHERE username='$input'
            OR email='$input'
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];

        // GENERATE TOKEN
        $token = bin2hex(random_bytes(32));

        // 1.5 MINUTE EXPIRY
        $expiry = time() + 90;

        // SAVE TOKEN
        $update = "UPDATE login_customers_db
                   SET reset_token='$token',
                       token_expiry='$expiry'
                   WHERE username='$username'";

        if (!$conn->query($update)) {
            die($conn->error);
        }
        $_SESSION['reset_username'] = $username;

        $msg = "
        <div style='color:green;font-weight:bold;'>
            ✅ Reset Link Generated
            <br><br>
            <a href='forgot_pass_customers_verify.php?token=$token'>
                Click Here To Reset Password
            </a>
            <br><br>
            <small>
                Token expires in 1.5 minutes
            </small>
        </div>";
    } else {
        $msg = "
        <div style='color:red;font-weight:bold;'>
            ❌ User Not Found!
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            text-align: center;
            justify-content: center;
            align-items: center;
            background: linear-gradient(90deg, #eef2ff, #dfe9ff);
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        /* MAIN CONTAINER */
        #main-container {
            align-items: center;
            justify-content: space-evenly;
            width: 100%;
            gap: 30px;
            max-width: 1500px;
            padding: 20px 40px;
        }

        #main-container .container {
            text-align: center;
            display: inline-block;
            vertical-align: top;
            margin: 50px;
        }

        /* LEFT BOX */
        .box {
            width: 400px;
            background: rgba(255, 255, 255, 0.97);
            padding: 30px;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            z-index: 2;
        }

        .logo {
            font-size: 50px;
            color: #2575fc;
            margin-bottom: 15px;
            animation: zoomIn 0.8s ease-in-out;
        }

        .box h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #1f2b5c;
        }

        .subtitle {
            color: #4d5a85;
            line-height: 1.5;
            margin-bottom: 10px;
            font-size: 14px;
        }

        /* INPUT */
        .input-box {
            position: relative;
            margin-bottom: 20px;
        }

        .input-box i {
            position: absolute;
            left: 15px;
            top: 16px;
            font-size: 20px;
            color: #777;
        }

        .input-box input {
            width: 100%;
            padding: 12px 12px 12px 50px;
            border: 2px solid #d7dcf5;
            border-radius: 10px;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
        }

        /* BUTTON */
        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #5a4bff, #7b61ff);
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: scale(1.03);
        }

        /* MESSAGE */
        .msg {
            margin-top: 15px;
        }

        /* DIVIDER */
        .divider {
            margin: 10px;
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

        /* RIGHT IMAGE */
        .image-box img {
            width: 400px;
            max-width: 100%;
            animation: floatImage 4s ease-in-out infinite;
        }

        @keyframes floatImage {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* MOBILE */
        @media(max-width: 768px) {

            body {
                overflow-y: auto;
            }

            #main-container {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }

            .image-box img {
                width: 380px;
                margin-top: 20px;
            }

            .box {
                width: 90%;
                padding: 35px 25px;
            }

            .box h2 {
                font-size: 34px;
            }
        }
    </style>

</head>

<body>

        <div id="main-container">

            <div class="container">
                <!-- LEFT SIDE -->
                <div class="box">
                    <i class="fa-solid fa-lock logo"></i>
                    <h2>Forgot Password?</h2>
                    <p class="subtitle">
                        No worries! It happens to the best of us.
                        Enter your email and we’ll send you a link
                        to reset your password.
                    </p>
                    <form method="post">
                        <div class="input-box">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="text" name="username" placeholder="Enter your email address or username"
                                required>
                        </div>

                        <button type="submit">
                            Send Reset Link
                        </button>
                    </form>

                    <div class="msg">
                        <?php echo $msg; ?>
                    </div>

                    <!-- LINE -->
                    <div class="divider">
                        <span>OR</span>
                    </div>

                    <!-- BACK TO LOGIN -->
                    <div class="back-login">
                        <a href="indexCustomers.php">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Login
                        </a>
                    </div>

                </div>
            </div>

            <div class="container">
                <!-- RIGHT SIDE IMAGE -->
                <div class="image-box">
                    <img src="/e-commerce/admin/assets/img/forgotcustomrbg.png" alt="Forgot Password">
                </div>
            </div>

        </div>

</body>

</html>