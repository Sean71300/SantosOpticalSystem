<?php
header('Content-Type: application/json');
include 'setup.php';
include 'employeeFunctions.php';
if (session_status() == PHP_SESSION_NONE) session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');

    // basic validation
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch = $_POST['branch'] ?? '';

    $missing = [];
    foreach (['name','username','password','email','phone','role','branch'] as $f) {
        if (empty($$f)) $missing[] = $f;
    }
    if (!empty($missing)) throw new Exception('Missing fields: ' . implode(', ', $missing));

    // generate id
    if (!function_exists('generate_EmployeeID')) throw new Exception('Missing helper generate_EmployeeID');
    $id = generate_EmployeeID();

    // handle image upload
    list($err, $imagePathOrNull, $isUploaded) = handleImage($id);
    if ($err) throw new Exception($err);
    if (!$imagePathOrNull) $imagePathOrNull = 'Images/default.jpg';

    // insert using prepared statement
    $conn = connect();
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
    $upd_by = $_SESSION['full_name'] ?? '';

    $stmt = $conn->prepare("INSERT INTO employee (EmployeeID,EmployeeName,EmployeePicture,EmployeeEmail,EmployeeNumber,RoleID,LoginName,Password,BranchCode,Status,Upd_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?)");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('sssssssss', $id, $name, $imagePathOrNull, $email, $phone, $role, $username, $hashed_pw, $branch, $upd_by);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new Exception('Insert failed: ' . $e);
    }
    $stmt->close();

    // fetch role/branch display names
    $role_name = '';
    $branch_name = '';
    $r = $conn->prepare("SELECT Description FROM roleMaster WHERE RoleID = ?");
    if ($r) { $r->bind_param('s', $role); $r->execute(); $r->bind_result($role_name); $r->fetch(); $r->close(); }
    $b = $conn->prepare("SELECT BranchName FROM BranchMaster WHERE BranchCode = ?");
    if ($b) { $b->bind_param('s', $branch); $b->execute(); $b->bind_result($branch_name); $b->fetch(); $b->close(); }

    // log
    $employee_id = $_SESSION['id'] ?? '';
    GenerateLogs($employee_id, $id, $name);

    echo json_encode(['success' => true, 'data' => [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
        'role_name' => $role_name ?: $role,
        'branch' => $branch,
        'branch_name' => $branch_name ?: $branch,
        'image' => $imagePathOrNull,
        'username' => $username
    ]]);

} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}

?>
