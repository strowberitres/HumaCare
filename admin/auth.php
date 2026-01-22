<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password_hash'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User not found in the database.";
        header("Location: ../view/login.php");
        exit;
    }

    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['logged_in'] = true;

        // Redirect to index.php without exposing user_id in the URL
        header("Location: ../index.php");
        // header("Location: ../index.php?page=user&id=" . $_SESSION['user_id']);
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: ../view/login.php");
        exit;
    }
}
?>
