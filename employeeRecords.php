<?php
include 'ActivityTracker.php';
include_once 'employeeFunctions.php';   
include 'loginChecker.php';
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
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Branch</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>                      
                            <?php employeeData(); ?>                      
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
        </script>
    </body>
</html>