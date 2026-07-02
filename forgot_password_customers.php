<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

$msg = "";

if (isset($_POST['reset_password'])) {

    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['new_password']);

    // CHECK EMPTY
    if (
        empty($email) ||
        empty($dob) ||
        empty($phone) ||
        empty($new_password)
    ) {

        $msg = "⚠️ Please fill all fields.";

    } else {

        // FIND CUSTOMER
        $query = "
            SELECT * FROM customers
            WHERE email='$email'
            LIMIT 1
        ";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            // VERIFY DETAILS
            if (
                $row['dob'] == $dob &&
                $row['phone'] == $phone
            ) {

                // HASH PASSWORD
                $hashed_password = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );

                // UPDATE PASSWORD
                mysqli_query($conn, "
                    UPDATE customers
                    SET password='$hashed_password'
                    WHERE email='$email'
                ");

                echo "
                <script>
                    alert('✅ Password Reset Successfully');
                    window.location='customer_login.php';
                </script>
                ";

                exit();

            } else {

                // WRONG INFO ALERT
                $msg = "❌ Wrong DOB or Phone Number.";

            }

        } else {

            // EMAIL NOT FOUND
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

    <title>Forgot Password</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
        }

        .forgot-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Responsive */
        @media (max-width:768px) {

            .container {
                padding-left: 20px;
                padding-right: 20px;
            }

            .col-md-5 {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0;
            }

            .forgot-box {
                padding: 10px;
                border-radius: 12px;
            }

            .forgot-box h2 {
                font-size: 22px;
            }

            .form-control {
                height: 35px;
                font-size: 14px;
            }

            .btn {
                height: 32px;
                font-size: 15px;
            }
        }

        /* Small Mobile */
        @media (max-width:480px) {

            .container {
                margin-top: 30px !important;
            }

            .col-md-5 {
                width: 100% !important;
            }

            .forgot-box {
                padding: 15px;
            }

            .forgot-box h2 {
                font-size: 20px;
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
        }
    </style>

</head>

<body>

    <div class="container mt-5">

        <div class="col-md-5 mx-auto">

            <div class="forgot-box">

                <!-- BACK -->
                <div class="mb-3">

                    <a href="customer_login.php" class="text-decoration-none fw-bold text-primary">

                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Login

                    </a>

                </div>

                <h2 class="text-center text-danger mb-4">

                    <i class="fa-solid fa-key"></i>
                    Forgot Password

                </h2>

                <!-- MESSAGE -->
                <?php if ($msg != "") { ?>

                    <div class="alert alert-danger text-center">

                        <?php echo $msg; ?>

                    </div>

                <?php } ?>

                <form method="POST">

                    <!-- EMAIL -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Email Address
                        </label>

                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>

                    </div>

                    <!-- DOB -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Date of Birth
                        </label>

                        <input type="date" name="dob" class="form-control" required>

                    </div>

                    <!-- PHONE -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Phone Number
                        </label>

                        <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>

                    </div>

                    <!-- NEW PASSWORD -->
                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            New Password
                        </label>

                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password"
                            required>

                    </div>

                    <!-- BUTTON -->
                    <button type="submit" name="reset_password" class="btn btn-danger w-100">

                        <i class="fa-solid fa-unlock"></i>
                        Reset Password

                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>