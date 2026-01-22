<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password_hash'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "User found! Checking password...<br>";

        if (password_verify($password, $user['password_hash'])) {
            echo "Password correct! Redirecting...<br>";

            // ✅ Store user data in session properly
            $_SESSION['user_id'] = $user['id'];  
            $_SESSION['role'] = $user['role'];    
            $_SESSION['logged_in'] = true;

            header("Location: ../index.php");
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }
}
?>

<!-- LOGIN FORM -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HumaCare</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="../assets/pics/logo.png" alt="">
                <h1>HumaCare</h1>
            </div>
            <h2>Welcome Back</h2>
            <p>Please enter your details</p>
            <form action="../admin/auth.php" method="post">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
                
                <label for="password">Password</label>
                <input type="password" name="password_hash" id="password" required>
                
                <!-- <div class="remember-me">
                    <input type="checkbox" id="remember"> 
                    <label for="remember">Remember for 30 days</label>
                </div>
                 -->
                <button type="submit" class="btn">Sign In</button>
            </form>
            <!-- <a href="forgot_password.php" class="forgot-password">Forgot password?</a> -->
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
        <div class="login-illustration">
            <img src="../assets/pics/illustration.gif" alt="Login Illustration">
        </div>
    </div>
</body>
</html>

<style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #f5f5f5;
    }
    .login-container {
        display: flex;
        width: 800px;
        background: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    .login-form {
        flex: 1;
        padding: 40px;
    }
    .login-form h2 {
        color: #333;
    }
    .login-form p {
        color: #777;
        margin-bottom: 20px;
    }
    .login-form input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .btn {
        width: 100%;
        padding: 10px;
        background: #136769;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn:hover {
        background: #b8d2e4;
    }
    .forgot-password {
        display: block;
        margin-top: 10px;
        text-align: right;
        color: #6a0dad;
    }
    .login-illustration {
        flex: 1;
        background: #136769;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-illustration img {
        width: 80%;
    }
    .logo{
        display:flex;
    }
    .logo img{
        width: 40px;
        height: 40px
    }
    .logo h1{
        font-size:15px;
        color: #136769;
    }
</style>
