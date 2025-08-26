<?php
// Debug helper: quick DB connectivity test and log writer for salesData troubleshooting
header('Content-Type: application/json; charset=utf-8');
$host = 'localhost';
$user = 'u809407821_santosopticals';
$pass = '8Bt?Q0]=w';
$db   = 'u809407821_santosopticals';

// try connect
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    $msg = 'DB connect failed: ' . $conn->connect_error;
    @file_put_contents(__DIR__ . '/logs/salesData_errors.log', date('c') . " - " . $msg . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// quick query to validate tables
$res = $conn->query("SELECT 1 FROM Order_hdr LIMIT 1");
if ($res === false) {
    $msg = 'Test query failed: ' . $conn->error;
    @file_put_contents(__DIR__ . '/logs/salesData_errors.log', date('c') . " - " . $msg . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'DB connection OK, test query passed.']);

?>
