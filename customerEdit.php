<?php
include 'customerFunctions.php'; 
include 'ActivityTracker.php';
include 'loginChecker.php';

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$id = "";
$name = "";
$address = "";
$phone = "";
$info = "";
$notes = "";   

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["CustomerID"])) {
        header("location:customerRecords.php");
        exit;
    }

    $id = $_GET["CustomerID"];
    
    $conn = connect();
    $sql = "SELECT * FROM customer where CustomerID=$id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location:customerRecords.php");
        exit;
    }

    $name = $row["CustomerName"];
    $address = $row["CustomerAddress"];
    $phone = $row["CustomerContact"];
    $info = $row["CustomerInfo"];
    $notes = $row["Notes"];
} else {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $info = $_POST["info"];
    $notes = $_POST["notes"];

    do {
        if (empty($id) || empty($name) || empty($address) || empty($phone) || empty($info) || empty($notes)) {
            $errorMessage = 'All the fields are required';
            break;
        }
        $upd_by = $_SESSION["full_name"];
        $employee_id = $_SESSION["id"];
        $sql = "UPDATE customer 
            SET CustomerName = '$name', CustomerAddress = '$address', 
            CustomerContact = '$phone', CustomerInfo = '$info',
            Notes = '$notes', Upd_by = '$upd_by' 
            WHERE CustomerID = {$id}";

        $conn = connect();
        $result = $conn->query($sql);
        EGenerateLogs($employee_id,$id,$name);

        if (!$result) {
            $errorMessage = "Invalid query: " . $conn->error;
            break;
        }

        $successMessage = "Client updated successfully";
            
    } while(false);
}
function EGenerateLogs($employee_id,$id,$name)
    {
        $conn = connect(); 
        $Logsid = generate_LogsID();
        
        $stmt = $conn->prepare("INSERT INTO Logs 
                            (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                            VALUES
                            (?, ?, ?, 'customer', '4', ?, NOW())");
        $stmt->bind_param("ssss", $Logsid, $employee_id, $id, $name);
        $stmt->execute();
        $stmt->close();
    }

handleCancellation();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Customer | Santos Optical</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    
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
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-action {
            min-width: 120px;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-edit me-2"></i> Edit Customer</h1>
                <a class="btn btn-outline-secondary" href="customerRecords.php" role="button" data-bs-toggle="modal" 
                data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>            
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong><?php echo $errorMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong><?php echo $successMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <form method="post" id="customerEdit">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-lg" name="name" 
                               value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control form-control-lg" name="phone" 
                               value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control form-control-lg" name="address" 
                               value="<?php echo htmlspecialchars($address); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Customer Information</label>
                        <textarea class="form-control form-control-lg" name="info" rows="3" required><?php 
                            echo htmlspecialchars($info); 
                        ?></textarea>
                        <div class="form-text">Age, height, gender, etc.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control form-control-lg" name="notes" rows="3" required><?php 
                            echo htmlspecialchars($notes); 
                        ?></textarea>
                        <div class="form-text">Face shape, special requirements, etc.</div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 mt-5">                    
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="form-container">
            <hr>
        </div>

        <div class="form-container">
            <?php getMedicalRecords($id); ?>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to retrun to the records? Any unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="customerRecords.php" class="btn btn-danger">Yes, Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Medical Record Modal -->
    <div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addMedicalRecordModalLabel">Add New Medical Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="medicalRecordForm" method="post" action="medical-records-funcs.php">
                    <div class="modal-body">
                        <input type="hidden" name="customerID" id="modalCustomerID">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="visit_date" class="form-label">Visit Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="visit_date" name="visit_date" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="eye_condition" class="form-label">Eye Condition</label>
                                <input type="text" class="form-control" id="eye_condition" name="eye_condition">
                            </div>
                            <div class="col-md-6">
                                <label for="systemic_diseases" class="form-label">Systemic Diseases</label>
                                <input type="text" class="form-control" id="systemic_diseases" name="systemic_diseases" placeholder="e.g., diabetes, hypertension">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="visual_acuity_right" class="form-label">Visual Acuity (Right)</label>
                                <input type="text" class="form-control" id="visual_acuity_right" name="visual_acuity_right" placeholder="e.g., 20/20">
                            </div>
                            <div class="col-md-4">
                                <label for="visual_acuity_left" class="form-label">Visual Acuity (Left)</label>
                                <input type="text" class="form-control" id="visual_acuity_left" name="visual_acuity_left" placeholder="e.g., 20/20">
                            </div>
                            <div class="col-md-4">
                                <label for="pupillary_distance" class="form-label">Pupillary Distance (mm)</label>
                                <input type="number" class="form-control" id="pupillary_distance" name="pupillary_distance">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="intraocular_pressure_right" class="form-label">Intraocular Pressure - Right (mmHg)</label>
                                <input type="number" step="0.01" class="form-control" id="intraocular_pressure_right" name="intraocular_pressure_right">
                            </div>
                            <div class="col-md-6">
                                <label for="intraocular_pressure_left" class="form-label">Intraocular Pressure - Left (mmHg)</label>
                                <input type="number" step="0.01" class="form-control" id="intraocular_pressure_left" name="intraocular_pressure_left">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="refraction_right" class="form-label">Refraction (Right)</label>
                                <input type="text" class="form-control" id="refraction_right" name="refraction_right" placeholder="e.g., -1.50 DS">
                            </div>
                            <div class="col-md-6">
                                <label for="refraction_left" class="form-label">Refraction (Left)</label>
                                <input type="text" class="form-control" id="refraction_left" name="refraction_left" placeholder="e.g., -1.25 DS">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="current_medications" class="form-label">Current Medications</label>
                                <textarea class="form-control" id="current_medications" name="current_medications" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="allergies" class="form-label">Allergies</label>
                                <textarea class="form-control" id="allergies" name="allergies" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="family_eye_history" class="form-label">Family Eye History</label>
                                <textarea class="form-control" id="family_eye_history" name="family_eye_history" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="previous_eye_surgeries" class="form-label">Previous Eye Surgeries</label>
                                <textarea class="form-control" id="previous_eye_surgeries" name="previous_eye_surgeries" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="corneal_topography" class="form-label">Corneal Topography</label>
                                <textarea class="form-control" id="corneal_topography" name="corneal_topography" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="fundus_examination" class="form-label">Fundus Examination</label>
                                <textarea class="form-control" id="fundus_examination" name="fundus_examination" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="additional_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Medical Record Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if (isset($_GET['success'])) {
                        echo '<div class="alert alert-success mb-0">' . htmlspecialchars($_GET['success']) . '</div>';
                    } elseif (isset($_GET['error'])) {
                        echo '<div class="alert alert-danger mb-0">' . htmlspecialchars($_GET['error']) . '</div>';
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the medical record modal
            const addMedicalRecordModal = document.getElementById('addMedicalRecordModal');
            if (addMedicalRecordModal) {
                addMedicalRecordModal.addEventListener('show.bs.modal', function(event) {
                    // Button that triggered the modal
                    const button = event.relatedTarget;
                    // Extract info from data-bs-* attributes
                    const customerID = button.getAttribute('data-customer-id');
                    // Update the modal's content
                    const modalCustomerID = addMedicalRecordModal.querySelector('#modalCustomerID');
                    modalCustomerID.value = customerID;
                    
                    // Set today's date as default
                    const today = new Date().toISOString().split('T')[0];
                    addMedicalRecordModal.querySelector('#visit_date').value = today;
                });
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') || urlParams.has('error')) {
                const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
                statusModal.show();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>
</body>
</html>