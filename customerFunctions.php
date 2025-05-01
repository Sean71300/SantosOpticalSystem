<?php
    include_once 'setup.php'; 
  
    $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;

    //read all row from database table
    function customerData($sort = 'CustomerID', $order = 'ASC')
    {
        $customerData = "";
        $connection = connect();

        // Validate sort column to prevent SQL injection
        $validColumns = ['CustomerID', 'CustomerName', 'CustomerAddress', 'CustomerContact'];
        $sort = in_array($sort, $validColumns) ? $sort : 'CustomerID';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * 
        FROM customer c
        WHERE NOT EXISTS (
            SELECT 1 
            FROM archives a 
            WHERE a.TargetID = c.CustomerID AND a.TargetType = 'customer'
        )
        ORDER BY $sort $order";
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
                <td>";
                    
                    
                    {
                        echo 
                        "
                            <a class='btn btn-primary btn-sm' href='customerEdit.php?CustomerID={$row['CustomerID']}'>Edit</a>
                            <a class='btn btn-danger btn-sm' href='customerDelete.php?CustomerID={$row['CustomerID']}'>Delete</a>
                        ";
                    }
                    echo "
                    <button class='btn btn-info btn-sm view-orders' data-customer-id='$row[CustomerID]'>View Orders</button>
                </td>
            </tr>";
        }            
    }

    // New function to get ordered products by customer
    function getCustomerOrders($customerID) {
        $connection = connect();
        $orders = array();

        $sql = "SELECT p.Model, b.BrandName, od.Quantity, oh.Created_dt 
                FROM orderDetails od
                JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
                JOIN ProductBranchMaster pbm ON od.ProductBranchID = pbm.ProductBranchID
                JOIN productMstr p ON pbm.ProductID = p.ProductID
                JOIN brandMaster b ON p.BrandID = b.BrandID
                WHERE oh.CustomerID = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $customerID);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        $connection->close();
        
        return $orders;
    }

    // Handle AJAX request for customer orders
    if (isset($_GET['action'])) {
        header('Content-Type: application/json');
        if ($_GET['action'] === 'getCustomerOrders' && isset($_GET['customerID'])) {
            $customerID = $_GET['customerID'];
            $orders = getCustomerOrders($customerID);
            echo json_encode($orders);
            exit();
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
        $employee_id = $_SESSION["id"];
        $sql = "INSERT INTO customer 
                (CustomerID,CustomerName,CustomerAddress,CustomerContact,
                CustomerInfo,Notes,Upd_by) 
                VALUES
                ('$id','$name','$address','$phone','$info','$notes','$upd_by')";
        
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
                            (?, ?, ?, 'customer', '3', ?, NOW())");
        $stmt->bind_param("ssss", $Logsid, $employee_id, $id, $name);
        $stmt->execute();
        $stmt->close();
    }
    
    function handleCancellation() {
        if (isset($_POST['confirm_cancel'])) {
            // Execute your cancellation logic here
            // For example, you might want to remove a record from the database
    
            // Redirect to another page
            header('Location: customerRecords.php');
            exit();
        }
    }
    
?>