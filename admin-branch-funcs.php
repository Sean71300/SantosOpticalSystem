<?php
include 'setup.php';
    function displayBranches() {
        if ($ok) {
            // Log the add action
            $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
            if ($empId) { log_action($empId, $branchCode, 'branch', 3, "Added branch: $branchName (Code: $branchCode)"); }
            // Redirect (PRG) to avoid duplicate form submissions
            header('Location: admin-branch.php?result=add_success');
            exit();
        } else {
            header('Location: admin-branch.php?result=add_error');
            exit();
        }
        mysqli_close($link);
                            <div class="mb-3">
                                <label for="branchName" class="form-label">Branch Name</label>
                                <input type="text" class="form-control" id="branchName" name="branchName" required>
                            </div>
                            <div class="mb-3">
                                <label for="branchLocation" class="form-label">Branch Location</label>
                                <input type="text" class="form-control" id="branchLocation" name="branchLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactNo" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contactNo" name="contactNo" required inputmode="numeric" pattern="[0-9]*" maxlength="15" oninput="this.value=this.value.replace(/[^0-9]/g,\'\')">
                                <div class="form-text">Numbers only.</div>
                            </div>
                            <hr>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success w-25" name="addBranchBtn">Add Branch</button>
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>';
    }

    function addBranch() {
        if ($ok) {
            // Log the edit action
            $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
            if ($empId) { log_action($empId, $branchCode, 'branch', 4, "Edited branch: $branchName (Code: $branchCode)"); }
            header('Location: admin-branch.php?result=edit_success');
            exit();
        } else {
            header('Location: admin-branch.php?result=edit_error');
            exit();
        }
        mysqli_close($link);
                            <p>Error adding branch: '.mysqli_error($link).'</p>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            if ($ok) {
                header('Location: admin-branch.php?result=delete_success');
                exit();
            } else {
                header('Location: admin-branch.php?result=delete_error');
                exit();
            }
            mysqli_close($link);
                </div>
            </div>
        </div>';
                            
    }

    function confirmEditBranch() {
        $link = connect();
        $branchCode = $_POST['branchCode'] ?? '';
        $branchName = $_POST['branchName'] ?? '';
        $branchLocation = $_POST['branchLocation'] ?? '';
        $contactNo = $_POST['contactNo'] ?? '';

        $sql = "UPDATE BranchMaster SET BranchName = ?, BranchLocation = ?, ContactNo = ? WHERE BranchCode = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $branchName, $branchLocation, $contactNo, $branchCode);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            // Log the edit action
            $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
            if ($empId) { log_action($empId, $branchCode, 'branch', 4, "Edited branch: $branchName (Code: $branchCode)"); }

            echo '<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content bg-secondary-subtle">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editBranchModalLabel">Success</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="margin-top: -1.5rem;">
                                <hr>
                                <p>Branch updated successfully.</p>
                                <hr>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success w-25" data-bs-dismiss="modal">OK</button>
                                </div>
                                </div>
                                </div>
                                </div>
                                </div>';
        } else {
            echo 
            '<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-secondary-subtle">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBranchModalLabel">Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="margin-top: -1.5rem;">
                            <hr>
                            <p>Error updating branch: '.mysqli_error($link).'</p>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
        mysqli_close($link);
    }

    function deleteBranch() {
        echo 
        '<div class="modal fade" id="deleteBranchModal" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteBranchModalLabel">Delete Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3 pb-3">
                        <p>Are you sure you want to delete this branch?</p>
                        <form method="post">
                            <input type="hidden" name="branchCode" value="'.htmlspecialchars($_POST['branchCode'] ?? '').'">
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger w-25" name="confirmDeleteBtn">Delete</button>
                                <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>';
    }

    function confirmDeleteBranch() {
        $link = connect();
        $branchCode = $_POST['branchCode'] ?? '';
        $sql = "DELETE FROM BranchMaster WHERE BranchCode = ?";
        $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $branchCode);
            $ok = mysqli_stmt_execute($stmt);

        if ($stmt) {
            echo 
            '<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-secondary-subtle">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteBranchModalLabel">Success</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="margin-top: -1.5rem;">
                            <hr>
                            <p>Branch deleted successfully.</p>
                            <hr>
                            <div class="modal-footer">
                                    <button type="button" class="btn btn-success w-25" data-bs-dismiss="modal" onclick="window.location.href=\'admin-branch.php\'">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            echo 
            '<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-secondary-subtle">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteBranchModalLabel">Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="margin-top: -1.5rem;">
                            <hr>
                            <p>Error deleting branch: '.mysqli_error($link).'</p>
                            <hr>
                            <div class="modal-footer">
                                    <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal" onclick="window.location.href=\'admin-branch.php\'">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        mysqli_close($link);
    }        
    
?>