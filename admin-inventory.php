<?php
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'admin-inventory-funcs.php'; // Include the functions file

// Get sort parameters from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ProductID';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <title>Admin | Inventories</title>
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
            /* Sorting styles - updated to match employeeRecords.php */
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
        <?php include "sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-boxes me-2"></i> Inventory Management</h1>
                <a class="btn btn-primary" href="admin-inventory-add.php" role="button">
                    <i class="fas fa-plus me-2"></i> Add Product
                </a>            
            </div>

            <div class="filter-container">
                <form method="post" class="row g-3">
                    <div class="col-md-8">
                        <label for="chooseBranch" class="form-label">Select Branch</label>
                        <select name="chooseBranch" id="chooseBranch" class="form-select">
                            <?php getBranches(); ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" name="searchProduct">
                            <i class="fas fa-search me-2"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">                                                
                        <thead class="table-light">
                            <tr>
                                <th class="sortable <?php echo $sort == 'ProductID' ? 'active' : ''; ?>" onclick="sortTable('ProductID')">
                                    Product ID
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'ProductID' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?php echo $sort == 'CategoryType' ? 'active' : ''; ?>" onclick="sortTable('CategoryType')">
                                    Category
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'CategoryType' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?php echo $sort == 'ShapeDescription' ? 'active' : ''; ?>" onclick="sortTable('ShapeDescription')">
                                    Shape
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'ShapeDescription' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?php echo $sort == 'BrandName' ? 'active' : ''; ?>" onclick="sortTable('BrandName')">
                                    Brand
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'BrandName' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th class="sortable <?php echo $sort == 'Model' ? 'active' : ''; ?>" onclick="sortTable('Model')">
                                    Model
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'Model' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th>Remarks</th>
                                <th>Product Image</th>
                                <th class="sortable <?php echo $sort == 'TotalCount' ? 'active' : ''; ?>" onclick="sortTable('TotalCount')">
                                    Total Count
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'TotalCount' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th>Updated by</th>
                                <th class="sortable <?php echo $sort == 'LastUpdated' ? 'active' : ''; ?>" onclick="sortTable('LastUpdated')">
                                    Last Updated
                                    <span class="sort-icon">
                                        <i class="fas fa-sort-<?php echo $sort == 'LastUpdated' ? (strtolower($order)) == 'asc' ? 'up' : 'down' : 'up'; ?>"></i>
                                    </span>
                                </th>
                                <th colspan="2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                if (isset($_POST['searchProduct'])) {
                                    getInventory($sort, $order);
                                }
                                elseif (isset($_POST['editProductBtn'])) {
                                    getInventory($sort, $order);
                                    editProduct();
                                }
                                elseif (isset($_POST['saveProductBtn'])) {
                                    confirmEditProduct();
                                    getInventory($sort, $order);
                                }
                                elseif (isset($_POST['deleteProductBtn'])) {
                                    confirmDeleteProduct();
                                    getInventory($sort, $order);
                                }
                                elseif (isset($_POST['confirmDeleteBtn'])) {
                                    deleteProduct();
                                    getInventory($sort, $order);
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>

        <script>
            // Modal handling
            document.addEventListener('DOMContentLoaded', function() {
                var editProductModalElement = document.getElementById("editProductModal");
                if (editProductModalElement) {
                    var editProductModal = new bootstrap.Modal(editProductModalElement);
                    editProductModal.show();
                }

                var deleteProductModalElement = document.getElementById("deleteProductModal");
                if (deleteProductModalElement) {
                    var deleteProductModal = new bootstrap.Modal(deleteProductModalElement);
                    deleteProductModal.show();
                }

                var confirmDeleteProductModal = document.getElementById("confirmDeleteProductModal");
                if (confirmDeleteProductModal) {
                    var confirmDeleteModal = new bootstrap.Modal(confirmDeleteProductModal);
                    confirmDeleteModal.show();
                }

                var confirmEditModal = document.getElementById("confirmEditModal");
                if (confirmEditModal) {
                    var confirmEditModal = new bootstrap.Modal(confirmEditModal);
                    confirmEditModal.show();
                }
            });

            // Add confirmation for delete actions
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if(!confirm('Are you sure you want to delete this product?')) {
                        e.preventDefault();
                    }
                });
            });

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
        </script>
    </body>
</html>