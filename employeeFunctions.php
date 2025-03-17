<?php
    include_once 'setup.php'; 
  
    //read all row from database table
    function employeeData()

        {
            $customerData = "";
            $connection = connect();

            $sql = "SELECT * FROM employee";
            $result = $connection->query($sql);

            if(!$result) {
                die ("Invalid query: " . $connection->error);
            }

            // read data of each row
            while ($row = $result->fetch_assoc()){
                $role="";

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
                    <td class='align-middle'>
                        <a class='btn btn-primary btn-sm' href='employeeEdit.php?EmployeeID=$row[EmployeeID]' >Edit</a>
                        <a class='btn btn-danger btn-sm' href='employeeDelete.php?EmployeeID=$row[EmployeeID]'>Delete</a>
                    </td>
                </tr>";
            }            
        }
    function handleCustomerForm() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST["name"];
            $address = $_POST["address"];
            $phone = $_POST["phone"];
            $info = $_POST["info"];
            $notes = $_POST["notes"];
    
            // Initialize messages
            $errorMessage = "";
            $successMessage = "";
    
            // Validate inputs
            if (empty($name) || empty($address) || empty($phone) || empty($info) || empty($notes)) {
                $errorMessage = 'All the fields are required';
            } else {
                // Call the function to insert data
                insertData($name, $address, $phone, $info, $notes);
                $successMessage = "Customer added successfully"; 
    
                // Clear the form fields after submission
                $name = "";
                $address = "";
                $phone = "";
                $info = "";
                $notes = "";
            }
    
            // Return messages for further handling (e.g., displaying in the original page)
            return [$errorMessage, $successMessage];
        }
    }
    function insertData($name,$address,$phone,$info,$notes)
        {
            $conn = connect(); 
            $id = generate_CustomerID();           
            $sql = "INSERT INTO customer 
                    (CustomerID,CustomerName,CustomerAddress,CustomerContact,
                    CustomerInfo,Notes,Upd_by) 
                    VALUES
                    ('$id','$name','$address','$phone','$info','$notes','Bien Ven P. Santos')";
            
            mysqli_query($conn, $sql);
        }
    
    
?>