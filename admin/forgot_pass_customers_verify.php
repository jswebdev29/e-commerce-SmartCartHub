<?php
session_start();

require_once __DIR__ . '/../connectfinity.php';

$msg = "";
$showForm = false;

/* =========================
   TOKEN VERIFY
========================= */
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $sql = "SELECT * FROM login_customers_db
            WHERE reset_token='$token'
            LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // TOKEN VALID
        if (time() <= $row['token_expiry']) {
            $showForm = true;
            $_SESSION['reset_username'] = $row['username'];
        } else {
            header("refresh:3;url=indexCustomers.php");
            $msg = "
            <div style='color:red;font-weight:bold;'>
                ❌ Token Expired!
                <br>
                Redirecting To Login...
            </div>";
        }
    } else {
        header("refresh:3;url=indexCustomers.php");
        $msg = "
        <div style='color:red;font-weight:bold;'>
            ❌ Invalid Token!
            <br>
            Redirecting To Login...
        </div>";
    }
}

/* =========================
   RESET PASSWORD
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $birthplace = strtolower(
        mysqli_real_escape_string(
            $conn,
            trim($_POST['birthplace'])
        )
    );

    $newpass = $_POST['newpass'];
    $username = $_SESSION['reset_username'] ?? '';

    $sql = "SELECT * FROM login_customers_db
            WHERE username='$username'
            AND dob='$dob'
            AND phone='$phone'
            AND email='$email'
            AND LOWER(security_answer)='$birthplace'
            LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {

        // HASH PASSWORD
        $hashedPassword = password_hash(
            $newpass,
            PASSWORD_DEFAULT
        );

        $update = "UPDATE login_customers_db
                   SET password='$hashedPassword',
                       reset_token=NULL,
                       token_expiry=NULL
                   WHERE username='$username'";
        if ($conn->query($update)) {
            session_unset();
            session_destroy();

            $msg = "
            <div style='color:green;font-weight:bold;'>
                ✅ Password Reset Successful!
                <br><br>

                <a href='indexCustomers.php'>
                    Login Now
                </a>
            </div>";
            $showForm = false;
        } else {
            $msg = "❌ Password Update Failed!";
        }
    } else {
        $msg = "❌ Verification Failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Verify Identity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        body {

            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            font-family: Arial;
        }

        .container {

            width: 420px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }

        .logo {

            font-size: 50px;
            color: #2575fc;
            margin-bottom: 10px;
        }

        input {

            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        button {

            width: 100%;
            padding: 12px;
            background: #2575fc;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #6a11cb;
        }

        #timer {
            color: red;
            font-weight: bold;
        }
    </style>

    <script>
        let remaining = 90;
        const countdown = setInterval(() => {
            if (remaining > 0) {
                let timer = document.getElementById("timer");
                if (timer) {
                    timer.innerText =
                        "⏳ Token expires in " +
                        remaining +
                        " sec";
                }
                remaining--;
            } else {
                clearInterval(countdown);
                let form =
                    document.getElementById("verifyForm");
                if (form) {
                    form.style.display = "none";
                }

                let timer =
                    document.getElementById("timer");
                if (timer) {
                    timer.innerHTML =
                        "❌ Token Expired! Redirecting...";
                }

                setTimeout(() => {
                    window.location.href =
                        "indexCustomers.php";
                }, 2000);
            }
        }, 1000);
    </script>

</head>
<body>
    <div class="container">
        <i class="fa-solid fa-lock logo"></i>
        <h2>Verify Identity</h2>
        <p id="timer"></p>
        <?php echo $msg; ?>
        <?php if ($showForm): ?>
            <form method="post" id="verifyForm">
            
                <label style="float:left; font-size:16px; margin-top:5px;">Date of Birth</label>
                <input type="date" name="dob" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="birthplace" placeholder="Birthplace City" required>
                <input type="password" name="newpass" placeholder="New Password" required>
                <button type="submit">
                    Reset Password
                </button>

            </form>
        <?php endif; ?>

    </div>
</body>
</html>