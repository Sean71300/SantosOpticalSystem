<?php
include_once 'customerFunctions.php';  

if (isset($_GET["EmployeeID"])) {
    $id = $_GET ["EmployeeID"];

    $conn=connect();

    $sql = "DELETE FROM employee WHERE EmployeeID=$id";
    $conn->query($sql);
}

header("location: employeeRecords.php");
exit;

?>