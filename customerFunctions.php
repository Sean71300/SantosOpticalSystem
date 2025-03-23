<?php
    include_once 'setup.php'; 
  
    //read all row from database table
    function customerData()

        {
            $customerData = "";
            $connection = connect();

            $sql = "SELECT * FROM customer";
            $result = $connection->query($sql);

            if(!$result) {
                die ("Invalid query: " . $connection->error);
            }

            // read data of each row
            while ($row = $result->fetch_assoc()){
                echo
                "<tr>
                    <td>$row[CustomerID]</td>
                    <td>$row[CustomerName]</td>
                    <td>$row[CustomerAddress]</td>
                    <td>$row[CustomerContact]</td>
                    <td>
                        <a class='btn btn-primary btn-sm' href='customerEdit.php?CustomerID=$row[CustomerID]' >Edit</a>
                        <a class='btn btn-danger btn-sm' href='customerDelete.php?CustomerID=$row[CustomerID]'>Delete</a>
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
            $upd_by = $_SESSION["full_name"];
            $sql = "INSERT INTO customer 
                    (CustomerID,CustomerName,CustomerAddress,CustomerContact,
                    CustomerInfo,Notes,Upd_by) 
                    VALUES
                    ('$id','$name','$address','$phone','$info','$notes','$upd_by')";
            
            mysqli_query($conn, $sql);
        }
    
    function handleCancellation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
            // Execute your cancellation logic here
            // For example, you might want to remove a record from the database
    
            // Redirect to another page
            header('Location: customerRecords.php');
            exit();
        }
    }
    
?>