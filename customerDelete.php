<?php
include_once 'customerFunctions.php';  

if (isset($_GET["CustomerID"])) {
    $id = $_GET ["CustomerID"];

    $conn=connect();

    $sql = "DELETE FROM customer WHERE customerId=$id";
    $conn->query($sql);
}

header("location: customerPage.php");
exit;

?>