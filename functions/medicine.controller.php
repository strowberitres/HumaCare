<?php
require $_SERVER['DOCUMENT_ROOT'] . '/HumaCare/config/config.php';
session_start();

// Ensure action is set before accessing it
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" || isset($_GET["action"])) {
    try {
        if ($action === 'saveBtn') { // Use $action instead of $_POST['action']
            $medicine_id = $_POST['medicine_id'] ?? null;
            $medicine_name = $_POST['medicine_name'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $expiry_date = $_POST['expiry_date'] ?? '';
            $arrival_date = $_POST['arrival_date'] ?? '';

            if (!empty($medicine_id) && $medicine_id !== "0") { // Ensure it's an existing record
                // Update query
                $query = "UPDATE medicine_stocks SET medicine_name = ?, quantity = ?, expiry_date = ?, arrival_date = ? WHERE medicine_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$medicine_name, $quantity, $expiry_date, $arrival_date, $medicine_id]);
                echo "<p style='color: green;'>Medicine updated successfully</p>";
            } else {
                // Insert query
                $query = "INSERT INTO medicine_stocks (medicine_name, quantity, expiry_date, arrival_date) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$medicine_name, $quantity, $expiry_date, $arrival_date]);
                echo "<p style='color: green;'>Medicine added successfully</p>";
            }
            exit();
        }

        if ($action === 'delete') {
            $delete_id = $_POST['delete_id'] ?? null;
            if ($delete_id) {
                $query = "DELETE FROM medicine_stocks WHERE medicine_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$delete_id]);
                echo "<p style='color: red;'>Medicine deleted successfully</p>";
            } else {
                echo "<p style='color: red;'>Error: No ID provided</p>";
            }
            exit();
        }

        if ($action === 'fetch') {
            $query = "SELECT * FROM medicine_stocks";
            $stmt = $pdo->query($query);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($medicines as $row) {
                echo "<tr>
                        <td>{$row['medicine_id']}</td>
                        <td>{$row['medicine_name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['expiry_date']}</td>
                        <td>{$row['arrival_date']}</td>
                        <td>
                            <button class='editBtn' data-medicine='" . json_encode($row) . "'>Edit</button>
                            <button class='deleteBtn' data-id='{$row['medicine_id']}'>Delete</button>
                        </td>
                      </tr>";
            }
            exit();
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        exit();
    }
}
?>
