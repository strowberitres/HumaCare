<?php
require $_SERVER['DOCUMENT_ROOT'] . '/HumaCare/config/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $appointment_date = $_POST['appointment_date'] ?? null;
    $appointment_time = $_POST['appointment_time'] ?? null;
    $status = $_POST['status'] ?? null;
    $remarks = $_POST['remarks'] ?? null;
    $delete_id = $_POST['delete_id'] ?? null;
    $delete_multiple = $_POST['delete_multiple'] ?? null;

    if (!empty($delete_id)) {
        $query = "DELETE FROM appointments WHERE appointment_id = ?";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$delete_id])) {
            echo "<p style='color: red;'>Appointment deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting appointment!</p>";
        }
        exit;
    }

    if (!empty($delete_multiple) && is_array($delete_multiple)) {
        $placeholders = implode(',', array_fill(0, count($delete_multiple), '?'));
        $query = "DELETE FROM appointments WHERE appointment_id IN ($placeholders)";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($delete_multiple)) {
            echo "<p style='color: red;'>Selected appointments deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting selected appointments!</p>";
        }
        exit;
    }

    if (!empty($appointment_id)) {
        $query = "UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = ?, remarks = ? WHERE appointment_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$appointment_date, $appointment_time, $status, $remarks, $appointment_id]);
        echo "<p style='color: green;'>Appointment updated successfully!</p>";
    } else {
        $query = "INSERT INTO appointments (appointment_date, appointment_time, status, remarks) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$appointment_date, $appointment_time, $status, $remarks]);
        echo "<p style='color: green;'>Appointment added successfully!</p>";
    }
    exit;
}

// FETCH APPOINTMENTS
$query = "SELECT * FROM appointments";
$stmt = $pdo->prepare($query);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($appointments as $row) {
    echo "<tr>
            <td><input type='checkbox' class='selectCheckbox' value='" . htmlspecialchars($row['appointment_id']) . "'></td>
            <td>" . htmlspecialchars($row['appointment_id']) . "</td>
            <td>" . htmlspecialchars($row['appointment_date']) . "</td>
            <td>" . htmlspecialchars($row['appointment_time']) . "</td>
            <td>" . htmlspecialchars($row['status']) . "</td>
            <td>" . htmlspecialchars($row['remarks']) . "</td>
            <td>
                <button class='editBtn' 
                    data-id='" . htmlspecialchars($row['appointment_id']) . "' 
                    data-date='" . htmlspecialchars($row['appointment_date']) . "' 
                    data-time='" . htmlspecialchars($row['appointment_time']) . "' 
                    data-status='" . htmlspecialchars($row['status']) . "' 
                    data-remarks='" . htmlspecialchars($row['remarks']) . "'>
                    Edit
                </button>
                <button class='deleteBtn' data-id='" . htmlspecialchars($row['appointment_id']) . "'>Delete</button>
            </td>
          </tr>";
}
?>
