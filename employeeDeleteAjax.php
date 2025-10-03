<?php
header('Content-Type: application/json');
include_once 'employeeFunctions.php';
include 'ActivityTracker.php';
if (session_status() == PHP_SESSION_NONE) session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $id = $_POST['EmployeeID'] ?? '';
    if (empty($id)) throw new Exception('Missing EmployeeID');

    // Branch scoping enforcement: Admin (1) and Employee (2) can only delete within their branch
    $sessionRole = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
    $sessionBranch = isset($_SESSION['branchcode']) ? (string)$_SESSION['branchcode'] : '';
    $usernameSess = $_SESSION['username'] ?? '';
    $isRestrictedRole = in_array($sessionRole, [1,2], true);
    if ($isRestrictedRole) {
        if ($sessionBranch === '' && $usernameSess !== '') {
            $connResolve = connect();
            if ($rs = $connResolve->prepare("SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1")) {
                $rs->bind_param('s', $usernameSess);
                if ($rs->execute()) {
                    $resR = $rs->get_result();
                    if ($resR && ($rw = $resR->fetch_assoc())) {
                        $_SESSION['branchcode'] = (string)$rw['BranchCode'];
                        $sessionBranch = $_SESSION['branchcode'];
                    }
                }
                $rs->close();
            }
        }
        if ($sessionBranch === '') throw new Exception('Unable to resolve your branch.');
        // Check target employee branch
        $connChk = connect();
        if ($chk = $connChk->prepare("SELECT BranchCode FROM employee WHERE EmployeeID = ? LIMIT 1")) {
            $chk->bind_param('s', $id);
            $chk->execute();
            $rsChk = $chk->get_result();
            if (!$rsChk || !($rowC = $rsChk->fetch_assoc())) {
                throw new Exception('Employee not found');
            }
            if ((string)$rowC['BranchCode'] !== $sessionBranch) {
                throw new Exception('You may only remove employees in your branch.');
            }
            $chk->close();
        }
    }

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
