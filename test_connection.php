<?php
// Save this as: test_connection.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting test...<br>";

echo "Step 2: Attempting to include connect.php...<br>";
require_once 'connect.php';

echo "Step 3: connect.php included successfully<br>";

echo "Step 4: Calling connect() function...<br>";
$conn = connect();

echo "Step 5: Function returned<br>";

if ($conn) {
    echo "Step 6: Connection object exists<br>";
    echo "Connection type: " . get_class($conn) . "<br>";
    
    // Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "Step 7: Simple query successful<br>";
        $row = $result->fetch_assoc();
        echo "Query result: " . $row['test'] . "<br>";
    } else {
        echo "Step 7: Query failed - " . $conn->error . "<br>";
    }
} else {
    echo "Step 6: Connection is NULL or FALSE<br>";
}

echo "<br>Test completed!";
?>