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

// Debug: log incoming POST and FILES to help diagnose missing fields (append-only)
$logLine = "\n[".date('Y-m-d H:i:s')."] POST=".json_encode($_POST)." FILES=".json_encode(array_map(function($f){return ['name'=>$f['name'],'error'=>$f['error'],'size'=>$f['size']];}, $_FILES))."\n";
@file_put_contents(__DIR__ . '/employeeUpdate_debug.log', $logLine, FILE_APPEND);

// If role/branch/username missing, try to load from existing employee row (fallback)
if (!empty($id) && (empty($role) || empty($branch) || empty($username))) {
    $conn = connect();
    $pst = $conn->prepare("SELECT RoleID, BranchCode, LoginName FROM employee WHERE EmployeeID = ? LIMIT 1");
    if ($pst) {
        $pst->bind_param('s', $id);
        $pst->execute();
        $pst->bind_result($exist_role, $exist_branch, $exist_login);
        if ($pst->fetch()) {
            if (empty($role)) $role = $exist_role;
            if (empty($branch)) $branch = $exist_branch;
            if (empty($username)) $username = $exist_login;
        }
        $pst->close();
    }
}

// Validate and report missing fields for debugging
$missing = [];
foreach (['id','name','username','email','phone','role','branch'] as $f) {
    if (empty($$f)) $missing[] = $f;
}
if (!empty($missing)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields: '.implode(', ', $missing), 'missing' => $missing]);
    exit;
}

// Handle image upload; new handleImage returns [$err, $imagePathOrNull, $isUploaded]
list($err, $imagePathOrNull, $isUploaded) = handleImage($id);
if ($err) {
    echo json_encode(['success' => false, 'message' => $err]);
    exit;
}

$conn = connect();
$upd_by = $_SESSION['full_name'] ?? '';
$employee_id = $_SESSION['id'] ?? '';

// Build UPDATE dynamically: only set EmployeePicture if a new file was uploaded
if ($isUploaded) {
    $stmt = $conn->prepare("UPDATE employee SET EmployeeName = ?, EmployeePicture = ?, EmployeeEmail = ?, EmployeeNumber = ?, RoleID = ?, LoginName = ?, Upd_by = ?, BranchCode = ? WHERE EmployeeID = ?");
    $stmt->bind_param('sssssssss', $name, $imagePathOrNull, $email, $phone, $role, $username, $upd_by, $branch, $id);
} else {
    // Do not modify EmployeePicture
    $stmt = $conn->prepare("UPDATE employee SET EmployeeName = ?, EmployeeEmail = ?, EmployeeNumber = ?, RoleID = ?, LoginName = ?, Upd_by = ?, BranchCode = ? WHERE EmployeeID = ?");
    $stmt->bind_param('ssssssss', $name, $email, $phone, $role, $username, $upd_by, $branch, $id);
}
if (!$stmt->execute()) {
    $errno = $stmt->errno;
    $errMsg = $stmt->error;
    if ($errno == 1062) {
        echo json_encode(['success' => false, 'message' => 'Update failed: username already exists', 'error' => $errMsg, 'errno' => $errno]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: '.$errMsg, 'error' => $errMsg, 'errno' => $errno]);
    }
    exit;
}
$stmt->close();

// Log
EGenerateLogs($employee_id, $id, $name);

// (session refresh behavior was removed per revert request)

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

// Determine image URL for response: use uploaded path if present, otherwise load existing picture
if ($isUploaded && !empty($imagePathOrNull)) {
    $responseImage = $imagePathOrNull;
} else {
    // fetch existing EmployeePicture
    $q = $conn->prepare("SELECT EmployeePicture FROM employee WHERE EmployeeID = ? LIMIT 1");
    $responseImage = '';
    if ($q) {
        $q->bind_param('s', $id);
        $q->execute();
        $q->bind_result($existingPic);
        if ($q->fetch()) {
            $responseImage = $existingPic;
        }
        $q->close();
    }
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
    'image' => htmlspecialchars($responseImage, ENT_QUOTES, 'UTF-8'),
    'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
    ]
];

// Clear any buffered output (such as accidental HTML) and send clean JSON
while (ob_get_level() > 0) ob_end_clean();
echo json_encode($resp);

?>
