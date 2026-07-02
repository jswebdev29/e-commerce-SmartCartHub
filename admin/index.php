<?php
session_start();

require_once __DIR__ . '/../connectfinity.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize tracking
if (!isset($_SESSION['failed_attempts']))
    $_SESSION['failed_attempts'] = 0;
if (!isset($_SESSION['first_attempt_time']))
    $_SESSION['first_attempt_time'] = time();
if (!isset($_SESSION['block_time']))
    $_SESSION['block_time'] = 0;

$error = "";
$blocked = false;
$remainingTime = 0;

// Check if still blocked
if (time() - $_SESSION['block_time'] < 30) {
    $blocked = true;
    $remainingTime = 30 - (time() - $_SESSION['block_time']);
    $error = "Too many failed attempts. Try again in $remainingTime seconds.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid CSRF token");
    }

    $username = trim($_POST["username"]) ?? '';
    $password = trim($_POST["password"]) ?? '';
    $userCaptcha = trim($_POST["captchaCode"]) ?? '';
    $realCaptcha = $_POST["captcha"] ?? '';

    if ($userCaptcha !== $realCaptcha) {
        $error = "Captcha does not match!";
    } else {
        $stmt = $conn->prepare("
    SELECT * FROM login_owner
    WHERE username = ? OR email = ?
    LIMIT 1
");

        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $row = $result->fetch_assoc();

            // VERIFY HASHED PASSWORD
            if (password_verify($password, $row['password'])) {

                $_SESSION['failed_attempts'] = 0;
                $_SESSION['first_attempt_time'] = time();
                $_SESSION['block_time'] = 0;

                session_regenerate_id(true);

                $_SESSION["admin_username"] = $row['username'];

                // Auto logout timer start
                $_SESSION['LOGIN_TIME_ADMIN'] = time();

                header("Location: dashboard.php");
                exit();
            } else {
                // WRONG PASSWORD
                if (time() - $_SESSION['first_attempt_time'] > 30) {
                    $_SESSION['failed_attempts'] = 1;
                    $_SESSION['first_attempt_time'] = time();
                } else {
                    $_SESSION['failed_attempts']++;
                    if ($_SESSION['failed_attempts'] >= 2) {
                        $_SESSION['block_time'] = time();
                        $blocked = true;
                        $remainingTime = 30;
                        $error = "";
                    }
                }

                if (!$blocked && !$error) {
                    $error = "Invalid username or password!";
                }
            }

        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(319deg, #446f91, #008eff);
            min-height: 100vh;
            /* display: flex; */
            /* justify-content: center;
            align-items: center; */
            overflow-x: hidden;
        }

        .login-card {
            position: relative;
            padding: 30px 50px;
            width: 300px;
            text-align: center;
            border-radius: 12px;
            background: conic-gradient(#ff0000, #00ff00, #0000ff, #ffff00, #ff0000);
            overflow: hidden;
            z-index: 1;
            margin: 60px auto;
        }

        .login-card::before {
            content: "";
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 12px;
            background: conic-gradient(#ff0000, #00ff00, #0000ff, #ffff00, #ff0000);
            animation: rotateBorder 3s linear infinite;
            z-index: -1;
        }

        .login-card::after {
            content: "";
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border-radius: 10px;
            background: #1386e2;
            z-index: -1;
        }

        @keyframes rotateBorder {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .login-card h2 {
            margin-top: 10px;
            color: rgb(134, 4, 4);
            font-size: 40px;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        input {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 2px solid black;
            color: white;
            background-color: transparent;
            width: 80%;
            padding: 15px 15px 15px 40px;
            margin: 8px 0;
            font-size: 18px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        input:focus-visible {
            outline: none;
        }

        input::placeholder {
            color: #ffffff80;
        }

        input[type="submit"],
        input[type="reset"] {
            width: 42%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            margin: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="submit"] {
            background: #370b66;
            color: white;
        }

        input[type="reset"] {
            background: #370b66cc;
            color: white;
        }

        input[type="submit"]:hover,
        input[type="reset"]:hover {
            border: 1px solid black;
            background: green;
            transform: scale(1.05);
        }

        .captcha-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 15px 0;
        }

        #captcha {
            user-select: none;
            font-weight: bold;
            font-style: italic;
            text-decoration: line-through;
            font-size: 20px;
            letter-spacing: 12px;
            background: #fff;
            border: 2px solid black;
            color: #000;
            padding: 10px 20px;
            border-radius: 6px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }

        #refreshBtn {
            background: #370b66;
            color: white;
            border: none;
            padding: 12px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        #refreshBtn:hover {
            border: 1px solid black;
            background: green;
            transform: scale(1.1);
        }

        .input-container {
            position: relative;
            width: 100%;
            margin: 10px 0;
        }

        .input-container i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 18px;
            pointer-events: none;
        }

        .forgot-link {
            margin-top: 10px;
        }

        .forgot-link a {
            color: yellow;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        /* ----------------------------------------- */

        /* MOBILE */
        @media screen and (max-width: 576px) {

            body {
                display: block;
                min-height: auto;
                padding: 0;
                margin: 0;
            }

            .login-card {
                width: calc(100% - 80px);
                margin: 30px 40px 200px 40px;
                padding: 25px 20px;
                height: auto;
                box-sizing: border-box;
            }

            .login-card h2 {
                margin: 5px;
                font-size: 32px;
            }

            input {
                width: 100%;
                box-sizing: border-box;
                padding: 15px 15px 15px 40px;
                margin: 2px 0;
                font-size: 16px;
            }

            .captcha-container {
                margin: 2px 0;
                /* flex-direction: column; */
            }

            #captcha {
                box-sizing: border-box;
                letter-spacing: 5px;
            }

            #refreshBtn {
                margin-left: 0;
            }

            input[type="submit"],
            input[type="reset"] {
                margin: 8px 0;
                margin: 6px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h2><i class="fa-solid fa-circle-user"></i> Login</h2>
        <?php if (isset($_GET['timeout'])) { ?>
            <p style="
        color:white;
        background:red;
        font-size:12px;
        padding:8px;
        border-radius:6px;
        text-align:center;
        font-weight:bold;
        margin-bottom:10px;
    ">
                Session expired. Please login again.
            </p>
        <?php } ?>

        <!-- <?php if (!empty($_GET['timeout'])) { ?>
            <div class="alert alert-danger text-center">
                Session expired. Please login again.
            </div>
        <?php } ?> -->

        <?php
        if ($error) {
            echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
        }

        if ($blocked) {
            echo "<p id='timer'>Try again in $remainingTime seconds</p>";
        }
        ?>

        <form method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Enter Username or Email" required <?php if ($blocked)
                    echo "disabled"; ?>>
            </div>

            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Enter Password" required <?php if ($blocked)
                    echo "disabled"; ?>>
            </div>

            <div class="captcha-container">
                <p id="captcha"></p>
                <button id="refreshBtn" type="button"><i class="fa-solid fa-rotate"></i></button>
            </div>

            <div class="input-container">
                <i class="fa-solid fa-shield-halved"></i>
                <input type="hidden" name="captcha" id="captchaHidden">
                <input type="text" name="captchaCode" placeholder="Enter Captcha" required <?php if ($blocked)
                    echo "disabled"; ?>>
            </div>

            <div>
                <input type="submit" value="Login" <?php if ($blocked)
                    echo "disabled"; ?>>
                <input type="reset" value="Reset" <?php if ($blocked)
                    echo "disabled"; ?>>
            </div>
        </form>

        <!-- ✅ Forgot Password Link -->
        <div class="forgot-link">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>

    <script>
        // ✅ Generate Captcha
        const captcha = document.getElementById('captcha');
        const captchaHidden = document.getElementById('captchaHidden');
        const refreshBtn = document.getElementById('refreshBtn');

        function generateCaptcha() {
            const text = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
            let code = "";
            for (let i = 0; i < 5; i++) {
                code += text[Math.floor(Math.random() * text.length)];
            }
            captcha.innerText = code;
            captchaHidden.value = code;
        }

        refreshBtn.addEventListener('click', (e) => {
            e.preventDefault();
            generateCaptcha();
        });

        generateCaptcha();

        // ✅ Countdown for blocked state
        <?php if ($blocked): ?>
            let remaining = <?php echo $remainingTime; ?>;
            const timerElem = document.getElementById('timer');
            const inputs = document.querySelectorAll('#loginForm input, #loginForm button');

            const countdown = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(countdown);
                    timerElem.style.display = 'none';
                    inputs.forEach(inp => inp.disabled = false);
                } else {
                    timerElem.innerText = `Try again in ${remaining} seconds`;
                }
            }, 1000);
        <?php endif; ?>
    </script>
</body>

</html>