<?php
session_start();
require 'config/config.php';

// Redirect to login.php if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch statistics
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

// Get the page parameter for switch case navigation
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">


</head>
<body>
        <div class="sidebar">
            <div class="logo">
                <img src="assets/pics/logo_white.png" alt="Logo">
                <h3 class="title1">HumaCare</h3>
            </div>
            
            <a href="index.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
            <a href="index.php?page=users" class="<?= ($page == 'users') ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Manage Users
            </a>
            <a href="index.php?page=appointments" class="<?= ($page == 'appointments') ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-check"></i> Appointments
            </a>
            <a href="index.php?page=medicine" class="<?= ($page == 'medicine') ? 'active' : '' ?>">
                <i class="fa-solid fa-pills"></i> Medicine Stocks
            </a>
            <a href="index.php?page=patients" class="<?= ($page == 'patients') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-injured"></i> Patients
            </a>
            <a href="index.php?page=clinics" class="<?= ($page == 'clinics') ? 'active' : '' ?>">
                <i class="fa-solid fa-hospital"></i> Clinics
            </a>
            <a href="index.php?page=settings" class="<?= ($page == 'settings') ? 'active' : '' ?>">
                <i class="fa-solid fa-cog"></i> Settings
            </a>
            <a href="admin/logout.php" class="logout">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>


        <div class="main-content">
            <div class="header">
                <h1>Welcome, <span><?php echo htmlspecialchars($user['username']); ?></span> 👋</h1>
                <p id="date-time"></p>
            </div>

            <?php
        switch ($page) {
            case 'users': 
                include 'functions/manage.users.php'; 
                break;
            case 'appointments': 
                include 'functions/manage.appointments.php'; 
                break;
            case 'medicine': 
                include 'functions/manage.medicine.php'; 
                break;
            case 'patients': 
                include 'functions/manage.patients.php'; 
                break;
            case 'clinics': 
                include 'view/clinics.php'; // Ensure correct file path
                break;
            case 'settings': 
                include 'view/settings.php'; // Ensure correct file path
                break;
            case 'dashboard':
            default:
                ?>
                <div class="stats-container">
                    <div class="stat-box">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients:</span> <?php echo $totalPatients; ?>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments:</span> <?php echo $totalAppointments; ?>
                    </div>
                </div>

                <div class="chart-container">
                    <?php if ($totalPatients > 0 || $totalAppointments > 0): ?>
                        <canvas id="statsChart"></canvas>
                    <?php else: ?>
                        <p class="no-data">No data available for chart.</p>
                    <?php endif; ?>
                </div>

                <script>
                    function updateDateTime() {
                        const now = new Date();
                        document.getElementById('date-time').innerText = now.toLocaleDateString('en-US', {
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                        }) + " | " + now.toLocaleTimeString();
                    }
                    setInterval(updateDateTime, 1000);
                    updateDateTime();

                    <?php if ($totalPatients > 0 || $totalAppointments > 0): ?>
                    var ctx = document.getElementById('statsChart').getContext('2d');
                    var statsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Patients', 'Appointments'],
                            datasets: [{
                                label: 'Total Count',
                                data: [<?php echo $totalPatients; ?>, <?php echo $totalAppointments; ?>],
                                backgroundColor: ['#136769', '#0f5658'],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true, position: 'top' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });
                    <?php endif; ?>
                </script>
            <?php
                break;
        }
        ?>


</body>
</html>
