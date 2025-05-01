<?php
include_once 'customerFunctions.php';  
include 'ActivityTracker.php';
include 'loginChecker.php';

if (isset($_GET["CustomerID"])) {
    $id = $_GET ["CustomerID"];
    $Aid = generate_ArchiveID();
    $Eid = $_SESSION["id"];
    $sqlCustomer = "INSERT INTO archives (ArchiveID, TargetID,EmployeeID, TargetType) VALUES (?, ?, ?,'customer')";
    $stmt = $conn->prepare($sqlCustomer);
    $stmt->bind_param("iii",$Aid, $id, $Eid);
    $stmt->execute();
    $stmt->close();
}

$conn = connect(); 
        $id = $_GET ["CustomerID"];
        $query = "SELECT * FROM customer WHERE CustomerID = $id";
        $Logsid = generate_LogsID();
        $Eid = $_SESSION["id"];
        $result = mysqli_query($conn, $query);        
        $row = mysqli_fetch_assoc($result);
        $name = $row['CustomerName'];
        
        mysqli_close($conn);
        $stmt = $conn->prepare("INSERT INTO Logs 
                            (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                            VALUES
                            (?, ?, ?, 'customer', '5', ?, NOW())");
        $stmt->bind_param("ssss", $Logsid, $Eid, $id, $name);
        $stmt->execute();
        $stmt->close();

exit;

?>