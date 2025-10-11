<?php
include 'setup.php';
    function displayBranches() {
        $link = connect();
        $sql = "SELECT * FROM BranchMaster";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td class='align-middle'>" . htmlspecialchars($row['BranchName']) . "</td>
                    <td class='align-middle'>" . htmlspecialchars($row['BranchLocation']) . "</td>
                    <td class='align-middle'>" . htmlspecialchars($row['ContactNo']) . "</td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='branchCode' value='" . htmlspecialchars($row['BranchCode']) . "'>
                            <input type='hidden' name='branchName' value='" . htmlspecialchars($row['BranchName']) . "'>
                            <input type='hidden' name='branchLocation' value='" . htmlspecialchars($row['BranchLocation']) . "'>
                            <input type='hidden' name='contactNo' value='" . htmlspecialchars($row['ContactNo']) . "'>
                            <button type='submit' class='btn btn-success mt-2' name='editBranchBtn' style='font-size:18px'><i class='fa-solid fa-pen-to-square'></i></button>
                        </form>
                    </td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='branchCode' value='" . htmlspecialchars($row['BranchCode']) . "'>
                            <input type='hidden' name='branchName' value='" . htmlspecialchars($row['BranchName']) . "'>
                            <input type='hidden' name='branchLocation' value='" . htmlspecialchars($row['BranchLocation']) . "'>
                            <input type='hidden' name='contactNo' value='" . htmlspecialchars($row['ContactNo']) . "'>
                            <button type='submit' class='btn btn-danger mt-2' name='deleteBranchBtn' style='font-size:18px'><i class='fa-solid fa-trash'></i></button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No branches found</td></tr>";
        }
    }

    function addBranchModal() {
        echo 
        '<div class = "modal fade" id="addBranchModal" aria-labelledby="addBranchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-secondary-subtle">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBranchModalLabel">Add New Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="margin-top: -1.5rem;">
                        <hr>
                        <form method="post" enctype="multipart/form-data">
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
        $link = connect();
        $branchCode = generate_BranchCode();
        $branchName = $_POST['branchName'] ?? '';
        $branchLocation = $_POST['branchLocation'] ?? '';
        $contactNo = $_POST['contactNo'] ?? '';

        $sql = "INSERT INTO BranchMaster (BranchCode, BranchName, BranchLocation, ContactNo) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $branchCode, $branchName, $branchLocation, $contactNo);
        $stmt = mysqli_stmt_execute($stmt);
        if ($stmt) {
            // Log the add action
            $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
            if ($empId) { log_action($empId, $branchCode, 'branch', 3, "Added branch: $branchName (Code: $branchCode)"); }

            echo 
            '<div class="modal fade" id="addBranchModalSuccess" tabindex="-1" aria-labelledby="addBranchModalSuccessLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-secondary-subtle">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBranchModalSuccessLabel">Success</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="margin-top: -1.5rem;">
                            <hr>
                            <p>Branch added successfully.</p>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success w-25" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            echo
            '<div class="modal fade" id="addBranchModalSuccess" tabindex="-1" aria-labelledby="addBranchModalSuccessLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-secondary-subtle">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBranchModalSuccessLabel">Error</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="margin-top: -1.5rem;">
                            <hr>
                            <p>Error adding branch: '.mysqli_error($link).'</p>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        mysqli_close($link);
    }
    function editBranch(){
        $branchCode = $_POST['branchCode'] ?? '';
        $branchName = $_POST['branchName'] ?? '';
        $branchLocation = $_POST['branchLocation'] ?? '';
        $contactNo = $_POST['contactNo'] ?? '';

        echo 
        '<div class = "modal fade" id="editBranchModal" aria-labelledby="editBranchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-secondary-subtle">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="margin-top: -1.5rem;">
                        <hr>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="branchCode" value="'.htmlspecialchars($branchCode).'">
                            <div class="mb-3">
                                <label for="branchName" class="form-label">Branch Name</label>
                                <input type="text" class="form-control" id="branchName" name="branchName" value="'.htmlspecialchars($branchName).'" required>
                            </div>
                            <div class="mb-3">
                                <label for="branchLocation" class="form-label">Branch Location</label>
                                <input type="text" class="form-control" id="branchLocation" name="branchLocation" value="'.htmlspecialchars($branchLocation).'" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactNo" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contactNo" name="contactNo" value="'.htmlspecialchars($contactNo).'" required>
                            </div>

                            <hr>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success w-25" name="saveBtn">Save</button>
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
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

        $sql = "UPDATE BranchMaster SET 
                BranchName = '".mysqli_real_escape_string($link, $branchName)."', 
                BranchLocation = '".mysqli_real_escape_string($link, $branchLocation)."', 
                ContactNo = '".mysqli_real_escape_string($link, $contactNo)."' 
                WHERE BranchCode = '".mysqli_real_escape_string($link, $branchCode)."'";
        
        if (mysqli_query($link, $sql)) {
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
        mysqli_stmt_bind_param($stmt, 's', $branchCode);
        $stmt = mysqli_stmt_execute($stmt);

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
                                <button type="button" class="btn btn-success w-25" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
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
                                <button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        mysqli_close($link);
    }        
    
?>