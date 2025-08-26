<?php
include 'employeeFunctions.php';
include 'ActivityTracker.php';
   
include 'loginChecker.php';

// Get sort parameters from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Employee Records | Santos Optical</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <style>
            body {
                background-color: #f5f7fa;
                display: flex;
            }
            .sidebar {
                background-color: white;
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;
                position: fixed;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            }
            .sidebar-header {
                padding: 0 20px 20px;
                border-bottom: 1px solid rgba(0,0,0,0.1);
            }
            .sidebar-item {
                padding: 12px 20px;
                margin: 5px 0;
                border-radius: 0;
                display: flex;
                align-items: center;
                color: #2c3e50;
                transition: all 0.3s;
                text-decoration: none;
            }
            .sidebar-item:hover {
                background-color: #f8f9fa;
                color: #2c3e50;
            }
            .sidebar-item.active {
                background-color: #e9ecef;
                color: #2c3e50;
                font-weight: 500;
            }   
            .sidebar-item i {
                margin-right: 10px;
                width: 20px;
                text-align: center;
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
                width: calc(100% - 250px);
            }
            .table-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
            }
            .employee-img {
                width: 50px;
                height: 50px;
                object-fit: cover;
                border-radius: 50%;
            }
            .status-active {
                color: #28a745;
                font-weight: 500;
            }
            .status-inactive {
                color: #dc3545;
                font-weight: 500;
            }
            .action-btn {
                padding: 5px 10px;
                margin: 0 3px;
            }
            /* Sorting styles - updated to match customerRecords.php */
            .sortable {
                cursor: pointer;
                position: relative;
                padding-right: 25px;
            }
            .sortable:hover {
                background-color: #f8f9fa;
            }
            .sort-icon {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                display: none;
            }
            .sortable.active .sort-icon {
                display: inline-block;                
            }
        </style>
    </head>
    <body>
        <?php include "sidebar.php"?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-users-cog me-2"></i> Employee Records</h1>
                    <a class="btn btn-primary" href="employeeCreate.php" role="button">
                        <i class="fas fa-plus me-2"></i> New Employee
                    </a>            
                </div>
                
                <div class="table-instructions alert alert-info" style="margin-bottom: 20px; padding: 10px 15px; border-radius: 4px;">
                <strong>Instructions:</strong>
                <ul style="margin-bottom: 0; padding-left: 20px;">
                    <li>To add an Employee, click the button at the top right.</li>
                    <li>To edit or delete Employee, click the button at the 'Actions' column.</li>
					<li>Click any column header to sort the table in ascending/descending order.</li>
                </ul>
            </div>
                
                <div class="table-responsive">
                    <table class="table table-hover text-center">
                        <thead class="table-light">
                            <tr>
                            <th class="sortable <?php echo $sort == 'EmployeeID' ? 'active' : ''; ?>" onclick="sortTable('EmployeeID')">
                                ID
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'EmployeeID' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th class="sortable <?php echo $sort == 'EmployeeName' ? 'active' : ''; ?>" onclick="sortTable('EmployeeName')">
                                Name
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'EmployeeName' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th class="sortable <?php echo $sort == 'EmployeeEmail' ? 'active' : ''; ?>" onclick="sortTable('EmployeeEmail')">
                                Email
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'EmployeeEmail' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th>
                                Contact                               
                            </th>
                            <th class="sortable <?php echo $sort == 'RoleID' ? 'active' : ''; ?>" onclick="sortTable('RoleID')">
                                Role
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'RoleID' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                            <th>Image</th>                            
                            <th class="sortable <?php echo $sort == 'BranchCode' ? 'active' : ''; ?>" onclick="sortTable('BranchCode')">
                                Branch
                                <span class="sort-icon">
                                    <i class="fas fa-sort-<?php echo $sort == 'BranchCode' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                </span>
                            </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>                      
                            <?php employeeData($sort, $order); ?>                      
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add confirmation for delete actions
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        if(!confirm('Are you sure you want to delete this employee?')) {
                            e.preventDefault();
                        }
                    });
                });

                // Intercept edit clicks to show modal confirmation and populate details
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Read data attributes
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name') || '';
                        const image = this.getAttribute('data-image') || '';
                        const role = this.getAttribute('data-role') || '';
                        const roleId = this.getAttribute('data-roleid') || '';
                        const branch = this.getAttribute('data-branch') || '';
                        const branchCode = this.getAttribute('data-branchcode') || '';
                        const username = this.getAttribute('data-username') || '';
                        const email = this.getAttribute('data-email') || '';
                        const phone = this.getAttribute('data-phone') || '';

                        const modal = document.getElementById('editConfirmModal');

                        // Populate form fields
                        modal.querySelector('.modal-emp-id').value = id;
                        modal.querySelector('.modal-name').value = name;
                        modal.querySelector('.modal-username').value = username;
                        modal.querySelector('.modal-email').value = email;
                        modal.querySelector('.modal-phone').value = phone;
                        modal.querySelector('.modal-role').value = roleId;
                        modal.querySelector('.modal-branch').value = branchCode;

                        // Populate image display
                        const imgEl = modal.querySelector('.emp-img');
                        if (image) {
                            imgEl.src = image;
                            imgEl.style.display = 'inline-block';
                        } else {
                            imgEl.style.display = 'none';
                        }

                        var bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    });
                });

                // Modal image preview when a new file is chosen
                const modalImageInput = document.getElementById('modalImage');
                if (modalImageInput) {
                    modalImageInput.addEventListener('change', function() {
                        const file = this.files && this.files[0];
                        const imgEl = document.querySelector('#editConfirmModal .emp-img');
                        if (file && imgEl) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                imgEl.src = e.target.result;
                                imgEl.style.display = 'inline-block';
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Handle modal form submission via AJAX
                const modalForm = document.getElementById('modalEditForm');
                if (modalForm) {
                    modalForm.addEventListener('submit', function(evt) {
                        evt.preventDefault();
                        const submitBtn = modalForm.querySelector('.modal-save');
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Saving...';

                        const formData = new FormData(modalForm);

                        fetch('employeeUpdate.php', {
                            method: 'POST',
                            body: formData
                        }).then(r => r.text())
                        .then(text => {
                            let resp;
                            try {
                                resp = JSON.parse(text);
                            } catch (err) {
                                // Server returned HTML or invalid JSON — show for debugging
                                console.error('Invalid JSON response from server:', text);
                                alert('Server returned invalid response. Check console for details.');
                                return;
                            }

                            if (!resp.success) {
                                alert('Update failed: ' + (resp.message || 'unknown'));
                                return;
                            }

                            // Update the table row inline
                            const data = resp.data;
                            const rows = document.querySelectorAll('table tbody tr');
                            rows.forEach(row => {
                                const idCell = row.querySelector('td:first-child');
                                if (idCell && idCell.textContent.trim() === data.id.toString()) {
                                    row.querySelector('td:nth-child(2)').textContent = data.name;
                                    row.querySelector('td:nth-child(3)').textContent = data.email;
                                    row.querySelector('td:nth-child(4)').textContent = data.phone;
                                    row.querySelector('td:nth-child(5)').textContent = data.role_name || data.role;
                                    row.querySelector('td:nth-child(6) img').src = data.image;
                                    row.querySelector('td:nth-child(7)').textContent = data.branch_name || data.branch;

                                    // Also update data-* attributes on Edit button so modal stays in sync
                                    const editBtn = row.querySelector('.edit-btn');
                                    if (editBtn) {
                                        editBtn.setAttribute('data-name', data.name);
                                        editBtn.setAttribute('data-image', data.image);
                                        editBtn.setAttribute('data-email', data.email);
                                        editBtn.setAttribute('data-phone', data.phone);
                                        editBtn.setAttribute('data-role', data.role_name || data.role);
                                        editBtn.setAttribute('data-roleid', data.role);
                                        editBtn.setAttribute('data-branch', data.branch_name || data.branch);
                                        editBtn.setAttribute('data-branchcode', data.branch);
                                    }
                                }
                            });

                            // Close modal
                            var bsModalInstance = bootstrap.Modal.getInstance(document.getElementById('editConfirmModal'));
                            if (bsModalInstance) bsModalInstance.hide();
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred while updating.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Save changes';
                        });
                    });
                }
            });
        </script>

            // Function to handle table sorting
            function sortTable(column) {
                const urlParams = new URLSearchParams(window.location.search);
                let currentSort = urlParams.get('sort') || 'id';
                let currentOrder = urlParams.get('order') || 'asc';
                
                let newOrder = 'asc';
                if (currentSort === column) {
                    newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                }
                
                // Update URL parameters
                urlParams.set('sort', column);
                urlParams.set('order', newOrder);
                
                // Reload the page with new parameters
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }
        </script>

    <!-- Edit confirmation modal (moved inside body so Bootstrap can manage it) -->
    <div class="modal fade" id="editConfirmModal" tabindex="-1" aria-labelledby="editConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editConfirmModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="modalEditForm" method="post" action="employeeUpdate.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" class="modal-emp-id">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img class="emp-img mb-3" src="" alt="Employee Image" style="width:128px;height:128px;object-fit:cover;border-radius:8px;display:none;" />
                            <div>
                                <label for="modalImage" class="btn btn-sm btn-success">
                                    <input type="file" name="IMAGE" id="modalImage" accept=".jpg, .png, .jpeg" style="display:none;">
                                    <i class="fas fa-camera me-1"></i> Change
                                </label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control modal-name" name="name" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control modal-username" name="username" required>
                            </div>
                            <div class="mb-2 row">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control modal-email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control modal-phone" name="phone" required>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <div class="col-md-6">
                                    <label class="form-label">Branch</label>
                                    <select class="form-select modal-branch" name="branch" required>
                                        <?php // We'll populate with AJAX-updated options client-side if needed ?>
                                        <?php branchHandler(''); ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <select class="form-select modal-role" name="role" required>
                                        <?php roleHandler(''); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary modal-save">Save changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    </body>
</html>
