<?php
include_once 'setup.php';

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
        $stmt->bind_param(
            "iissssssddsssssssss",
            $historyID,
            $customerID,
            $visit_date,
            $_POST['eye_condition'],
            $_POST['systemic_diseases'],
            $_POST['visual_acuity_right'],
            $_POST['visual_acuity_left'],
            $_POST['intraocular_pressure_right'],
            $_POST['intraocular_pressure_left'],
            $_POST['refraction_right'],
            $_POST['refraction_left'],
            $_POST['pupillary_distance'],
            $_POST['current_medications'],
            $_POST['allergies'],
            $_POST['family_eye_history'],
            $_POST['previous_eye_surgeries'],
            $_POST['corneal_topography'],
            $_POST['fundus_examination'],
            $_POST['additional_notes']
        );
        
        if ($stmt->execute()) {
            // Log the activity
            $employee_id = $_SESSION["id"];
            GenerateLogs($employee_id, $customerID, "Added medical record");
            
            // Redirect back to the customer profile with success message
            header("Location: customerEdit.php?CustomerID=$customerID&success=Medical record added successfully");
            exit();
        } else {
            // Redirect back with error message
            header("Location: customerEdit.php?CustomerID=$customerID&error=Error adding medical record");
            exit();
        }
    } else {
        // Redirect back with error message
        header("Location: customerEdit.php?CustomerID=$customerID&error=Database error");
        exit();
    }
} else {
    // Redirect if accessed directly
    header("Location: customerRecords.php");
    exit();
}
?>