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
    // Handle optional uploaded image
    $imageFileName = '';
    $uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'customer_images';
    if (!is_dir($uploadsDir)) { mkdir($uploadsDir, 0755, true); }
    if (isset($_FILES['CustomerImage']) && $_FILES['CustomerImage']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['CustomerImage']['tmp_name'];
        $origName = basename($_FILES['CustomerImage']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed, true) && filesize($tmpName) <= 2 * 1024 * 1024) {
            // get existing image to delete
            $oldImg = '';
            $r = $conn->prepare("SELECT CustomerImage FROM customer WHERE CustomerID = ? LIMIT 1");
            if ($r) { $r->bind_param('s', $customerID); $r->execute(); $res = $r->get_result(); $row = $res->fetch_assoc(); if ($row) $oldImg = $row['CustomerImage']; $r->close(); }

            $imageFileName = $customerID . '_' . time() . '.' . $ext;
            $dest = $uploadsDir . DIRECTORY_SEPARATOR . $imageFileName;
            if (move_uploaded_file($tmpName, $dest)) {
                // remove old image if exists
                if (!empty($oldImg)) {
                    $oldPath = $uploadsDir . DIRECTORY_SEPARATOR . $oldImg;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
            } else {
                $imageFileName = '';
            }
        }
    }

    if (!empty($imageFileName)) {
        $stmt = $conn->prepare("UPDATE customer SET CustomerName = ?, CustomerAddress = ?, CustomerContact = ?, CustomerInfo = ?, Notes = ?, Upd_by = ?, CustomerImage = ? WHERE CustomerID = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('ssssssss', $name, $address, $contact, $info, $notes, $upd_by, $imageFileName, $customerID);
    } else {
        $stmt = $conn->prepare("UPDATE customer SET CustomerName = ?, CustomerAddress = ?, CustomerContact = ?, CustomerInfo = ?, Notes = ?, Upd_by = ? WHERE CustomerID = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sssssss', $name, $address, $contact, $info, $notes, $upd_by, $customerID);
    }
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
