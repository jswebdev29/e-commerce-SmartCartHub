<?php
session_start();
require_once __DIR__ . '/connectfinity.php';

if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit;
}

$email = $_SESSION['customer_email'];

$user = $conn->query("
    SELECT * FROM customers 
    WHERE email='$email'
");

$row = $user->fetch_assoc();

$msg = "";

if (isset($_POST['update'])) {

    $input_email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);

    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);

    // GET LATEST USER DATA
    $user = $conn->query("
        SELECT * FROM customers 
        WHERE email='$email'
    ");

    $row = $user->fetch_assoc();

    /*
    =========================================
    METHOD 1 → OLD PASSWORD
    =========================================
    */

    $password_match = false;

    if (!empty($old_password)) {

        // HASH PASSWORD CHECK
        if (password_verify($old_password, $row['password'])) {

            $password_match = true;

        }

        // OLD NORMAL PASSWORD SUPPORT
        elseif ($old_password == $row['password']) {

            $password_match = true;
        }
    }

    /*
    =========================================
    METHOD 2 → PROFILE INFORMATION
    =========================================
    */

    $info_match = (
        $row['email'] == $input_email &&
        $row['dob'] == $dob &&
        $row['phone'] == $phone
    );

    /*
    =========================================
    UPDATE PASSWORD
    =========================================
    */

    if ($password_match || $info_match) {

        // HASH NEW PASSWORD
        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $conn->query("
            UPDATE customers 
            SET password='$hashed_password'
            WHERE email='$email'
        ");

        echo "
        <script>
            alert('✅ Password updated successfully');
            window.location='customer_profile.php';
        </script>
        ";

        exit;

    } else {

        $msg = "❌ Wrong Email, DOB, Phone Number or Old Password.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="admin/assets/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f4f7fb;
        }

        .change-box {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        .or-text {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin: 20px 0;
        }

        /* =========MOBILE RESPONSIVE===================== */

        @media (max-width:768px) {

            .container {
                padding-left: 30px;
                padding-right: 30px;
            }

            .col-md-6 {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
            }

            .change-box {
                padding: 10px;
                border-radius: 12px;
            }

            h3 {
                font-size: 22px;
            }

            h5 {
                font-size: 16px;
            }

            .form-control {
                height: 30px;
                font-size: 14px;
            }

            .btn {
                height: 35px;
                font-size: 15px;
            }

            .or-text {
                font-size: 20px;
                margin: 15px 0;
            }
        }

        /* Small Mobile */

        @media (max-width:480px) {

            .container {
                margin-top: 20px !important;
            }

            .change-box {
                padding: 10px;
            }

            h3 {
                font-size: 20px;
            }

            h5 {
                font-size: 14px;
            }

            .form-control {
                font-size: 13px;
            }

            .btn {
                font-size: 14px;
            }

            .or-text {
                font-size: 16px;
            }
        }
    </style>

</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="col-md-6 mx-auto">

            <div class="change-box">

                <!-- BACK -->
                <div class="mb-3">

                    <a href="customer_profile.php" class="text-decoration-none fw-bold text-primary">

                        <i class="fa-solid fa-arrow-left"></i>
                        Back

                    </a>

                </div>

                <h3 class="mb-4 text-center text-success">

                    <i class="fa-solid fa-key"></i>
                    Change Password

                </h3>

                <!-- ALERT -->
                <?php if ($msg != "") { ?>

                    <div class="alert alert-danger text-center">

                        <?php echo $msg; ?>

                    </div>

                <?php } ?>

                <form method="post">

                    <h5 class="text-primary mb-3">

                        Verify Using Profile Information

                    </h5>

                    <!-- EMAIL -->
                    <input type="email" name="email" class="form-control mb-3" placeholder="Enter Email">

                    <!-- DOB -->
                    <input type="date" name="dob" class="form-control mb-3">

                    <!-- PHONE -->
                    <input type="text" name="phone" class="form-control mb-3" placeholder="Enter Phone Number">

                    <div class="or-text">
                        OR
                    </div>

                    <h5 class="text-success mb-3">

                        Verify Using Old Password

                    </h5>

                    <!-- OLD PASSWORD -->
                    <input type="password" name="old_password" class="form-control mb-3"
                        placeholder="Enter Old Password">

                    <!-- NEW PASSWORD -->
                    <input type="password" name="new_password" class="form-control mb-4"
                        placeholder="Enter New Password" required>

                    <button type="submit" name="update" class="btn btn-success w-100">

                        <i class="fa-solid fa-rotate"></i>
                        Update Password

                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>