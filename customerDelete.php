<?php
include_once 'customerFunctions.php';  
include 'ActivityTracker.php';
include 'loginChecker.php';

// Start with database connection
$conn = connect();

if (isset($_GET["CustomerID"])) {
    $id = $_GET["CustomerID"];
    $Aid = generate_ArchiveID();
    $Eid = $_SESSION["id"];
    
    // Archive the customer
    $sqlCustomer = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sqlCustomer);
    $stmt->bind_param("iii", $Aid, $id, $Eid);
    $stmt->execute();
    $stmt->close();
    
    // Get customer name for logs
    $query = "SELECT CustomerName FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['CustomerName'];
    $stmt->close();
    
    // Create log entry
    $Logsid = generate_LogsID();
    $stmt = $conn->prepare("INSERT INTO Logs 
                        (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                        VALUES
                        (?, ?, ?, 'customer', '5', ?, NOW())");
    $stmt->bind_param("ssss", $Logsid, $Eid, $id, $name);
    $stmt->execute();
    $stmt->close();
    
    // Close connection only after all database operations
    mysqli_close($conn);
    
    // Show success message and redirect after 1 second
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Customer Deleted</title>
        <meta http-equiv="refresh" content="1;url=customerRecords.php">
    </head>
    <body>
        <p>Customer successfully archived. Redirecting...</p>
    </body>
    </html>';
    exit;
}

// If no CustomerID provided, redirect immediately
header("Location: customerRecords.php");
exit;
?>