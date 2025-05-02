<?php
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'employee-inventory-funcs.php'; // Use the employee-specific functions

// Get sort parameters from URL
$sort = $_GET['sort'] ?? 'ProductID';
$order = $_GET['order'] ?? 'ASC';

// Get employee's branch information
$employeeID = $_SESSION['id'] ?? '';
$branchName = '';
$branchCode = '';

$lowInventory = [];
if (!empty($branchCode)) {
    $lowInventory = getLowInventoryForEmployee($branchCode);
}

// Get the employee's branch details
$link = connect();
if ($link) {
    $sql = "SELECT e.BranchCode, b.BranchName 
            FROM employee e
            JOIN BranchMaster b ON e.BranchCode = b.BranchCode
            WHERE e.EmployeeID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $branchCode = $row['BranchCode'];
        $branchName = $row['BranchName'];
    }
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <title>Employee | Inventory</title>
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
            .product-img {
                width: 50px;
                height: 50px;
                object-fit: cover;
                border-radius: 4px;
            }
            .action-btn {
                padding: 5px 10px;
                margin: 0 3px;
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
            .branch-info {
                background-color: #e9f5ff;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
        </style>
    </head>

    <body>
        <?php include "sidebar.php"; ?>

        <?php if (!empty($lowInventory)) : ?>
            <div class="modal fade" id="lowInventoryModal" tabindex="-1" aria-labelledby="lowInventoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="lowInventoryModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Low Inventory Alert</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>The following products have low stock levels (≤ 10 units):</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Model</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowInventory as $item) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['ProductID']) ?></td>
                                            <td><?= htmlspecialchars($item['Model']) ?></td>
                                            <td><?= htmlspecialchars($item['CategoryType']) ?></td>
                                            <td><?= $item['Stocks'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-boxes me-2"></i> Inventory Management</h1>            
            </div>
            
        
            <div class="filter-container">
                <div class="branch-info">
                    <h5><i class="fas fa-store me-2"></i> <?= htmlspecialchars($branchName) ?> Branch</h5>
                </div>
            </div>
            
            <div class="table-instructions alert alert-info" style="margin-bottom: 20px; padding: 10px 15px; border-radius: 4px;">
                <strong>Instruction:</strong>
                <ul style="margin-bottom: 0; padding-left: 20px;">
					<li>Click any column header to sort the table in ascending/descending order.</li>
                </ul>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable <?= ($sort == 'ProductID' ? 'active' : '') ?>" onclick="sortTable('ProductID')">
                                    Product ID
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'ProductID' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?= ($sort == 'CategoryType' ? 'active' : '') ?>" onclick="sortTable('CategoryType')">
                                    Category
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'CategoryType' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?= ($sort == 'ShapeDescription' ? 'active' : '') ?>" onclick="sortTable('ShapeDescription')">
                                    Shape
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'ShapeDescription' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?= ($sort == 'BrandName' ? 'active' : '') ?>" onclick="sortTable('BrandName')">
                                    Brand
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'BrandName' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?= ($sort == 'Model' ? 'active' : '') ?>" onclick="sortTable('Model')">
                                    Model
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'Model' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                                <th>Material</th>
                                <th>Price</th>
                                <th>Product Image</th>
                                <th class="sortable <?= ($sort == 'Stocks' ? 'active' : '') ?>" onclick="sortTable('Stocks')">
                                    Stocks
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?= ($sort == 'Stocks' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up') ?>"></i>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php getInventory($sort, $order); ?>
                        </tbody>
                    </table>
                </div>
            </div>           
        </div>

        <div id="lowInventoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Low Inventory Alert</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>The following products are running low on stock:</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Current Stock</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody id="lowInventoryTableBody">
                                    <!-- Data will be inserted here by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Function to handle table sorting
            function sortTable(column) {
                const urlParams = new URLSearchParams(window.location.search);
                let currentSort = urlParams.get('sort') || 'ProductID';
                let currentOrder = urlParams.get('order') || 'ASC';
                
                let newOrder = 'ASC';
                if (currentSort === column) {
                    newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
                }
                
                // Update URL parameters
                urlParams.set('sort', column);
                urlParams.set('order', newOrder);
                
                // Reload the page with new parameters
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }

            // Show low inventory modal if there are low stock items
            document.addEventListener('DOMContentLoaded', function() {
                // You can add logic here to check for low inventory and show the modal if needed
                // Example: fetch('/api/low-inventory?branch=<?= $branchCode ?>')
                //          .then(response => response.json())
                //          .then(data => { if(data.length > 0) show modal })
            });
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Low inventory modal handling
            const lowInventoryModalEl = document.getElementById('lowInventoryModal');
            
            if (lowInventoryModalEl && !sessionStorage.getItem('empLowInventoryShown')) {
                const lowInventoryModal = new bootstrap.Modal(lowInventoryModalEl);
                lowInventoryModal.show();
                sessionStorage.setItem('empLowInventoryShown', 'true');
            }

            // Clear flag when user leaves the page
            window.addEventListener('beforeunload', function() {
                sessionStorage.removeItem('empLowInventoryShown');
            });
        });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>
    </body>
</html>