<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/HumaCare/config/config.php';

// if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 3) {
//     die("Access Denied");
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        h1, h2 {
            text-align: center;
            color: #136769;
        }

        #medicineFormContainer {
            max-width: 500px;
            background: white;
            padding: 20px;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            display: none; /* Initially hidden */
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        #toggleFormBtn {
            display: block;
            margin: 10px auto;
            background: #007bff;
            color: white;

        }

        #closeFormBtn {
            background: #dc3545;
            color: white;
            margin-top: 10px;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        .editBtn {
            background: #ffc107;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 3px;
        }

        .deleteBtn {
            background: #dc3545;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 3px;
        }

        .editBtn:hover {
            background: #e0a800;
        }

        .deleteBtn:hover {
            background: #c82333;
        }

        #toggleFormBtn:hover{
            background: #b8d2e4;
        }
    </style>
</head>
<body>
    <h1>Manage Medicines</h1>
    <div id="message"></div>
    
    <button id="toggleFormBtn">New</button>

    <div id="medicineFormContainer">
        <form id="medicineForm">
            <input type="hidden" name="medicine_id" id="medicine_id">

            <label>Medicine Name:</label>
            <input type="text" name="medicine_name" id="medicine_name" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="0" required>

            <label>Arrival Date:</label>
            <input type="date" name="arrival_date" id="arrival_date" required>

            <label>Expiry Date:</label>
            <input type="date" name="expiry_date" id="expiry_date" required>
            
            <button type="submit" id="saveBtn">Save</button>
            <button type="button" id="closeFormBtn">Close</button>
        </form>
    </div>

    <h2>Existing Medicines</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Medicine Name</th>
                <th>Quantity</th>
                <th>Expiry Date</th>
                <th>Arrival Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="medicineTable"></tbody>
    </table>

    <script>
        $(document).ready(function () {
            function loadMedicines() {
                $.get("/HumaCare/functions/medicine.controller.php", { action: "fetch" }, function(data) {
                    $("#medicineTable").html(data);
                });
            }
            loadMedicines();

            // Toggle Form
            $("#toggleFormBtn").click(function () {
                $("#medicineFormContainer").slideDown();
                $("#medicineForm")[0].reset();
                $("#medicine_id").val("");
            });

            $("#closeFormBtn").click(function () {
                $("#medicineFormContainer").slideUp();
            });

            $("#medicineForm").submit(function(event) {
                event.preventDefault();
                let formData = $(this).serialize() + "&action=saveBtn";
                $.post("/HumaCare/functions/medicine.controller.php", formData, function(response) {
                    $("#message").html(response);
                    loadMedicines();
                    $("#medicineForm")[0].reset();
                    $("#medicine_id").val("");
                    $("#medicineFormContainer").slideUp(); // Close form after submission
                });
            });

            $(document).on("click", ".editBtn", function() {
                let medicine = $(this).data("medicine");
                $("#medicine_id").val(medicine.medicine_id);
                $("#medicine_name").val(medicine.medicine_name);
                $("#quantity").val(medicine.quantity);
                $("#expiry_date").val(medicine.expiry_date);
                $("#arrival_date").val(medicine.arrival_date);
                $("#medicineFormContainer").slideDown();
                $("#saveBtn").text("Update Medicine").css("background", "#ffc107");
            });

            $(document).on("click", ".deleteBtn", function() {
                if (confirm("Are you sure you want to delete this medicine?")) {
                    let id = $(this).data("id");
                    $.post("/HumaCare/functions/medicine.controller.php", { delete_id: id, action: "delete" }, function(response) {
                        $("#message").html(response);
                        loadMedicines();
                    });
                }
            });
        });
    </script>
</body>
</html>
