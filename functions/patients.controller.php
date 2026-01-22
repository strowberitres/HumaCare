<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require $_SERVER['DOCUMENT_ROOT'] . '/HumaCare/config/config.php';

$action = isset($_POST['action']) ? $_POST['action'] : ''; // Define $action properly

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_patient') {
    require '../config/config.php';

    // Capture patient details
    $first_name = ucwords(trim($_POST['first_name']));
    $last_name = ucwords(trim($_POST['last_name']));
    $sex = $_POST['sex'];
    $birth_date = $_POST['birth_date'];
    $birth_weight = $_POST['birth_weight'];
    $place_of_delivery = ucwords(trim($_POST['place_of_delivery']));
    $registered_at_civil_registry = $_POST['registered_at_civil_registry'];
    $address = ucwords(trim($_POST['address']));

    // Handle photo upload
    $photo_filename = null;
    if (isset($_FILES['photo_op']) && $_FILES['photo_op']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp_name = $_FILES['photo_op']['tmp_name'];
        $photo_name = basename($_FILES['photo_op']['name']);
        $photo_extension = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (in_array($photo_extension, $allowed_extensions)) {
            $photo_filename = uniqid('patient_', true) . '.' . $photo_extension;
            $photo_upload_path = '../uploads/' . $photo_filename;

            if (!move_uploaded_file($photo_tmp_name, $photo_upload_path)) {
                echo json_encode(["success" => false, "message" => "Failed to upload photo."]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid photo format. Only JPG, JPEG, and PNG allowed."]);
            exit;
        }
    }

    // Capture family details
    $mother_name = ucwords(trim($_POST['mother_name']));
    $mother_occupation = ucwords(trim($_POST['mother_occupation']));
    $father_name = ucwords(trim($_POST['father_name']));
    $father_occupation = ucwords(trim($_POST['father_occupation']));
    $guardian_name = ucwords(trim($_POST['guardian_name']));
    $guardian_relationship = ucwords(trim($_POST['guardian_relationship']));
    $contact_no = trim($_POST['contact_no']);

    // Capture immunization records
    $immunization_data = [
        'bcg_date' => $_POST['bcg_date'],
        'dpt_1_date' => $_POST['dpt_1_date'],
        'dpt_2_date' => $_POST['dpt_2_date'],
        'dpt_3_date' => $_POST['dpt_3_date'],
        'opv_1_date' => $_POST['opv_1_date'],
        'opv_2_date' => $_POST['opv_2_date'],
        'opv_3_date' => $_POST['opv_3_date'],
        'hepatitis_b_1_date' => $_POST['hepatitis_b_1_date'],
        'hepatitis_b_2_date' => $_POST['hepatitis_b_2_date'],
        'hepatitis_b_3_date' => $_POST['hepatitis_b_3_date'],
        'measles_date' => $_POST['measles_date'],
        'vitamin_a_date' => $_POST['vitamin_a_date']
    ];

    // Health Services
    $deworming_date = $_POST['deworming_date'];
    $dental_checkup_date = $_POST['dental_checkup_date'];

    // Capture sibling details
    $sibling_names = $_POST['sibling_name'] ?? [];
    $sibling_ages = $_POST['sibling_age'] ?? [];

    try {
        $pdo->beginTransaction();

        // Insert into `patients`
        $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, sex, birth_date, birth_weight, place_of_delivery, registered_at_civil_registry, address, photo_op) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $sex, $birth_date, $birth_weight, $place_of_delivery, $registered_at_civil_registry, $address, $photo_filename]);
        $patient_id = $pdo->lastInsertId();

        // Insert into `family_details`
        $stmt = $pdo->prepare("INSERT INTO family_details (patient_id, mother_name, mother_occupation, father_name, father_occupation, guardian_name, guardian_relationship, contact_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $mother_name, $mother_occupation, $father_name, $father_occupation, $guardian_name, $guardian_relationship, $contact_no]);

        // Insert into `immunization_records`
        $stmt = $pdo->prepare("INSERT INTO immunization_records (patient_id, bcg_date, dpt_1_date, dpt_2_date, dpt_3_date, opv_1_date, opv_2_date, opv_3_date, hepatitis_b_1_date, hepatitis_b_2_date, hepatitis_b_3_date, measles_date, vitamin_a_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_merge([$patient_id], array_values($immunization_data)));

        // Insert into `health_services`
        $stmt = $pdo->prepare("INSERT INTO health_services (patient_id, deworming_date, dental_checkup_date) VALUES (?, ?, ?)");
        $stmt->execute([$patient_id, $deworming_date, $dental_checkup_date]);

        // Insert siblings
        if (!empty($sibling_names)) {
            $stmt = $pdo->prepare("INSERT INTO sibling_details (patient_id, sibling_name, sibling_age) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($sibling_names); $i++) {
                if (!empty($sibling_names[$i]) && is_numeric($sibling_ages[$i])) {
                    $stmt->execute([$patient_id, ucwords(trim($sibling_names[$i])), (int)$sibling_ages[$i]]);
                }
            }
        }

        $pdo->commit();

        // Store success message in session
        $_SESSION['success_message'] = "Patient added successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }

    // Redirect back to the same page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Check if siblings are sent
if (!empty($_POST["sibling_name"]) && !empty($_POST["sibling_age"])) {
    $sibling_names = $_POST["sibling_name"]; // Array of sibling names
    $sibling_ages = $_POST["sibling_age"];   // Array of sibling ages

    for ($i = 0; $i < count($sibling_names); $i++) {
        $name = $sibling_names[$i];
        $age = $sibling_ages[$i];

        // Validate before inserting into database
        if (!empty($name) && is_numeric($age)) {
            // Insert each sibling into the database (adjust table and columns)
            $query = "INSERT INTO sibling_details (patient_id, sibling_name, sibling_age) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $patient_id, $name, $age);
            $stmt->execute();
        }
    }
}

// Edit Patient (for AJAX update)
if ($action === "edit_patient") {
    try {
        $pdo->beginTransaction();
        
        $patient_id = $_POST['patient_id'];
        $first_name = ucwords(trim($_POST['first_name']));
        $last_name = ucwords(trim($_POST['last_name']));
        $sex = $_POST['sex'];
        $birth_date = $_POST['birth_date'];
        $birth_weight = $_POST['birth_weight'];
        $place_of_delivery = ucwords(trim($_POST['place_of_delivery']));
        $registered_at_civil_registry = isset($_POST['registered_at_civil_registry']) ? 1 : 0;
        $address = ucwords(trim($_POST['address']));

        // Fetch old photo filename
        $stmt = $pdo->prepare("SELECT photo_op FROM patients WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        $old_photo = $stmt->fetchColumn();

        // Handle new photo upload
        $photo_filename = $old_photo; // Default to old photo
        if (isset($_FILES['photo_op']) && $_FILES['photo_op']['error'] === UPLOAD_ERR_OK) {
            $photo_tmp_name = $_FILES['photo_op']['tmp_name'];
            $photo_name = basename($_FILES['photo_op']['name']);
            $photo_extension = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png'];

            if (in_array($photo_extension, $allowed_extensions)) {
                $photo_filename = uniqid('patient_', true) . '.' . $photo_extension;
                $photo_upload_path = '../uploads/' . $photo_filename;

                if (move_uploaded_file($photo_tmp_name, $photo_upload_path)) {
                    // Delete old photo if exists
                    if ($old_photo && file_exists("../uploads/" . $old_photo)) {
                        unlink("../uploads/" . $old_photo);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to upload new photo."]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Invalid photo format. Only JPG, JPEG, and PNG allowed."]);
                exit;
            }
        }

        // Update patient in database
        $stmt = $pdo->prepare("UPDATE patients SET 
            first_name = ?, last_name = ?, sex = ?, birth_date = ?, birth_weight = ?, 
            place_of_delivery = ?, registered_at_civil_registry = ?, address = ?, photo_filename = ? 
            WHERE patient_id = ?");
        $stmt->execute([
            $first_name, $last_name, $sex, $birth_date, $birth_weight,
            $place_of_delivery, $registered_at_civil_registry, $address, $photo_filename, $patient_id
        ]);

        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Patient updated successfully"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit();
}

if ($action === "fetch_patients") {
    $stmt = $pdo->query("SELECT * FROM patients");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = "";

    foreach ($patients as $index => $patient) {
        // Ensure the image path is correct
     
        $output .= "<tr>
            <td>" . ($index + 1) . "</td>
            <td>{$patient['first_name']}</td>
            <td>{$patient['last_name']}</td>
            <td>{$patient['sex']}</td>
            <td>{$patient['birth_date']}</td>
            <td>{$patient['birth_weight']}</td>
            <td>{$patient['place_of_delivery']}</td>
            <td>{$patient['registered_at_civil_registry']}</td>
            <td>{$patient['address']}</td>
            <td>
                <button class='deletePatient' data-id='{$patient['patient_id']}'>Delete</button>
            </td>
        </tr>";
    }

    if (empty($output)) {
        $output = "<tr><td colspan='11'>No patients found.</td></tr>";
    }

    echo $output;
    exit();
}


if ($action === "delete_patient") {
    try {
        $pdo->beginTransaction();
        $patient_id = $_POST['patient_id'];

        // Check if patient exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        $patient_exists = $stmt->fetchColumn();

        if (!$patient_exists) {
            echo json_encode(["success" => false, "message" => "Error: Patient not found."]);
            exit();
        }

        // Delete related records in other tables
        $relatedTables = ['family_details', 'sibling_details', 'immunization_records', 'health_services'];
        foreach ($relatedTables as $table) {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
        }

        // Delete patient record
        $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->execute([$patient_id]);

        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Patient deleted successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

