<?php
include 'setup.php';

/**
 * Display all branches as table rows. Used by admin-branch.php
 */
function displayBranches() {
    $link = connect();
    $sql = "SELECT BranchCode, BranchName, BranchLocation, ContactNo FROM BranchMaster ORDER BY BranchName";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $code, $name, $location, $contact);
        while (mysqli_stmt_fetch($stmt)) {
            $codeEsc = htmlspecialchars($code);
            $nameEsc = htmlspecialchars($name);
            $locEsc = htmlspecialchars($location);
            $contactEsc = htmlspecialchars($contact);
            echo "<tr>".
                 "<td>{$nameEsc}</td>".
                 "<td>{$locEsc}</td>".
                 "<td>{$contactEsc}</td>".
                 "<td>".
                     "<form method='post' style='display:inline-block;margin:0;'>".
                         "<input type='hidden' name='branchCode' value='{$codeEsc}'>".
                         "<button type='submit' name='editBranchBtn' class='btn btn-sm btn-warning action-btn'>Edit</button>".
                     "</form>".
                 "</td>".
                 "<td>".
                     "<form method='post' style='display:inline-block;margin:0;'>".
                         "<input type='hidden' name='branchCode' value='{$codeEsc}'>".
                         "<button type='submit' name='deleteBranchBtn' class='btn btn-sm btn-danger action-btn'>Delete</button>".
                     "</form>".
                 "</td>".
                 "</tr>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}

/**
 * Output add-branch modal HTML
 */
function addBranchModal() {
    echo '<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="addBranchModalLabel">Add Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post">'
                    .'<div class="mb-3">'
                        .'<label for="branchName" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchName" name="branchName" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocation" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocation" name="branchLocation" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="contactNo" class="form-label">Contact Number</label>'
                        .'<input type="text" class="form-control" id="contactNo" name="contactNo" inputmode="numeric" pattern="[0-9]*" maxlength="15">'
                        .'<div class="form-text">Numbers only.</div>'
                    .'</div>'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-success w-25" name="addBranchBtn">Add Branch</button>'
                        .'<button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';
}

/**
 * Handle add branch POST. Uses PRG to redirect after insert.
 */
function addBranch() {
    $link = connect();
    $branchName = trim($_POST['branchName'] ?? '');
    $branchLocation = trim($_POST['branchLocation'] ?? '');
    $contactNo = trim($_POST['contactNo'] ?? '');

    $sql = "INSERT INTO BranchMaster (BranchName, BranchLocation, ContactNo) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sss', $branchName, $branchLocation, $contactNo);
        $ok = mysqli_stmt_execute($stmt);
        $insertId = mysqli_insert_id($link);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }

    // Log action if logger exists
    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $insertId, 'branch', 3, "Added branch: {$branchName}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=add_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=add_error');
        exit();
    }
}

/**
 * Render edit modal populated with branch data. This function echoes HTML but does not redirect.
 */
function editBranch() {
    $link = connect();
    $branchCode = $_POST['branchCode'] ?? '';
    $sql = "SELECT BranchCode, BranchName, BranchLocation, ContactNo FROM BranchMaster WHERE BranchCode = ? LIMIT 1";
    $name = $location = $contact = '';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $branchCode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $code, $name, $location, $contact);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);

    $codeEsc = htmlspecialchars($code);
    $nameEsc = htmlspecialchars($name);
    $locEsc = htmlspecialchars($location);
    $contactEsc = htmlspecialchars($contact);

    echo '<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post">'
                    .'<input type="hidden" name="branchCode" value="' . $codeEsc . '">'
                    .'<div class="mb-3">'
                        .'<label for="branchNameEdit" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchNameEdit" name="branchName" value="' . $nameEsc . '" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocationEdit" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocationEdit" name="branchLocation" value="' . $locEsc . '" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="contactNoEdit" class="form-label">Contact Number</label>'
                        .'<input type="text" class="form-control" id="contactNoEdit" name="contactNo" value="' . $contactEsc . '" inputmode="numeric" pattern="[0-9]*" maxlength="15">'
                    .'</div>'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-success w-25" name="saveBtn">Save</button>'
                        .'<button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';
}

/**
 * Handle edit confirm POST (update). Uses PRG redirect.
 */
function confirmEditBranch() {
    $link = connect();
    $branchCode = (int)($_POST['branchCode'] ?? 0);
    $branchName = trim($_POST['branchName'] ?? '');
    $branchLocation = trim($_POST['branchLocation'] ?? '');
    $contactNo = trim($_POST['contactNo'] ?? '');

    $sql = "UPDATE BranchMaster SET BranchName = ?, BranchLocation = ?, ContactNo = ? WHERE BranchCode = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssi', $branchName, $branchLocation, $contactNo, $branchCode);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }

    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $branchCode, 'branch', 4, "Edited branch: {$branchName}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=edit_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=edit_error');
        exit();
    }
}

/**
 * Show a delete confirmation modal (renders form to POST confirmDeleteBtn)
 */
function deleteBranch() {
    $code = htmlspecialchars($_POST['branchCode'] ?? '');
    echo '<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="deleteBranchModalLabel">Delete Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<p>Are you sure you want to delete this branch?</p>'
                .'<form method="post">'
                    .'<input type="hidden" name="branchCode" value="' . $code . '">'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-danger w-25" name="confirmDeleteBtn">Delete</button>'
                        .'<button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';
}

/**
 * Handle delete POST and redirect (PRG)
 */
function confirmDeleteBranch() {
    $link = connect();
    $branchCode = (int)($_POST['branchCode'] ?? 0);
    $sql = "DELETE FROM BranchMaster WHERE BranchCode = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $branchCode);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }

    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $branchCode, 'branch', 5, "Deleted branch: {$branchCode}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=delete_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=delete_error');
        exit();
    }
}

?>