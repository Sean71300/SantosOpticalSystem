<?php
header('Content-Type: application/json');
include 'setup.php';
if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $username = trim($_REQUEST['username'] ?? '');
    $excludeId = trim($_REQUEST['exclude_id'] ?? ''); // optional employee id to exclude (for edit)
    if ($username === '') throw new Exception('Username required');

    $conn = connect();
    if ($excludeId !== '') {
        $stmt = $conn->prepare('SELECT EmployeeID FROM employee WHERE LoginName = ? AND EmployeeID != ? LIMIT 1');
        $stmt->bind_param('ss', $username, $excludeId);
    } else {
        $stmt = $conn->prepare('SELECT EmployeeID FROM employee WHERE LoginName = ? LIMIT 1');
        $stmt->bind_param('s', $username);
    }
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = ($res && $res->num_rows > 0);
    $stmt->close();
    $conn->close();

    echo json_encode(['available' => $exists ? false : true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['available' => false, 'error' => $e->getMessage()]);
}
?>