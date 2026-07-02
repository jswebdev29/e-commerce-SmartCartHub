<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - File Not Found</title>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
    }

    h1 {
      font-size: 3em;
      margin: 0;
      animation: bounce 2s infinite;
    }

    p {
      margin-top: 10px;
      font-size: 1.2em;
      opacity: 0.8;
    }

    .container {
      text-align: center;
    }

    .icon {
      font-size: 4em;
      margin-bottom: 15px;
      color: #ffeb3b;
      animation: shake 1.5s infinite;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 25px;
      background: #fff;
      color: #764ba2;
      border-radius: 30px;
      font-size: 1em;
      font-weight: bold;
      text-decoration: none;
      transition: 0.3s;
    }

    .btn i {
      margin-right: 8px;
    }

    .btn:hover {
      background: #ff6ec4;
      color: #fff;
      transform: scale(1.1);
      box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
    }

    /* Animations */
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-20px);
      }
      60% {
        transform: translateY(-10px);
      }
    }

    @keyframes shake {
      0% { transform: rotate(0deg); }
      25% { transform: rotate(5deg); }
      50% { transform: rotate(0deg); }
      75% { transform: rotate(-5deg); }
      100% { transform: rotate(0deg); }
    }

    /* Floating shapes */
    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      animation: float 8s infinite ease-in-out;
    }

    .circle.small {
      width: 40px;
      height: 40px;
      bottom: 10%;
      left: 20%;
      animation-delay: 1s;
    }

    .circle.medium {
      width: 80px;
      height: 80px;
      top: 20%;
      right: 15%;
      animation-delay: 3s;
    }

    .circle.large {
      width: 120px;
      height: 120px;
      bottom: 25%;
      right: 40%;
      animation-delay: 5s;
    }

    @keyframes float {
      0% { transform: translateY(0); }
      50% { transform: translateY(-30px); }
      100% { transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Font Awesome Icon -->
    <i class="fa-solid fa-face-sad-tear icon"></i>
    <h1>404 - File Not Found</h1>
    <p>Oops! The page you're looking for doesn’t exist.</p>
    <a href="index.php" class="btn"><i class="fa-solid fa-house"></i> Go Home</a>
  </div>

  <!-- Floating circles -->
  <div class="circle small"></div>
  <div class="circle medium"></div>
  <div class="circle large"></div>

</body>
</html>
