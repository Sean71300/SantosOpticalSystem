<?php
include 'ActivityTracker.php';
include 'customerFunctions.php'; 
include 'loginChecker.php';
include_once 'setup.php';
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

    <div class="container mt-5">
    <i class="fa-solid fa-notes-medical me-2"></i><h2 class="mb-4">Customer Medical Records</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <?php
                        // Define columns in desired order
                        $columns = [
                            'history_id', 'CustomerID', 'CustomerName', 'visit_date',
                            'eye_condition', 'current_medications', 'allergies',
                            'family_eye_history', 'previous_eye_surgeries', 'systemic_diseases',
                            'visual_acuity_right', 'visual_acuity_left',
                            'intraocular_pressure_right', 'intraocular_pressure_left',
                            'refraction_right', 'refraction_left', 'pupillary_distance',
                            'corneal_topography', 'fundus_examination', 'additional_notes',
                            'created_at', 'updated_at'
                        ];
                        
                        foreach ($columns as $col) {
                            $header = ucwords(str_replace('_', ' ', $col));
                            echo "<th>$header</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = connect();
                    $query = "SELECT m.*, c.CustomerName 
                              FROM customerMedicalHistory m
                              JOIN customer c ON m.CustomerID = c.CustomerID
                              ORDER BY m.visit_date DESC";
                    
                    $result = mysqli_query($conn, $query);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            foreach ($columns as $col) {
                                $value = $row[$col] ?? '';
                                $display_value = empty(trim($value)) ? 'No record.' : htmlspecialchars($value);
                                echo "<td>$display_value</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='" . count($columns) . "' class='text-center'>No medical records found</td></tr>";
                    }
                    
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>