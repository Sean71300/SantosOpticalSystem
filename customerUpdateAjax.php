<?php
// AJAX endpoint to update customer profile and return JSON
include 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$customerID = isset($_POST['CustomerID']) ? trim($_POST['CustomerID']) : '';
$name = isset($_POST['CustomerName']) ? trim($_POST['CustomerName']) : '';
$address = isset($_POST['CustomerAddress']) ? trim($_POST['CustomerAddress']) : '';
$contact = isset($_POST['CustomerContact']) ? trim($_POST['CustomerContact']) : '';
$info = isset($_POST['CustomerInfo']) ? trim($_POST['CustomerInfo']) : '';
$notes = isset($_POST['Notes']) ? trim($_POST['Notes']) : '';

if ($customerID === '' || $name === '' || $address === '' || $contact === '') {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit();
}

$conn = connect();
// Use prepared statement to avoid injection
$upd_by = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
try {
    $stmt = $conn->prepare("UPDATE customer SET CustomerName = ?, CustomerAddress = ?, CustomerContact = ?, CustomerInfo = ?, Notes = ?, Upd_by = ? WHERE CustomerID = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('sssssss', $name, $address, $contact, $info, $notes, $upd_by, $customerID);
    $ok = $stmt->execute();
    if (!$ok) throw new Exception('Execute failed: ' . $stmt->error);
    $stmt->close();

    // Logging similar to existing code
    if (isset($_SESSION['id'])) {
        $employee_id = $_SESSION['id'];
        // create log id and insert
        $Logsid = generate_LogsID();
        $stmt2 = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, 'customer', '4', ?, NOW())");
        if ($stmt2) {
            $stmt2->bind_param('ssss', $Logsid, $employee_id, $customerID, $name);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
