<?php
include 'setup.php';
include 'employeeFunctions.php'; 
include 'ActivityTracker.php';
include 'loginChecker.php';


$id = "";
$name = "";
$username = "";
$email = "";
$phone = "";
$role = "";
$branch = "";
$imagePath = "";  

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["EmployeeID"])) {
        header("location:employeeRecords.php");
        exit;
    }

    $id = $_GET["EmployeeID"];
    $conn = connect();
    $sql = "SELECT * FROM employee where EmployeeID=$id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location:employeeRecords.php");
        exit;
    }            
    
    $name = $row["EmployeeName"];
    $username = $row["LoginName"];
    $email = $row["EmployeeEmail"];
    $phone = $row["EmployeeNumber"];
    $role = $row["RoleID"];
    $branch = $row["BranchCode"]; 
    $imagePath = $row["EmployeePicture"]; 
} else {
    // Handle form submission
    $id = $_POST["id"];
    $name = $_POST["name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $role = $_POST["role"];
    $branch = $_POST["branch"];

    do {
        // Check for empty fields
        if (empty($id) || empty($name) || empty($username) || empty($email) || empty($phone) || empty($role) || empty($branch)) {
            $errorMessage = 'All the fields are required';
            break;
        }

        // Handle image upload
        [$errorMessage, $imagePath] = handleImage($id);

        // Update employee record
        $upd_by = $_SESSION["full_name"];        
        $employee_id = $_SESSION["id"];
        $sql = "UPDATE employee 
                SET EmployeeName = '$name', EmployeePicture = '$imagePath', 
                EmployeeEmail = '$email', EmployeeNumber = '$phone',
                RoleID = '$role', LoginName = '$username', Upd_by = '$upd_by', BranchCode = '$branch' 
                WHERE EmployeeID = {$id}";

        $conn = connect();
        $result = $conn->query($sql);

        if (!$result) {
            $errorMessage = "Invalid query: " . $conn->error;
            break;
        }                        
        $successMessage = "Employee successfully updated";    
        EGenerateLogs($employee_id,$id,$name);    
        header('Refresh: 2, url=employeeRecords.php');   

    } while (false);    
}
function EGenerateLogs($employee_id, $id, $name) {
    $conn = connect(); 
    $Logsid = generate_LogsID();
    
    $stmt = $conn->prepare("INSERT INTO Logs 
                          (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                          VALUES
                          (?, ?, ?, 'employee', '4', ?, NOW())");
    $stmt->bind_param("ssss", $Logsid, $employee_id, $id, $name);
    $stmt->execute();
    $stmt->close();
}
handleCancellation();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Employee | Santos Optical</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
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
        .employee-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-edit me-2"></i> Edit Employee</h1>
                <a class="btn btn-outline-secondary" href="employeeRecords.php" role="button" data-bs-toggle="modal" 
                data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2 " ></i> Back to List
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
            
            <form method="post" id="employeeEdit" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-lg" name="name" 
                               value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control form-control-lg" name="username" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control form-control-lg" name="phone" 
                               value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select class="form-select form-control-lg" name="branch" required>
                            <?php branchHandler($branch); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select class="form-select form-control-lg" name="role" required>
                            <?php roleHandler($role); ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Employee Image" class="employee-img">
                        <div>
                            <label for="IMAGE" class="btn btn-success">
                                <input type="file" name="IMAGE" id="IMAGE" accept=".jpg, .png, .jpeg" onchange="profilePicture(this)" style="display:none;">
                                <i class="fas fa-camera me-2"></i> Change Picture
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 mt-5">                    
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
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
                    Are you sure you want to return to employee records? Any unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="employeeRecords.php" class="btn btn-danger">Yes, Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function profilePicture(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector(".employee-img").src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>