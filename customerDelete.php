<?php
include_once 'customerFunctions.php';  
include 'ActivityTracker.php';
include 'loginChecker.php';

if (isset($_GET["CustomerID"])) {
    $id = $_GET ["CustomerID"];
    $Aid = generate_ArchiveID();
    $sqlCustomer = "INSERT INTO archives (ArchiveID, TargetID, TargetType, Reason) VALUES (?, ?, 'customer', ?)";
    $stmt = $conn->prepare($sqlCustomer);
    $reasonCustomer = "Inactive customer for over 1 year";
    $stmt->bind_param("iis",$Aid, $customerID, $reasonCustomer);
    $stmt->execute();
    $stmt->close();
}

header("location: customerRecords.php");
exit;

?>