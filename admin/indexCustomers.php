<?php

$lifetime = 43200; // 12 hours

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// DB connection
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
        die("Invalid CSRF Token");
    }

    $username = trim($_POST["username"]) ?? '';
    $password = trim($_POST["password"]) ?? '';
    $userCaptcha = trim($_POST["captchaCode"]) ?? '';
    $realCaptcha = $_POST["captcha"] ?? '';

    if ($userCaptcha !== $realCaptcha) {
        $error = "Captcha does not match!";
    } else {
        $stmt = $conn->prepare("
    SELECT * FROM login_customers_db
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

                // Reset counters
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['first_attempt_time'] = time();
                $_SESSION['block_time'] = 0;

                session_regenerate_id(true);

                $_SESSION['admin_customer'] = $row['username'];

                $_SESSION['LOGIN_TIME_CUSTOMER'] = time(); // ADD THIS
                $_SESSION['LAST_ACTIVITY_CUSTOMER'] = time();
                $_SESSION['CREATED_CUSTOMER'] = time();

                header("Location: customers.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

    <style>
        body {
            font-family: Arial, sans-serif;
            /* background: linear-gradient(135deg, #505353da, #000405); */
            background-image: url(/e-commerce/img/wallpaper1.jpg!d);
            background-size: cover;
            height: 100vh;
            /* display: flex; */
            /* justify-content: center;
            align-items: center; */
            margin: 0;
        }

        #login-container {
            /* background: linear-gradient(135deg, #505353da, #000405); */
            background-color: #0303034f;
            backdrop-filter: blur(3px);
            border: 2px solid;
            /* border-left: 2px solid white;
            border-bottom: 2px solid white; */
            padding: 30px;
            margin-top: 25px;
            border-radius: 10px;
            width: 320px;
            /* box-shadow: 0px 4px 15px rgba(194, 193, 193, 0.2); */
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            margin: 80px auto;
        }

        h2 {
            color: white;
            margin-bottom: 30px;
        }

        input[type="text"],
        input[type="password"] {
            color: white;
            background-color: #0303032b;
            width: 76%;
            padding: 12px 35px;
            margin: 10px 0;
            border: 2px solid #ccc;
            border-radius: 5px;
            transition: 0.3s;
        }

        input::placeholder {
            color: #d1d1d1;
            opacity: 1;
            /* make sure it's not faded */
        }

        input[type="checkbox"] {
            appearance: none;
            background-color: #000405;
            border: 2px solid white;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        input[type="checkbox"]:checked {
            background-color: green;
            border-color: green;
        }

        input[type="checkbox"]:checked::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 6px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }



        input[type="submit"],
        input[type="reset"] {
            background: #2193b0;
            color: white;
            padding: 10px;
            width: 43%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 2px;
            transition: background 0.3s ease;
            margin: 10px;
        }

        input[type="submit"]:hover,
        input[type="reset"]:hover {
            background: green;
            letter-spacing: 0px;
        }

        .forgot-btn {
            display: inline-block;
            margin-top: 12px;
            background: transparent;
            border: none;
            color: #66cdff;
            cursor: pointer;
            font-size: 14px;
            text-decoration: underline;
            transition: 3s;
        }

        .forgot-btn:hover {
            color: #4abef6;
            font-size: 16px;
        }

        p {
            color: red;
            font-size: 14px;
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

        #refreshBtn {
            color: white;
            /* text-decoration: line-through; */
            font-style: italic;
            background-image: url(/e-commerce/img/captcha\ img.webp);
            border: 2px solid white;
            background-size: cover;
            font-weight: bold;
            font-size: 20px;
            letter-spacing: 10px;
            margin: 10px auto;
            padding: 10px;
            user-select: none;
            cursor: pointer;
            width: fit-content;
        }

        #refreshBtn i {
            margin-right: 8px;
            transition: 0.4s;
        }

        #refreshBtn:hover i {
            transform: rotate(180deg);
        }

        /* --------------------------------------------- */

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            pointer-events: none;
        }

        .timeout-msg {
            color: white;
            background: red;
            font-size: 12px;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* ================= MOBILE VIEW ================= */
        @media screen and (max-width: 576px) {

            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                overflow-x: hidden;
            }

            #login-container {
                width: calc(100% - 80px);
                /* 40px left + 40px right */
                margin: 50px 40px 200px 40px;
                /* top right bottom left */
                padding: 18px;
                box-sizing: border-box;
            }

            h2 {
                font-size: 24px;
                margin: 8px;
            }

            input[type="text"],
            input[type="password"] {
                width: 100%;
                padding: 10px 10px 10px 38px;
                margin-bottom: 2px;
                box-sizing: border-box;
                font-size: 14px;
            }


            .input-group {
                position: relative;
                margin-bottom: 5px;
            }

            .input-group i {
                position: absolute;
                left: 10px;
                top: 60%;
                transform: translateY(-50%);
                color: white;
                pointer-events: none;
            }

            #refreshBtn {
                letter-spacing: 4px;
                font-size: 16px;
                padding: 10px;
                box-sizing: border-box;
            }

            input[type="submit"],
            input[type="reset"] {
                margin: 6px 0;
            }
        }
    </style>
</head>

<body>
    <div id="login-container">
        <h2 id="heading1"><i class="fa-solid fa-circle-user"></i> Login Page</h2>
        <?php
        $timeoutMessage = filter_input(
            INPUT_GET,
            'timeout',
            FILTER_VALIDATE_INT
        );

        if ($timeoutMessage === 1) {
            ?>
            <p class="timeout-msg">
                Session expired. Please login again.
            </p>
        <?php } ?>


        <?php if ($error)
            echo "<p>$error</p>"; ?>
        <?php if ($blocked)
            echo "<p id='timer'>Try again in $remainingTime seconds</p>"; ?>

        <form method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Enter Username or Email" required <?php if ($blocked)
                    echo "disabled"; ?>>

            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Enter Password" id="psd" required <?php if ($blocked)
                    echo "disabled"; ?>>
            </div>

            <div>
                <input type="checkbox" id="checkedMe" onclick="togglePassword()" <?php if ($blocked)
                    echo "disabled"; ?>>
                <label for="checkedMe" style="color:white; margin-left:5px;">Show Password</label>
            </div>

            <div id="refreshBtn"> </div>


            <div class="input-group">
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

        <form action="forgot_pass_customers.php" method="get">
            <button type="submit" class="forgot-btn" <?php if ($blocked)
                echo "disabled"; ?>>Forgot Password?</button>
        </form>
    </div>

    <script>
        let captchaHidden = document.getElementById('captchaHidden');
        let refreshBtn = document.getElementById('refreshBtn');

        function generateCaptcha() {
            let text = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
            let code = "";

            for (let i = 0; i < 5; i++) {
                let randomIndex = Math.floor(Math.random() * text.length);
                code += text[randomIndex];
            }

            refreshBtn.innerHTML = `${code} <i class="fa-solid fa-rotate"></i>`;
            captchaHidden.value = code;
        }

        refreshBtn.addEventListener('click', function (e) {
            e.preventDefault();
            generateCaptcha();
        });

        generateCaptcha();

        // heading + border animation
        let clrs = ['skyblue', 'white']
        // let clrs2 = ['blue', 'yellow']
        let clrs2 = ['black']
        let i = 0;
        setInterval(() => {
            document.getElementById('heading1').style.color = clrs[i];
            document.getElementById('login-container').style.borderColor = clrs2[i];
            i = (i + 1) % 2;
        }, 100);

        function togglePassword() {
            let password = document.getElementById("psd");
            password.type = (password.type === "text") ? "password" : "text";
        }

        // countdown for block
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