<?php
include_once 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

// Detect AJAX requests early
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'INVALID ACCESS', 'detail' => 'Please login to continue.']);
        exit();
    }
    header("Location: login.php");
    exit();
}

$medicalSuccess = $_SESSION['medical_success'] ?? null;
$medicalError = $_SESSION['medical_error'] ?? null;
unset($_SESSION['medical_success'], $_SESSION['medical_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerID = $_POST['customerID'];
    $visit_date = $_POST['visit_date'];
    
    // Prepare the SQL statement
    $conn = connect();
    $historyID = generate_historyID();

    $sql = "INSERT INTO customerMedicalHistory (
            history_id, CustomerID, visit_date, eye_condition, systemic_diseases,
            visual_acuity_right, visual_acuity_left, intraocular_pressure_right,
            intraocular_pressure_left, refraction_right, refraction_left,
            pupillary_distance, current_medications, allergies, family_eye_history,
            previous_eye_surgeries, corneal_topography, fundus_examination, additional_notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Assign POST values to variables first because bind_param requires variables (passed by reference)
        $v_history_id = $historyID;
        $v_customer_id = $customerID;
        $v_visit_date = $visit_date;
        $v_eye_condition = $_POST['eye_condition'] ?? '';
        $v_systemic_diseases = $_POST['systemic_diseases'] ?? '';
        $v_visual_acuity_right = $_POST['visual_acuity_right'] ?? '';
        $v_visual_acuity_left = $_POST['visual_acuity_left'] ?? '';
        $v_intraocular_pressure_right = $_POST['intraocular_pressure_right'] ?? '';
        $v_intraocular_pressure_left = $_POST['intraocular_pressure_left'] ?? '';
        $v_refraction_right = $_POST['refraction_right'] ?? '';
        $v_refraction_left = $_POST['refraction_left'] ?? '';
        $v_pupillary_distance = $_POST['pupillary_distance'] ?? '';
        $v_current_medications = $_POST['current_medications'] ?? '';
        $v_allergies = $_POST['allergies'] ?? '';
        $v_family_eye_history = $_POST['family_eye_history'] ?? '';
        $v_previous_eye_surgeries = $_POST['previous_eye_surgeries'] ?? '';
        $v_corneal_topography = $_POST['corneal_topography'] ?? '';
        $v_fundus_examination = $_POST['fundus_examination'] ?? '';
        $v_additional_notes = $_POST['additional_notes'] ?? '';

        // Bind all parameters as strings to avoid type mismatch issues
        $stmt->bind_param(
            str_repeat('s', 19),
            $v_history_id,
            $v_customer_id,
            $v_visit_date,
            $v_eye_condition,
            $v_systemic_diseases,
            $v_visual_acuity_right,
            $v_visual_acuity_left,
            $v_intraocular_pressure_right,
            $v_intraocular_pressure_left,
            $v_refraction_right,
            $v_refraction_left,
            $v_pupillary_distance,
            $v_current_medications,
            $v_allergies,
            $v_family_eye_history,
            $v_previous_eye_surgeries,
            $v_corneal_topography,
            $v_fundus_examination,
            $v_additional_notes
        );

        if ($stmt->execute()) {
            // Log the activity
            $employee_id = $_SESSION["id"] ?? null;
            if ($employee_id) {
                GenerateLogs($employee_id, $customerID, "Added medical record");
            }

            // If request is AJAX, return JSON; otherwise redirect back
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Medical record added']);
                exit();
            } else {
                header("Location: customerEdit.php?CustomerID=$customerID&success=Medical+record+added+successfully");
                exit();
            }
        } else {
            $err = $stmt->error;
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $err]);
                exit();
            }
            header("Location: customerEdit.php?CustomerID=$customerID&error=" . urlencode('Database error'));
            exit();
        }
    } else {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        header("Location: customerEdit.php?CustomerID=$customerID&error=Database error");
        exit();
    }
} else {
    // If accessed directly via GET/other, return JSON for AJAX or redirect for normal requests
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'INVALID ACCESS', 'detail' => 'Invalid request method']);
        exit();
    }
    // Redirect if accessed directly by browser
    header("Location: customerRecords.php");
    exit();
}

function GenerateLogs($employee_id, $customerID, $action) {
    $conn = connect();
    $sql = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $logID = generate_LogsID();
        $activityCode = 3; // Assuming 3 corresponds to 'Added' in activityMaster
        $logTargetType = 'customer'; // Valid ENUM value
        $description = "$action for customer ID: $customerID";
        $upd_dt = date("Y-m-d H:i:s"); // Optional: Can be omitted to use DEFAULT
        
        // Correct format string: "iiisiss"
        $stmt->bind_param("iiisiss", 
            $logID, 
            $employee_id, 
            $customerID, 
            $logTargetType, 
            $activityCode, 
            $description, 
            $upd_dt // Remove this line if using DEFAULT
        );
        
        if (!$stmt->execute()) {
            error_log("Failed to insert log: {$stmt->error}");
        }
    }
    $stmt->close();
    $conn->close();
}
?>