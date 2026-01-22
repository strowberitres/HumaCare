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
    <title>Manage Patients</title>
    <link rel="stylesheet" href="/HumaCare/assets/patientcss.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <h2 class="title">Registered Patients</h2>
    <table border="1" id="patientsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Sex</th>
                <th>Birth Date</th>
                <th>Birth Weight</th>
                <th>Place of Delivery</th>
                <th>Civil Registry</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="patientsList">
        </tbody>
    </table>

    <script>
    $(document).ready(function() {
        function loadPatients() {
            $.ajax({
                url: "/HumaCare/functions/patients.controller.php",
                type: "POST",
                data: { action: "fetch_patients" },
                success: function (response) {
                    $("#patientsTable tbody").html(response); 
                },
                error: function (xhr, status, error) {
                    console.error("Fetch Patients Error:", status, error);
                }
            });
        }

        loadPatients();

        $(document).on("click", ".deletePatient", function () {
            var patientId = $(this).data("id");

            if (confirm("Are you sure you want to delete this patient?")) {
                $.ajax({
                    url: "/HumaCare/functions/patients.controller.php",
                    type: "POST",
                    data: { action: "delete_patient", patient_id: patientId },
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || "Patient deleted successfully.");
                            loadPatients();
                        } else {
                            alert(response.message || "Failed to delete patient.");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        alert("An error occurred while deleting.");
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
