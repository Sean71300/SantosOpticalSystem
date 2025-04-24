<?php
    include_once 'setup.php'; 
  
    //read all row from database table
    function employeeData()
        {            
            $connection = connect();
            $sql = "SELECT * FROM employee";
            $result = $connection->query($sql);

            if(!$result) {
                die ("Invalid query: " . $connection->error);
            }

            // read data of each row
            while ($row = $result->fetch_assoc()){
                $role="";
                $branch="";

                $connection = connect();
                $sql2 = "SELECT BranchName FROM branchmaster WHERE BRANCHCODE = $row[BranchCode]";
                $result2 = $connection->query($sql2);
                if ($result2->num_rows > 0) {
                    // Fetch the result as an associative array
                    $branchData = $result2->fetch_assoc();
                    $Branch = $branchData['BranchName']; // Convert to string
                } else {
                    $Branch = ""; // Handle the case where no results are found
                }

                if ($row['RoleID'] == 1){
                    $role = "Admin";
                }
                else {
                    $role = "Staff";
                }
                echo
                "<tr>
                    <td class='align-middle'>$row[EmployeeID]</td>
                    <td class='align-middle'>$row[EmployeeName]</td>
                    <td class='align-middle'>$row[EmployeeEmail]</td>
                    <td class='align-middle'>$row[EmployeeNumber]</td>
                    <td class='align-middle'>$role</td>
                    <td>";
                    echo '<img src="' . $row['EmployeePicture'] . '" alt="Image" style="max-width: 200px; margin: 10px;">';
                    echo "</td>
                    <td class='align-middle'>$row[Status]</td>
                    <td class='align-middle'>$Branch</td>
                    <td class='align-middle'>
                        <a class='btn btn-primary btn-sm' href='employeeEdit.php?EmployeeID=$row[EmployeeID]' >Edit</a>
                        <a class='btn btn-danger btn-sm' href='employeeDelete.php?EmployeeID=$row[EmployeeID]'>Delete</a>
                    </td>
                </tr>";
            }            
        }
        function branchHandler($branch) {
            $customerData = "";
            $connection = connect();
        
            $sql = "SELECT * FROM branchmaster";
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
            $customerData = "";
            $connection = connect();
        
            $sql = "SELECT * FROM rolemaster";
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

    function handleImage ($id){
        $errorMessage = "";        
        $conn = connect();
        $sql = "SELECT EmployeePicture FROM employee where EmployeeID=$id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $imagePath =  $row["EmployeePicture"]; 
        
        if ($_FILES["IMAGE"]["size"] < 100000000) {                    
            
            if (($_FILES["IMAGE"]["name"]) != null)
            {
                $imagePath = 'uploads/' . basename($_FILES['IMAGE']['name']);
                move_uploaded_file($_FILES['IMAGE']['tmp_name'], $imagePath);
            }   
        } else {
            $errorMessage = $errorMessage .'Image File size is too big. <br>';   
        }
        return [$errorMessage, $imagePath];
    }

    function insertData($name, $username, $password, $email, $phone, $role, $branch, $imagePath)
        {
            $conn = connect();
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

            
            if ($role == "Admin"){
                $roleID = 1;
            }
            else if ($role == "Employee" ){
                $roleID = 2;
            }
            
            $conn = connect(); 
            $id = generate_EmployeeID();  
            $upd_by = $_SESSION["full_name"];         
            $sql = "INSERT INTO employee 
                    (EmployeeID,EmployeeName,EmployeePicture,EmployeeEmail,
                    EmployeeNumber,RoleID,LoginName,Password,BranchCode,
                    Status,Upd_by) 
                    VALUES
                    ('$id','$name','$imagePath','$email','$phone',
                    '$roleID','$username','$hashed_pw','2025160000','Active','$upd_by')";
            
            mysqli_query($conn, $sql);
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
?>