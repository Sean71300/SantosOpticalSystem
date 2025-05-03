<?php
include 'ActivityTracker.php';
include 'customerFunctions.php'; 
include 'loginChecker.php';
include_once 'setup.php';

function getMedicalRecords($customerID) {
    $connection = connect();

    $sql = "SELECT * FROM customerMedicalHistory WHERE CustomerID = ? ORDER BY visit_date DESC";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="form-container">';
        echo '<div class="d-flex justify-content-between align-items-center mb-4">';
        echo '<h3><i class="fas fa-calendar-check me-2"></i> Medical History Records</h3>';
        echo '<button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addMedicalRecordModal" data-customer-id="'.$customerID.'">';
        echo '<i class="fas fa-plus me-2"></i> Add Record</button>';
        echo '</div>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<div class="medical-record-card mb-4 p-4 border rounded">';
            echo '<h5 class="mb-4"><i class="fas fa-calendar-day me-2"></i> '.htmlspecialchars($row['visit_date']).'</h5>';
            
            // Basic Information
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Eye Condition</label>';
            echo '<p class="form-control-static">'.(!empty($row['eye_condition']) ? htmlspecialchars($row['eye_condition']) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Systemic Diseases</label>';
            echo '<p class="form-control-static">'.(!empty($row['systemic_diseases']) ? htmlspecialchars($row['systemic_diseases']) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Visual Acuity
            echo '<div class="row mb-3">';
            echo '<div class="col-md-4">';
            echo '<label class="form-label">Visual Acuity (Right)</label>';
            echo '<p class="form-control-static">'.(!empty($row['visual_acuity_right']) ? htmlspecialchars($row['visual_acuity_right']) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-4">';
            echo '<label class="form-label">Visual Acuity (Left)</label>';
            echo '<p class="form-control-static">'.(!empty($row['visual_acuity_left']) ? htmlspecialchars($row['visual_acuity_left']) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-4">';
            echo '<label class="form-label">Pupillary Distance (mm)</label>';
            echo '<p class="form-control-static">'.(!empty($row['pupillary_distance']) ? htmlspecialchars($row['pupillary_distance']) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Intraocular Pressure
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Intraocular Pressure - Right (mmHg)</label>';
            echo '<p class="form-control-static">'.(!empty($row['intraocular_pressure_right']) ? htmlspecialchars($row['intraocular_pressure_right']) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Intraocular Pressure - Left (mmHg)</label>';
            echo '<p class="form-control-static">'.(!empty($row['intraocular_pressure_left']) ? htmlspecialchars($row['intraocular_pressure_left']) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Refraction
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Refraction (Right)</label>';
            echo '<p class="form-control-static">'.(!empty($row['refraction_right']) ? htmlspecialchars($row['refraction_right']) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Refraction (Left)</label>';
            echo '<p class="form-control-static">'.(!empty($row['refraction_left']) ? htmlspecialchars($row['refraction_left']) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Medications and Allergies
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Current Medications</label>';
            echo '<p class="form-control-static">'.(!empty($row['current_medications']) ? nl2br(htmlspecialchars($row['current_medications'])) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Allergies</label>';
            echo '<p class="form-control-static">'.(!empty($row['allergies']) ? nl2br(htmlspecialchars($row['allergies'])) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Family History and Previous Surgeries
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Family Eye History</label>';
            echo '<p class="form-control-static">'.(!empty($row['family_eye_history']) ? nl2br(htmlspecialchars($row['family_eye_history'])) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Previous Eye Surgeries</label>';
            echo '<p class="form-control-static">'.(!empty($row['previous_eye_surgeries']) ? nl2br(htmlspecialchars($row['previous_eye_surgeries'])) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Examinations
            echo '<div class="row mb-3">';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Corneal Topography</label>';
            echo '<p class="form-control-static">'.(!empty($row['corneal_topography']) ? nl2br(htmlspecialchars($row['corneal_topography'])) : 'No record.').'</p>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<label class="form-label">Fundus Examination</label>';
            echo '<p class="form-control-static">'.(!empty($row['fundus_examination']) ? nl2br(htmlspecialchars($row['fundus_examination'])) : 'No record.').'</p>';
            echo '</div>';
            echo '</div>';
            
            // Additional Notes
            if (!empty($row['additional_notes'])) {
                echo '<div class="row mb-3">';
                echo '<div class="col-12">';
                echo '<label class="form-label">Additional Notes</label>';
                echo '<p class="form-control-static">'.nl2br(htmlspecialchars($row['additional_notes'])).'</p>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>'; // Close medical-record-card
        }
        echo '</div>'; // Close form-container
    } else {
        echo '<div class="d-flex justify-content-between align-items-center mb-4">';
        echo '<h3><i class="fas fa-calendar-check me-2"></i> Medical History Records</h3>';
        echo '<button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addMedicalRecordModal" data-customer-id="'.$customerID.'">';
        echo '<i class="fas fa-plus me-2"></i> Add Record</button>';
        echo '</div>';
        echo '<div class="alert alert-info">No medical records found for this customer.</div>';
    }
    $stmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Medical History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <style>
        body {
            background-color: #f5f7fa;
            display: flex;
        }
        .sidebar {
            background-color: white;
            height: 100vh;
            padding: 20px 0;
            color: #2c3e50;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .sidebar-item {
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 0;
            display: flex;
            align-items: center;
            color: #2c3e50;
            transition: all 0.3s;
            text-decoration: none;
        }
        .sidebar-item:hover {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        .sidebar-item.active {
            background-color: #e9ecef;
            color: #2c3e50;
            font-weight: 500;
        }   
        .sidebar-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        /* Sorting styles */
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 25px;
        }
        .sortable:hover {
            background-color: #f8f9fa;
        }
        .sort-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }
        .sortable.active .sort-icon {
            display: inline-block;                
        }     
        .table th { white-space: nowrap; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>
    <?php include "sidebar.php"?>

    <div class="form-container">
        <?php getMedicalRecords($id); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>