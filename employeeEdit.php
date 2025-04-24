<?php
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

    
    if ( $_SERVER['REQUEST_METHOD'] == 'GET') {
        
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
            header ("location:employeeRecords.php");
            exit;
        }            
        
        $name = $row["EmployeeName"];
        $username = $row["LoginName"];
        $email = $row["EmployeeEmail"];
        $phone = $row["EmployeeNumber"];
        $role = $row["RoleID"];
        $branch = $row["BranchCode"]; 
        $imagePath =  $row["EmployeePicture"]; 
    }
    
    else {
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
                break; // Stop processing if there are errors
            }
    
            // Handle image upload
            [$errorMessage, $imagePath] = handleImage($id);
    
            // Determine role ID
            $roleID = ($role == "Admin") ? 1 : 2;
    
            // Update employee record
            $upd_by = $_SESSION["full_name"];
            $sql = "UPDATE employee 
                    SET EmployeeName = '$name', EmployeePicture = '$imagePath', 
                    EmployeeEmail = '$email', EmployeeNumber = '$phone',
                    RoleID = '$roleID', LoginName = '$username', Upd_by = '$upd_by', BranchCode = '$branch' 
                    WHERE EmployeeID = {$id}";
    
            $conn = connect();
            $result = $conn->query($sql);
    
            if (!$result) {
                $errorMessage = "Invalid query: " . $conn->error;
                break; // Stop processing if the query fails
            }                        
            $successMessage = "Employee succesfully updated";            
            header('Refresh: 2, url=employeeRecords.php');   

        } while (false);    
    }

    handleCancellation();

?>

<html>
    <title>
        Employee Page
    </title>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
    </head>
    <body class="bg-body-tertiary">
        <?php include "Navigation.php"?> 
        <div class="container category-container">
        <h1>New Employee</h1>

        <?php
         if (!empty($errorMessage)) {
            echo "
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$errorMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>
        
        <form id="employeeCreate" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value ="<?php echo $id;?>">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?php echo $name;?>" >
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Username</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="username" value="<?php echo $username;?>" >
                </div>
            </div>            
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="email" value="<?php echo $email;?>" >
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Contact Number</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="phone" value="<?php echo $phone;?>" >
                </div>
            </div>                      
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Branch</label>
                <div class="col-sm-6">
                    <select class="form-control" name="branch" required>                        
                        <?php branchHandler($branch);?>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Role</label>
                <div class="col-sm-6">
                    <select class="form-control" name="role" required>                                                
                        <?php roleHandler($role);?>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <img src="<?php echo $imagePath?>" alt="Image" style="max-width: 200px; margin: 10px;" alt="picture" id="picture" class="picture">
                <label for="IMAGE" class="btn btn-success add-picture-button mt-3">
                    <input class='form-control' type="file" name="IMAGE" id="IMAGE" accept=".jpg, .png, .jpeg" onchange="profilePicture(this)" style="display:none;" value="<?php echo htmlspecialchars($imagePath); ?>">Add Picture
                </label>    
            </div>
            <?php
            if (!empty($successMessage)) {
                echo "
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>$successMessage</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
                ";
            }
            ?>
            <div class="row mb-3">
                <div class="offset-mb-3 col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary" name="submit">Submit</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <!-- Button to trigger modal -->
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cancelModal">Return</button>                    
                </div>
            </div>
        </form>
    </div>

        <!-- Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to Return? You will lose any unsaved changes.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form  method="post" style="display: inline;">
                    <input type="hidden" name="confirm_cancel" value="1">
                    <button type="return" class="btn btn-primary">Yes, Return</button>
                </form>
            </div>
        </div>
    </div>
    </div>
    <script>
         function profilePicture(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("picture").src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                document.getElementById("picture").src = "#";
            }
        }
    </script>
    </body>
</html>