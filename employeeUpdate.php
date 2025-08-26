<?php
// Ensure we always return JSON and capture unexpected output/errors
ob_start();
header('Content-Type: application/json');

set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    $payload = ['success' => false, 'message' => "PHP Error: $message in $file on line $line"];
    echo json_encode($payload);
    exit;
});
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unhandled exception: '.$e->getMessage()]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Shutdown error: '.($err['message'] ?? '')]);
        exit;
    }
});

include 'setup.php';
include 'employeeFunctions.php';
// Do not include loginChecker.php here because it may redirect with HTML; instead validate session minimally
if (session_status() == PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Basic validation
$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$role = $_POST['role'] ?? '';
$branch = $_POST['branch'] ?? '';

if (empty($id) || empty($name) || empty($username) || empty($email) || empty($phone) || empty($role) || empty($branch)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Handle image upload
list($err, $imagePath) = handleImage($id);
if ($err) {
    echo json_encode(['success' => false, 'message' => $err]);
    exit;
}

$conn = connect();
$upd_by = $_SESSION['full_name'] ?? '';
$employee_id = $_SESSION['id'] ?? '';

$stmt = $conn->prepare("UPDATE employee SET EmployeeName = ?, EmployeePicture = ?, EmployeeEmail = ?, EmployeeNumber = ?, RoleID = ?, LoginName = ?, Upd_by = ?, BranchCode = ? WHERE EmployeeID = ?");
$stmt->bind_param('sssssssss', $name, $imagePath, $email, $phone, $role, $username, $upd_by, $branch, $id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Update failed: '.$stmt->error]);
    exit;
}
$stmt->close();

// Log
EGenerateLogs($employee_id, $id, $name);

// Return updated values to update table row on client
// Fetch display names for role and branch
$role_name = '';
$branch_name = '';
$rstmt = $conn->prepare("SELECT Description FROM roleMaster WHERE RoleID = ?");
if ($rstmt) {
    $rstmt->bind_param('s', $role);
    $rstmt->execute();
    $rstmt->bind_result($role_name);
    $rstmt->fetch();
    $rstmt->close();
}

$bstmt = $conn->prepare("SELECT BranchName FROM BranchMaster WHERE BranchCode = ?");
if ($bstmt) {
    $bstmt->bind_param('s', $branch);
    $bstmt->execute();
    $bstmt->bind_result($branch_name);
    $bstmt->fetch();
    $bstmt->close();
}

$resp = [
    'success' => true,
    'data' => [
        'id' => $id,
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        'role' => htmlspecialchars($role, ENT_QUOTES, 'UTF-8'),
        'role_name' => htmlspecialchars($role_name ?: $role, ENT_QUOTES, 'UTF-8'),
        'branch' => htmlspecialchars($branch, ENT_QUOTES, 'UTF-8'),
        'branch_name' => htmlspecialchars($branch_name ?: $branch, ENT_QUOTES, 'UTF-8'),
        'image' => htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8')
    ]
];

// Clear any buffered output (such as accidental HTML) and send clean JSON
while (ob_get_level() > 0) ob_end_clean();
echo json_encode($resp);

?>
