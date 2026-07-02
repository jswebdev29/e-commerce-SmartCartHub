<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

$msg = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {

        $msg = "⚠️ Please fill all fields.";

    } else {

        $email = mysqli_real_escape_string($conn, $email);

        $query = "SELECT * FROM customers WHERE email='$email' LIMIT 1";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            // PASSWORD VERIFY
            if (password_verify($password, $row['password'])) {

                $_SESSION['customer_email'] = $row['email'];
                $_SESSION['customer_name'] = $row['name'];

                header("Location: customer_profile.php");
                exit();

            } else {

                $msg = "❌ Invalid Password.";

            }

        } else {

            $msg = "❌ Email not found.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
        }

        .login-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .back-btn {
            text-decoration: none;
            font-weight: bold;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {

            .container {
                padding-left: 12px;
                padding-right: 12px;
            }

            .login-box {
                width: calc(100% - 80px);
                margin: 20px 40px 200px 40px;
                padding: 20px;
                border-radius: 12px;
            }

            .login-box h2 {
                font-size: 24px;
            }

            .form-control {
                height: 35px;
                font-size: 14px;
            }

            .btn {
                height: 38px;
                font-size: 15px;
            }

            .back-btn {
                font-size: 14px;
            }

            .text-end a {
                font-size: 13px;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {

            .container {
                margin-top: 10px !important;
            }

            .login-box {
                padding: 15px;
            }

            .login-box h2 {
                font-size: 22px;
            }

            .form-label {
                font-size: 14px;
            }

            .form-control {
                font-size: 13px;
            }

            .btn {
                font-size: 14px;
            }

            p {
                font-size: 13px;
            }
        }
    </style>

</head>

<body>

    <div class="container py-4">
        <div class="col-lg-5 col-md-7 col-sm-10 col-12 mx-auto">
            <div class="login-box">

                <!-- BACK BUTTON -->
                <div class="mb-3">

                    <a href="index.php" class="back-btn text-primary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back
                    </a>

                </div>

                <h2 class="text-center mb-4 text-primary">

                    <i class="fa-solid fa-user"></i>
                    Customer Login

                </h2>

                <!-- MESSAGE -->
                <?php if ($msg != "") { ?>

                    <div class="alert alert-danger text-center">

                        <?php echo $msg; ?>

                    </div>

                <?php } ?>

                <!-- LOGIN FORM -->
                <form method="post">

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Email Address
                        </label>

                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Password
                        </label>

                        <input type="password" name="password" class="form-control" placeholder="Enter your password"
                            required>

                    </div>

                    <!-- FORGOT PASSWORD -->
                    <div class="text-end mb-3">

                        <a href="forgot_password_customers.php" class="text-danger text-decoration-none fw-bold">

                            Forgot Password?

                        </a>

                    </div>

                    <button type="submit" name="login" class="btn btn-primary w-100">

                        <i class="fa-solid fa-right-to-bracket"></i>
                        Login

                    </button>

                </form>

                <p class="text-center mt-4 mb-0">

                    Don't have an account?

                    <a href="customer_register.php" class="fw-bold">
                        Register
                    </a>

                </p>

            </div>

        </div>

    </div>

</body>

</html>