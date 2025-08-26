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
            // Add confirmation for delete actions
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if(!confirm('Are you sure you want to delete this employee?')) {
                        e.preventDefault();
                    }
                });
            });

            // Intercept edit clicks to show modal confirmation
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    const name = this.getAttribute('data-name') || 'this employee';
                    const modal = document.getElementById('editConfirmModal');
                    modal.querySelector('.modal-body .emp-name').textContent = name;
                    modal.querySelector('.confirm-edit').setAttribute('data-href', url);
                    var bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                });
            });

            // Handle confirm button inside modal
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('confirm-edit')) {
                    const url = e.target.getAttribute('data-href');
                    if (url) window.location.href = url;
                }
            });

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
    </body>
</html>

<!-- Edit confirmation modal -->
<div class="modal fade" id="editConfirmModal" tabindex="-1" aria-labelledby="editConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConfirmModalLabel">Confirm Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You are about to edit <strong class="emp-name"></strong>. Continue?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary confirm-edit">Edit</button>
            </div>
        </div>
    </div>
</div>
