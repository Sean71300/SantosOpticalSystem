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
                
                $image = base64_encode($row["EmployeePicture"]);               
                
                echo
                "<tr>
                    <td class='align-middle'>$row[EmployeeID]</td>
                    <td class='align-middle'>$row[EmployeeName]</td>
                    <td class='align-middle'>$row[EmployeeEmail]</td>
                    <td class='align-middle'>$row[EmployeeNumber]</td>
                    <td class='align-middle'>$role</td>
                    <td>";
                    echo  '<img src="data:image/jpeg;base64,' . $image . '"class="icons">';
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
        
    
    function handleEmployeeForm() {

        $errorMessage = "";
        $successMessage = "";

        $conn = connect();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){   
            if ($_FILES["IMAGE"]["size"] < 5000000) {
                $filename = $_FILES["IMAGE"]["name"];
                $tempname = $_FILES["IMAGE"]["tmp_name"];
                $folder = "Uploads/" . $filename;    
                if ($filename != null && $tempname != null)
                {
                    $img_clean = file_get_contents($tempname);
                    $image = mysqli_real_escape_string($conn, $img_clean);
                    move_uploaded_file($tempname, $folder);
                }   else {
                    $img_path = "Images/default.jpg";
                    $img_clean= file_get_contents($img_path);
                    $image = mysqli_real_escape_string($conn, $img_clean);   
                }    
                $name = $_POST["name"];
                $username = $_POST["username"];
                $password = $_POST["password"];
                $email = $_POST["email"];
                $phone = $_POST["phone"];
                $role = $_POST["role"];
                $branch = $_POST["branch"];     
            } else {
                $errorMessage = $errorMessage .'Image File size is too big. \n';   
            }

            // Initialize messages
            
    
            // Validate inputs
            if (empty($name) || empty($username) || empty($password) || empty($email) || empty($phone) || empty($role) || empty($branch) ) {
                $errorMessage = $errorMessage .'Fill up all the fields';                
            } else {
                // Call the function to insert data
                insertData($name ,$username ,$password ,$email, $phone, $role ,$branch ,$image );
                $successMessage = "Customer added successfully"; 
    
                // Clear the form fields after submission
                $name = "";
                $username = "";
                $password = "";
                $email = "";
                $phone = "";
                $role = "";
                $branch = ""; 
                $image = "";  
            }
    
            // Return messages for further handling (e.g., displaying in the original page)
            return [$errorMessage, $successMessage];
            }                
          
    }
    
    function insertData($name, $username, $password, $email, $phone, $role, $branch, $image)
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
                    ('$id','$name','$image','$email','$phone',
                    '$roleID','$username','$hashed_pw','2025160000','Active','$upd_by')";
            
            mysqli_query($conn, $sql);
        }
    function handleCancellation() {
        if (isset($_POST['confirm_cancel'])) {
            // Execute your cancellation logic here
            // For example, you might want to remove a record from the database
    
            // Redirect to another page
            header('Location: employeeRecords.php');
            exit();
        }
    }    
?>