<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $status = 'active';
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $address = $_POST['address'] ?? '';
    $clinic_id = $_POST['clinic_id'] ?? null; // Set NULL if not provided
    $created_at = date('Y-m-d H:i:s');

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit;
    }

    // Check if username or email already exists
    $check_stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check_stmt->execute([$username, $email]);
    $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        if ($existing_user['username'] === $username) {
            $_SESSION['error'] = "Username already exists. Please choose another.";
        } elseif ($existing_user['email'] === $email) {
            $_SESSION['error'] = "Email is already registered. Try logging in.";
        }
        header("Location: register.php");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role_id, status, firstname, lastname, address, clinic_id, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$username, $hashed_password, $email, $role_id, $status, $firstname, $lastname, $address, $clinic_id, $created_at])) {
        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: register.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | HumaCare</title>
    <link rel="stylesheet" href="../assets/r.styles.css">
</head>
<body>
    <div class="register-container">
        <div class="register-form">
            <h2>Create an Account</h2>
            <p>Join us today</p>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <form action="register.php" method="post">

                <div class="form-group">
                    <div>
                        <label for="firstname">First Name</label>
                        <input type="text" name="firstname" id="firstname" required>
                    </div>
                    <div>
                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="role">Role</label>
                        <select name="role_id" id="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Health Worker</option>
                            <option value="3">Staff</option>
                        </select>
                    </div>
                    <div>
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div>
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn">Register</button>
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>
</body>
</html>
