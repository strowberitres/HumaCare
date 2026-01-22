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
    <title>Manage Appointments</title>
    <!-- <link rel="stylesheet" href="../assets/style.css"> -->
    <style>

        /* Headings */
        h1, h2 {
            text-align: center;
            color: #136769;
            padding: 5px;
        }

        /* Buttons */
        button, #toggleFormBtn, #closeFormBtn, #deleteSelectedBtn, .saveBtn {
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }

        /* Toggle Form Button */
        #toggleFormBtn {
            background-color: #007bff;
            color: white;
            width: 150px; /* Consistent width */
        }

        #toggleFormBtn:hover {
            background-color: #0056b3;
        }

        /* Save Button */
        .saveBtn {
            background-color: #28a745;
            color: white;
            width: 100%;
            display: block;
            margin-top: 10px;
        }

        .saveBtn:hover {
            background-color: #218838;
        }

        /* Close Form Button */
        #closeFormBtn {
            background-color: #dc3545;
            color: white;
            width: 100%;
            margin-top: 10px;
        }

        #closeFormBtn:hover {
            background-color: #c82333;
        }

        /* Delete Selected Button */
        #deleteSelectedBtn {
            background-color: #dc3545;
            color: white;
            width: 150px;
            margin: 10px auto;
            display: block;
        }

        #deleteSelectedBtn:hover {
            background-color: #c82333;
        }

        /* Search Bar */
        #searchBar {
            padding: 10px;
            width: 70%;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
            margin: 10px auto;
        }

        /* Appointment Form */
        #appointmentFormContainer {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            width: 400px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 16px;
            text-align: center; /* Centers all content */
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center; /* Center align table data */
        }

        th {
            background-color: #136769;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e6f7ff;
        }

        /* Checkbox Scaling */
        input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .editBtn, .deleteBtn {
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .editBtn {
            background-color: #ffc107;
            color: black;
        }

        .editBtn:hover {
            background-color: #e0a800;
        }

        .deleteBtn {
            background-color: #dc3545;
            color: white;
        }

        .deleteBtn:hover {
            background-color: #c82333;
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Manage Appointments</h1>

    <div class="header">
        <button id="toggleFormBtn">New</button>
        <!-- <input type="text" id="searchBar" placeholder="Search appointments..."> -->
    </div>

    <div id="appointmentFormContainer" style="display: none;">
        <form id="appointmentForm">
            <input type="hidden" id="appointment_id" name="appointment_id">
            
            <label for="appointment_date">Date:</label>
            <input type="date" id="appointment_date" name="appointment_date" required>

            <label for="appointment_time">Time:</label>
            <input type="time" id="appointment_time" name="appointment_time" required>

            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>


            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks"></textarea>

            <button type="submit">Save</button>
            <button type="button" id="closeFormBtn">Close</button> <!-- Close button added -->
        </form>
        <div id="error-message"></div>

    </div>

    
    <h2>Existing Appointments</h2>
        <button id="deleteSelectedBtn" disabled>Delete Selected</button>
        <table id="appointmentsTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>#</th>
                    <th>Appointment Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Actions</th>    
                </tr>
            </thead>
            <tbody id="appointmentsList"></tbody>
        </table>

        <script>

            $(document).ready(function () {
                loadAppointments();

                $("#toggleFormBtn").click(function () {
                    $("#appointmentFormContainer").show();
                    $("#appointmentForm")[0].reset();
                    $("#appointment_id").val("");
                    $("#error-message").html(""); // Clear error messages
                });

                $("#closeFormBtn").click(function () {
                    $("#appointmentFormContainer").hide();
                });

                function loadAppointments() {
                    $.get("/HumaCare/functions/appointments.controller.php", function (data) {
                        $("#appointmentsList").html(data);
                    });
                }

                $("#appointmentForm").submit(function (event) {
                    event.preventDefault();
                    let date = $("#appointment_date").val();
                    let time = $("#appointment_time").val();
                    let currentYear = new Date().getFullYear();
                    let selectedYear = new Date(date).getFullYear();
                    let errorMessage = "";

                    // Validate Year (Must be the current year)
                    if (selectedYear !== currentYear) {
                        errorMessage += "Error: The appointment year must be " + currentYear + ".<br>";
                    }

                    // Validate Time (Must be between 9 AM - 5 PM)
                    let [hour, minute] = time.split(":").map(Number);
                    if (hour < 9 || hour > 17 || (hour === 17 && minute > 0)) {
                        errorMessage += "Error: The appointment time must be between 9 AM and 5 PM.<br>";
                    }

                    if (errorMessage) {
                        $("#error-message").html("<p style='color: red;'>" + errorMessage + "</p>");
                        return;
                    }

                    $.post("/HumaCare/functions/appointments.controller.php", $(this).serialize(), function (response) {
                        $("#message").html(response);
                        loadAppointments();
                        $("#appointmentForm")[0].reset();
                        $("#appointment_id").val('');
                        $("#appointmentFormContainer").hide();
                    });
                });

                $(document).on("click", ".editBtn", function () {
                    let id = $(this).data("id");
                    let date = $(this).data("date");
                    let time = $(this).data("time");
                    let status = $(this).data("status");
                    let remarks = $(this).data("remarks");

                    $("#appointment_id").val(id);
                    $("#appointment_date").val(date);
                    $("#appointment_time").val(time);
                    $("#status").val(status);
                    $("#remarks").val(remarks);
                    $("#error-message").html(""); // Clear error messages

                    $("#appointmentFormContainer").show();
                });

                $(document).on("click", ".deleteBtn", function () {
                    if (confirm("Are you sure you want to delete this appointment?")) {
                        let id = $(this).data("id");
                        $.post("/HumaCare/functions/appointments.controller.php", { delete_id: id }, function (response) {
                            $("#message").html(response);
                            loadAppointments();
                        });
                    }
                });

                $(document).on("change", "#selectAll", function () {
                    $(".selectCheckbox").prop("checked", $(this).prop("checked"));
                    toggleDeleteSelectedButton();
                });

                $(document).on("change", ".selectCheckbox", function () {
                    toggleDeleteSelectedButton();
                });

                $("#deleteSelectedBtn").click(function () {
                    if (confirm("Are you sure you want to delete selected appointments?")) {
                        let selectedIds = $(".selectCheckbox:checked").map(function () {
                            return $(this).val();
                        }).get();

                        $.post("/HumaCare/functions/appointments.controller.php", { delete_multiple: selectedIds }, function (response) {
                            $("#message").html(response);
                            loadAppointments();
                        });
                    }
                });

                function toggleDeleteSelectedButton() {
                    let anyChecked = $(".selectCheckbox:checked").length > 0;
                    $("#deleteSelectedBtn").prop("disabled", !anyChecked);
                }
            });

</script>



</body>
</html>