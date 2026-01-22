<?php
session_start();
require 'config/config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

// Fetch user role
$stmt = $pdo->prepare("SELECT role_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../view/login.php");
    exit;
}

$role_id = $user['role_id'];

// Define role-based access
$permissions = [
    1 => ['dashboard', 'users', 'patients', 'appointments', 'clinics', 'health_workers', 'health_services', 'medicine_stocks', 'immunization_records', 'family_details', 'sibling_details', 'roles'], // Admin
    2 => ['dashboard', 'patients', 'appointments', 'health_services', 'immunization_records'], // Health Worker
    3 => ['dashboard', 'clinics', 'medicine_stocks', 'appointments'], // Clinic Staff
];

// Get requested page
$page = $_GET['page'] ?? 'dashboard';

// Normalize page names (fix mismatch like "manage.users" -> "users")
$page_map = [
    'manage.users' => 'users',
    'manage.appointments' => 'appointments',
    'manage.patients' => 'patients',
    'manage.clinics' => 'clinics',
    'manage.health_services' => 'health_services',
    'manage.medicine' => 'medicine_stocks',
];

$page = $page_map[$page] ?? $page; // Convert requested page if needed

// Check if user has permission
if (!in_array($page, $permissions[$role_id])) {
    header("Location: access_denied.php");
    exit;
}

// Include requested page
$file = "pages/$page.php";
if (file_exists($file)) {
    include $file;
} else {
    echo "<h1>Page not found</h1>";
}
?>
