<?php
    include_once 'setup.php'; 
  
    //read all row from database table
    function employeeData($sort = 'EmployeeID', $order = 'ASC') 
    {
        $connection = connect();
        
        // Validate and normalize inputs
        $validColumns = ['EmployeeID', 'EmployeeName', 'EmployeeEmail', 'EmployeeNumber', 'RoleID', 'Status', 'BranchCode'];
        $sort = in_array($sort, $validColumns) ? $sort : 'EmployeeID';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    
        // Base query with all necessary joins
        $sql = "SELECT e.*, b.BranchName, 
                CASE 
                    WHEN e.RoleID = 1 THEN 'Admin' 
                    ELSE 'Staff' 
                END AS RoleDisplay,
                CASE 
                    WHEN e.RoleID = 1 THEN 1 
                    ELSE 2 
                END AS RoleOrder
                FROM employee e
                LEFT JOIN BranchMaster b ON e.BranchCode = b.BranchCode ";
    
        // Special sorting logic for each column
        switch($sort) {
            case 'EmployeeEmail': // Address column
                $sql .= "ORDER BY e.EmployeeEmail $order";
                break;
                
            case 'RoleID': // Role column
                $sql .= "ORDER BY RoleOrder $order";
                break;
                
            case 'BranchCode': // Branch column
                $sql .= "ORDER BY b.BranchName $order";
                break;
                
            default: // All other columns
                $sql .= "ORDER BY e.$sort $order";
        }
    
        $result = $connection->query($sql);
    
        if(!$result) {
            die("Invalid query: " . $connection->error);
        }
    
        // Rest of your display code remains the same...
        while ($row = $result->fetch_assoc()) {
            $role = ($row['RoleID'] == 1) ? "Admin" : "Staff";
            $branch = $row['BranchName'] ?? '';
    
            echo "<tr>
                    <td class='align-middle'>{$row['EmployeeID']}</td>
                    <td class='align-middle'>{$row['EmployeeName']}</td>
                    <td class='align-middle'>{$row['EmployeeEmail']}</td>
                    <td class='align-middle'>{$row['EmployeeNumber']}</td>
                    <td class='align-middle'>$role</td>
                    <td class='align-middle'>
                        <img src='{$row['EmployeePicture']}' alt='Employee Image' style='max-width: 50px; border-radius: 50%;'>
                    </td>                    
                    <td class='align-middle'>$branch</td>
                    <td class='align-middle'>
                        <a class='btn btn-primary btn-sm' href='employeeEdit.php?EmployeeID={$row['EmployeeID']}'>Edit</a>
                        <a class='btn btn-danger btn-sm' href='employeeDelete.php?EmployeeID={$row['EmployeeID']}'>Delete</a>
                    </td>
                  </tr>";
        }
    }

    // Rest of your functions remain the same...
    function branchHandler($branch) {
        $connection = connect();
    
        $sql = "SELECT * FROM BranchMaster";
        $result = $connection->query($sql);
    
        if (!$result) {
            die("Invalid query: " . $connection->error);
        }        
    
        // Read data of each row
        while ($row = $result->fetch_assoc()) {
            // Use double quotes for the option value and PHP echo
            echo "
                <option value='{$row['BranchCode']}' " . (($branch == $row['BranchCode']) ? 'selected' : '') . ">
                    {$row['BranchName']}
                </option>
            ";
        }            
    }

    function roleHandler($role) {
        $connection = connect();
    
        $sql = "SELECT * FROM roleMaster";
        $result = $connection->query($sql);
    
        if (!$result) {
            die("Invalid query: " . $connection->error);
        }        
    
        // Read data of each row
        while ($row = $result->fetch_assoc()) {
            // Use double quotes for the option value and PHP echo
            echo "
                <option value='{$row['RoleID']}' " . (($role == $row['RoleID']) ? 'selected' : '') . ">
                    {$row['Description']}
                </option>
            ";
        }            
    }
    function setStatus($id){
        $conn = connect(); 
        $sql = "UPDATE employee 
            SET Status = 'Inactive' WHERE EmployeeID = $id";
        $result = $conn->query($sql);
    }
    function handleEmployeeFormC() 
    {

        $errorMessage = "";
        $successMessage = "";

        

        $conn = connect();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){               

            $name = $_POST["name"];
            $username = $_POST["username"];
            $password = $_POST["password"];
            $email = $_POST["email"];
            $phone = $_POST["phone"];
            $role = $_POST["role"];
            $branch = $_POST["branch"];   
    
            // Validate inputs
            if (empty($name) || empty($username) || empty($password) || empty($email) || empty($phone) || empty($role) || empty($branch) ) {
                $errorMessage = $errorMessage .'Fill up all the fields.';                
            } else {
                if ($_FILES["IMAGE"]["size"] < 100000000) {                    
                    $imagePath = 'uploads/' . basename($_FILES['IMAGE']['name']);
                    if (($_FILES["IMAGE"]["name"]) != null)
                    {
                        move_uploaded_file($_FILES['IMAGE']['tmp_name'], $imagePath);
                    }   else {
                        $imagePath  = "Images/default.jpg";                        
                    }    
                    insertData($name ,$username ,$password ,$email, $phone, $role ,$branch ,$imagePath );
                    $successMessage = "Employee succesfully added"; 
                    header("Refresh: 2; url=employeeRecords.php");
                } else {
                    $errorMessage = $errorMessage .'Image File size is too big. <br>';   
                }                
            }
    
            // Return messages for further handling (e.g., displaying in the original page)
            return [$errorMessage, $successMessage];
            }                         
    }       

    function handleImage($id) 
        {
            $errorMessage = "";        
            $conn = connect();
            $sql = "SELECT EmployeePicture FROM employee where EmployeeID=$id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $imagePath = $row["EmployeePicture"]; 
            
            if ($_FILES["IMAGE"]["size"] < 100000000 && $_FILES["IMAGE"]["error"] == UPLOAD_ERR_OK) {                    
                if (!empty($_FILES["IMAGE"]["name"])) {
                    // Create uploads directory if it doesn't exist
                    $uploadDir = 'uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generate unique filename to prevent conflicts
                    $fileExtension = pathinfo($_FILES['IMAGE']['name'], PATHINFO_EXTENSION);
                    $newFilename = uniqid() . '.' . $fileExtension;
                    $imagePath = $uploadDir . $newFilename;
                    
                    if (!move_uploaded_file($_FILES['IMAGE']['tmp_name'], $imagePath)) {
                        $errorMessage = "Failed to upload image. Please check directory permissions.";
                        // Fall back to default image if upload fails
                        $imagePath = "Images/default.jpg";
                    }
                }   
            } else {
                if ($_FILES["IMAGE"]["error"] != UPLOAD_ERR_NO_FILE) {
                    $errorMessage = 'Image file size is too big or upload error occurred.';
                }
            }
            return [$errorMessage, $imagePath];
        }

    function insertData($name, $username, $password, $email, $phone, $role, $branch, $imagePath)
        {
            $conn = connect();
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);            
            
            
            $conn = connect(); 
            $id = generate_EmployeeID();  
            $employee_id = $_SESSION["id"];   
            $upd_by = $_SESSION["full_name"];         
            $sql = "INSERT INTO employee 
                    (EmployeeID,EmployeeName,EmployeePicture,EmployeeEmail,
                    EmployeeNumber,RoleID,LoginName,Password,BranchCode,
                    Status,Upd_by) 
                    VALUES
                    ('$id','$name','$imagePath','$email','$phone',
                    '$role','$username','$hashed_pw','2025160000','Active','$upd_by')";
            
            mysqli_query($conn, $sql);
            GenerateLogs($employee_id,$id,$name);
        }
        function GenerateLogs($employee_id,$id,$name)
            {
                $conn = connect(); 
                $Logsid = generate_LogsID();
                
                $stmt = $conn->prepare("INSERT INTO Logs 
                                    (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                                    VALUES
                                    (?, ?, ?, 'employee', '3', ?, NOW())");
                $stmt->bind_param("ssss", $Logsid, $employee_id, $id, $name);
                $stmt->execute();
                $stmt->close();
            }
    function handleCancellation() 
    {
        if (isset($_POST['confirm_cancel'])) {
            // Execute your cancellation logic here
            // For example, you might want to remove a record from the database
    
            // Redirect to another page
            header('Location: employeeRecords.php');
            exit();
        }
    }

    function getInventoryCount() {
        $conn = connect();
        $count = 0;
        $branchCode = $_SESSION['branchcode'];

        $query = "SELECT COUNT(*) as count 
                 FROM ProductBranchMaster 
                 WHERE BranchCode = '$branchCode'";

        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
        }
        mysqli_close($conn);
        return $count;
    }

    function getOrderCount() {
        $conn = connect();
        $count = 0;
        $branchCode = $_SESSION['branchcode']; 
        $query = "SELECT COUNT(*) as count FROM Order_hdr WHERE BranchCode = '$branchCode'";

        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
        }
        mysqli_close($conn);
        return $count;
    }
    
    function getLowInventoryProducts() {
        $userBranchCode = $_SESSION['branchcode'];
        if (!$userBranchCode) {
            return [];
        }

        $conn = connect();
        $lowInventory = [];
        $query = "SELECT pbm.ProductBranchID, pbm.ProductID, pbm.BranchCode, pbm.Stocks, pm.*
                FROM ProductBranchMaster pbm 
                JOIN productMstr pm ON pbm.ProductID = pm.ProductID
                WHERE pbm.Stocks <= 10 
                AND pbm.BranchCode = ?
                ORDER BY pbm.Stocks ASC";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $userBranchCode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $lowInventory[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $lowInventory;
    }

    function getCustomerCount() {
        $conn = connect();
        $count = 0;
        $query = "SELECT COUNT(*) as count FROM customer";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
        }
        mysqli_close($conn);
        return $count;
    }
?>