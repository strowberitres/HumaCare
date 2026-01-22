<?php
session_start();
require '../config/config.php'; // Database connection

// Ensure only Admin (role_id = 1) and Health Worker (role_id = 2) can manage users
// if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 3)) {
//     die(json_encode(["success" => false, "message" => "Access denied."]));
// }

// Fetch all users
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetch') {
    $stmt = $pdo->query("SELECT user_id, firstname, lastname, username, email, role_id, status, address, created_at FROM users");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Add a new userx  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    require '../config/config.php';

    $user_id = $_POST['user_id'];
    $firstname = ucwords($_POST['firstname']);
    $lastname = ucwords($_POST['lastname']);
    $username = ($_POST['username']);
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];
    $status = $_POST['status'];
    $address = ucwords($_POST['address']);
    
    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, email, password_hash, role_id, status, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$firstname, $lastname, $username, $email, $password, $role_id, $status, $address]);

    if ($result) {
        echo json_encode(["success" => true, "message" => "User added successfully!"]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Failed to add user.", 
            "error" => $stmt->errorInfo() // Get SQL error
        ]);
    }
    exit;
}

// Update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    require '../config/config.php';

    $user_id = $_POST['user_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];
    $status = $_POST['status'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, username = ?, email = ?, role_id = ?, status = ?, address = ? WHERE user_id = ?");
    $result = $stmt->execute([$firstname, $lastname, $username, $email, $role_id, $status, $address, $user_id]);

    if ($result) {
        echo json_encode(["success" => true, "message" => "User updated successfully!"]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Failed to update user.", 
            "error" => $stmt->errorInfo() // Get SQL error
        ]);
    }
    exit;
}

// Delete a user
$action = $_POST['action'] ?? null; // Ensure $action is set

if ($action === 'delete') {
    $delete_id = $_POST['delete_id'] ?? null;
    if ($delete_id) {
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$delete_id]);
        echo "<p style='color: red;'>User deleted successfully</p>";
    } else {
        echo "<p style='color: red;'>Error: No ID provided</p>";
    }
    exit();
}


?>
