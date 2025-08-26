<?php
include 'setup.php';
include 'employeeFunctions.php';
include 'loginChecker.php';

header('Content-Type: application/json');

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
$resp = [
    'success' => true,
    'data' => [
        'id' => $id,
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        'role' => htmlspecialchars($role, ENT_QUOTES, 'UTF-8'),
        'branch' => htmlspecialchars($branch, ENT_QUOTES, 'UTF-8'),
        'image' => htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8')
    ]
];

echo json_encode($resp);

?>
