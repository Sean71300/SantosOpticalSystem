<?php
include_once 'setup.php';
include_once 'customerFunctions.php';

header('Content-Type: application/json');

if (isset($_GET['customerID'])) {
    $customerID = $_GET['customerID'];
    $orders = getCustomerOrders($customerID);
    echo json_encode($orders);
} else {
    echo json_encode([]);
}
?>