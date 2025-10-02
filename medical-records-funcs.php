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
        // Bind all parameters as strings to avoid type mismatch issues
        $stmt->bind_param(
            str_repeat('s', 19),
            $historyID,
            $customerID,
            $visit_date,
            $_POST['eye_condition'] ?? null,
            $_POST['systemic_diseases'] ?? null,
            $_POST['visual_acuity_right'] ?? null,
            $_POST['visual_acuity_left'] ?? null,
            $_POST['intraocular_pressure_right'] ?? null,
            $_POST['intraocular_pressure_left'] ?? null,
            $_POST['refraction_right'] ?? null,
            $_POST['refraction_left'] ?? null,
            $_POST['pupillary_distance'] ?? null,
            $_POST['current_medications'] ?? null,
            $_POST['allergies'] ?? null,
            $_POST['family_eye_history'] ?? null,
            $_POST['previous_eye_surgeries'] ?? null,
            $_POST['corneal_topography'] ?? null,
            $_POST['fundus_examination'] ?? null,
            $_POST['additional_notes'] ?? null
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