<?php
header('Content-Type: application/json');
include_once 'employeeFunctions.php';
include 'ActivityTracker.php';
if (session_status() == PHP_SESSION_NONE) session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $id = $_POST['EmployeeID'] ?? '';
    if (empty($id)) throw new Exception('Missing EmployeeID');

    $conn = connect();
    $Aid = generate_ArchiveID();
    $Eid = $_SESSION['id'] ?? '';

    setStatus($id);

    $sqlEmployee = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType) VALUES (?, ?, ?, 'employee')";
    $stmt = $conn->prepare($sqlEmployee);
    if (!$stmt) throw new Exception('Prepare failed: '.$conn->error);
    $stmt->bind_param('sss', $Aid, $id, $Eid);
    $stmt->execute();
    $stmt->close();

    $query = "SELECT EmployeeName FROM employee WHERE EmployeeID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['EmployeeName'] ?? '';
    $stmt->close();

    $Logsid = generate_LogsID();
    $stmt = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt) VALUES (?, ?, ?, 'employee', '5', ?, NOW())");
    $stmt->bind_param('ssss', $Logsid, $Eid, $id, $name);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}

?>
