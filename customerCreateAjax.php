<?php
include 'setup.php';
include 'ActivityTracker.php';
include 'customerFunctions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$info = isset($_POST['info']) ? trim($_POST['info']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($name === '' || $address === '' || $phone === '') {
    echo json_encode(['success' => false, 'message' => 'Name, address and contact are required']);
    exit();
}

// Insert
insertData($name, $address, $phone, $info, $notes);

// Return success (we can't easily return the new ID without modification of insertData)
echo json_encode(['success' => true]);

?>