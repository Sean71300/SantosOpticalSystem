<?php
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'admin-branch-funcs.php';

// Only Super Admin (roleid 4) can access Branch Management
if (session_status() === PHP_SESSION_NONE) { @session_start(); }
$rid = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
if ($rid !== 4) {
    header('Location: Dashboard.php');
    exit();
}
?>

<html>  
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <title>Admin | Branch</title>
        <style>
            :root {
                --sidebar-width: 250px;
                --sidebar-collapsed-width: 80px;
                --mobile-breakpoint: 992px;
            }
            
            body {
                background-color: #f5f7fa;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            
            /* Sidebar Styles */
            .sidebar {
                background-color: white;
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;
                position: fixed;
                width: var(--sidebar-width);
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                z-index: 1000;
            }
            
            .sidebar-header {
                padding: 0 20px 20px;
                border-bottom: 1px solid rgba(0,0,0,0.1);
                text-align: center;
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
                white-space: nowrap;
                overflow: hidden;
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
                flex-shrink: 0;
            }
            
            /* Main Content Styles */
            .main-content {
                margin-left: var(--sidebar-width);
                padding: 20px;
                width: calc(100% - var(--sidebar-width));
                transition: all 0.3s ease;
            }
            
            .table-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
                overflow-x: auto;
            }
            
            .product-img {
                width: 50px;
                height: 50px;
                object-fit: cover;
                border-radius: 4px;
            }
            
            .action-btn {
                padding: 5px 8px;
                margin: 2px;
                font-size: 0.85rem;
            }
            
            .filter-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .sortable {
                cursor: pointer;
                position: relative;
                padding-right: 25px;
                white-space: nowrap;
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
            
            /* Mobile Menu Toggle */
            .menu-toggle {
                display: none;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1100;
                background: white;
                border: none;
                border-radius: 5px;
                padding: 8px 12px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            /* Responsive Styles */
            @media (max-width: 1200px) {
                .main-content {
                    padding: 15px;
                }
                
                .table-container, .filter-container {
                    padding: 15px;
                }
            }
            
            @media (max-width: 992px) {
                :root {
                    --sidebar-width: 80px;
                }
                
                .sidebar-item span {
                    display: none;
                }
                
                .sidebar-item i {
                    margin-right: 0;
                    font-size: 1.2rem;
                }
                
                .sidebar-header {
                    padding: 0 10px 20px;
                }
                
                .sidebar-header h3 {
                    display: none;
                }
                
                .sidebar-header img {
                    width: 40px;
                    height: 40px;
                }
            }
            
            @media (max-width: 768px) {
                body {
                    flex-direction: column;
                }
                
                .sidebar {
                    width: 100%;
                    height: auto;
                    position: relative;
                    display: none;
                }
                
                .sidebar.active {
                    display: block;
                }
                
                .main-content {
                    margin-left: 0;
                    width: 100%;
                    padding: 15px 10px;
                }
                
                .menu-toggle {
                    display: block;
                }
                
                .d-flex.justify-content-between {
                    flex-direction: column;
                    gap: 15px;
                }
                
                .filter-container .row > div {
                    margin-bottom: 10px;
                }
                
                .filter-container .row > div:last-child {
                    margin-bottom: 0;
                }
                
                .action-btn {
                    padding: 3px 6px;
                    font-size: 0.75rem;
                }
            }
            
            @media (max-width: 576px) {
                .product-img {
                    width: 40px;
                    height: 40px;
                }
                
                h1 {
                    font-size: 1.5rem;
                }
                
                .btn {
                    padding: 0.375rem 0.75rem;
                    font-size: 0.875rem;
                }
            }
            
            /* Print Styles */
            @media print {
                .sidebar, .menu-toggle, .btn {
                    display: none !important;
                }
                
                .main-content {
                    margin-left: 0;
                    width: 100%;
                    padding: 0;
                }
                
                .table-container {
                    box-shadow: none;
                    padding: 0;
                }
            }
        </style>
    </head>

    <body>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h1><i class="fas fa-boxes me-2"></i> Branches</h1>
                <button class="btn btn-primary" id="addBranchBtn" data-bs-toggle="modal" data-bs-target="#addBranchModal" type="button">
                    <i class="fas fa-plus me-2"></i> Add New Branch
                </button>            
            </div>

            <div class="table-instructions alert alert-info" style="margin-bottom: 20px; padding: 10px 15px; border-radius: 4px;">
                <strong>Instructions:</strong>
                <ul style="margin-bottom: 0; padding-left: 20px;">
                    <li>To add a branch, click the button at the top right.</li>
                    <li>To edit or delete a branch, click the button at the 'Actions' column.</li>
                </ul>
            </div>
            
            <div class="table-container">
                <table class="table table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Branch Name</th>
                            <th>Branch Location</th>
                            <th>Contact No.</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody">
                        <?php displayBranches(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
            addBranchModal();

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_POST['addBranchBtn'])) {
                    addBranch();
                }
                else
                if (isset($_POST['editBranchBtn'])) {
                    editBranch();
                }
                elseif (isset($_POST['deleteBranchBtn'])) {
                    deleteBranch();
                }
            }   
        ?>
    </body>

    <script>
        // Mobile menu toggle
            document.getElementById('menuToggle').addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            });
        // Modal handling
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_POST['addBranchBtn'])): ?>
                // Prefer showing the success modal if insert succeeded; fall back to form modal.
                var modalEl = document.getElementById('addBranchModalSuccess') || document.getElementById('addBranchModal');
                if (modalEl) { new bootstrap.Modal(modalEl).show(); }
            <?php elseif (isset($_POST['editBranchBtn'])): ?>
                var editModalEl = document.getElementById('editBranchModal');
                if (editModalEl) { new bootstrap.Modal(editModalEl).show(); }
            <?php elseif (isset($_POST['deleteBranchBtn'])): ?>
                var deleteModalEl = document.getElementById('deleteBranchModal');
                if (deleteModalEl) { new bootstrap.Modal(deleteModalEl).show(); }
            <?php endif; ?>
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>
</html>