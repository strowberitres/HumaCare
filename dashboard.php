<?php
session_start();
require '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || $_SESSION['user_id'] != $_GET['id']) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch statistics
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
// $totalClinics = $pdo->query("SELECT COUNT(*) FROM clinics")->fetchColumn();
// $totalHealthWorkers = $pdo->query("SELECT COUNT(*) FROM health_workers")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- FontAwesome Icons -->
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            margin-top: 20px;
        }
        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 180px;
            height: 180px;
            font-size: 18px;
            font-weight: bold;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 3px 3px 10px rgba(0,0,0,0.2);
        }
        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .btn i {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .stat-box {
            background-color: #f8f9fa;
            padding: 15px;
            width: 150px;
            border-radius: 8px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>

        <div class="stats-container">
            <div class="stat-box">Patients: <?php echo $totalPatients; ?></div>
            <div class="stat-box">Appointments: <?php echo $totalAppointments; ?></div>
            <!-- <div class="stat-box">Clinics: <?php echo $totalClinics; ?></div> -->

        </div>

        <div class="button-container">
            <a href="../functions/manage.users.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="../functions/manage.appointments.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-calendar-check"></i> Manage Appointments
            </a>
            <a href="../functions/manage.medicine.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-pills"></i> Medicine Stocks
            </a>
            <a href="../functions/manage.patients.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-procedures"></i> View Patients
            </a>
            <a href="view_clinics.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-hospital"></i> View Clinics
            </a>
            <a href="settings.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn">
                <i class="fas fa-cogs"></i> Settings
            </a>
        </div>

        <canvas id="statsChart" style="margin-top: 30px;"></canvas>

        <div style="margin-top: 20px;">
            <a href="logout.php" class="btn" style="background-color: red;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('statsChart').getContext('2d');
        var statsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Patients', 'Appointments'],
                datasets: [{
                    label: 'Statistics',
                    data: [<?php echo $totalPatients; ?>, <?php echo $totalAppointments; ?> ],
                    backgroundColor: ['blue', 'green', 'red', 'purple']
                }]
            }
        });
    </script>
</body>
</html>
