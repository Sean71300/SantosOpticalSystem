<?php
include 'setup.php';
include 'ActivityTracker.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$customerID = isset($_POST['CustomerID']) ? $_POST['CustomerID'] : null;
if (!$customerID) {
    echo json_encode(['success' => false, 'message' => 'Missing CustomerID']);
    exit();
}

$conn = connect();

// Soft-delete: set Status = 'Inactive'
$stmt = $conn->prepare("UPDATE customer SET Status = 'Inactive' WHERE CustomerID = ?");
$stmt->bind_param('s', $customerID);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    // Log the action in Logs
    $Logsid = generate_LogsID();
    $employee_id = isset($_SESSION['id']) ? $_SESSION['id'] : '0';
    $stmt2 = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, 'customer', '5', ?, NOW())");
    $desc = 'Removed customer ' . $customerID;
    $stmt2->bind_param('ssss', $Logsid, $employee_id, $customerID, $desc);
    $stmt2->execute();
    $stmt2->close();

    // Optionally insert into archives table if available
    if (function_exists('archiveTarget')) {
        try { archiveTarget('customer', $customerID); } catch (Exception $e) { /* ignore */ }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove customer']);
}

$conn->close();

?>