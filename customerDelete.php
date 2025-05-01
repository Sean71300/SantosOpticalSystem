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
        $Logsid = generate_LogsID();
        $Eid = $_SESSION["id"];
        $id = $_GET ["CustomerID"];
        $name = $_GET ["CustomerName"];
        $stmt = $conn->prepare("INSERT INTO Logs 
                            (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                            VALUES
                            (?, ?, ?, 'customer', '5', ?, NOW())");
        $stmt->bind_param("ssss", $Logsid, $Eid, $id, $name);
        $stmt->execute();
        $stmt->close();

header(" url=customerRecords.php");
exit;

?>