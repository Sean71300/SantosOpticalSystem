<?php
include_once 'employeeFunctions.php';  
include 'ActivityTracker.php';
include 'loginChecker.php';

// Start with database connection
$conn = connect();

if (isset($_GET["EmployeeID"])) {
    $id = $_GET["EmployeeID"];

    // Branch scoping enforcement for Admin (1) and Employee (2)
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
        if ($sessionBranch === '') {
            header('Location: employeeRecords.php');
            exit;
        }
        if ($chk = $conn->prepare("SELECT BranchCode FROM employee WHERE EmployeeID = ? LIMIT 1")) {
            $chk->bind_param('s', $id);
            $chk->execute();
            $rsChk = $chk->get_result();
            if (!$rsChk || !($rowC = $rsChk->fetch_assoc()) || (string)$rowC['BranchCode'] !== $sessionBranch) {
                $chk->close();
                header('Location: employeeRecords.php');
                exit;
            }
            $chk->close();
        }
    }
    $Aid = generate_ArchiveID();
    $Eid = $_SESSION["id"];
    setStatus($id);
    // Archive the employee
    $sqlEmployee = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType) VALUES (?, ?, ?, 'employee')";
    $stmt = $conn->prepare($sqlEmployee);
    $stmt->bind_param("iii", $Aid, $id, $Eid);
    $stmt->execute();
    $stmt->close();
    
    // Get employee name for logs
    $query = "SELECT EmployeeName FROM employee WHERE EmployeeID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $name = $row['EmployeeName'];
    $stmt->close();
    
    // Create log entry
    $Logsid = generate_LogsID();
    $stmt = $conn->prepare("INSERT INTO Logs 
                        (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description, Upd_dt)
                        VALUES
                        (?, ?, ?, 'employee', '5', ?, NOW())");
    $stmt->bind_param("ssss", $Logsid, $Eid, $id, $name);
    $stmt->execute();
    $stmt->close();
    
    // Close connection
    mysqli_close($conn);
    
    // Show modal and refresh
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Employee Deleted</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .modal {
                display: flex !important;
                align-items: center;
                justify-content: center;
            }
            .modal-dialog {
                margin: 0;
                width: auto;
                max-width: 400px;
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Show modal
                var myModal = new bootstrap.Modal(document.getElementById("successModal"), {
                    backdrop: "static",
                    keyboard: false
                });
                myModal.show();
                
                // Redirect after 1 second
                setTimeout(function() {
                    window.location.href = "employeeRecords.php";
                }, 2000);
            });
        </script>
    </head>
    <body>
        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Employee Removed Succefully</h5>
                    </div>                    
                    <div class="modal-body text-center">
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    </body>
    </html>';
    exit;
}

// If no EmployeeID provided, redirect immediately
header("Location: employeeRecords.php");
exit;
?>