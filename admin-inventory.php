<?php
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'admin-inventory-funcs.php';

// Get sort parameters from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ProductID';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Handle branch selection
if (isset($_POST['searchProduct'])) {
    $branchName = $_POST['chooseBranch'] ?? '';
    $_SESSION['current_branch'] = $branchName;
} else {
    $branchName = $_GET['branch'] ?? $_SESSION['current_branch'] ?? '';
}

// Store current branch in session
$_SESSION['current_branch'] = $branchName;

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editProductBtn'])) {
        editProduct();
    }
    elseif (isset($_POST['saveProductBtn'])) {
        confirmEditProduct();
        header("Location: admin-inventory.php?sort=$sort&order=$order&branch=".urlencode($branchName));
        exit();
    }
    elseif (isset($_POST['deleteProductBtn'])) {
        confirmDeleteProduct();
    }
    elseif (isset($_POST['confirmDeleteBtn'])) {
        deleteProduct();
        header("Location: admin-inventory.php?sort=$sort&order=$order&branch=".urlencode($branchName));
        exit();
    }
}
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
            <form method="post" class="row g-3" id="branchForm">
                <div class="col-md-8">
                    <label for="chooseBranch" class="form-label">Select Branch</label>
                    <select name="chooseBranch" id="chooseBranch" class="form-select form-select-sm">
                        <option value='' <?= empty($branchName) ? 'selected' : '' ?>>View All Branches</option>
                        <?php 
                        $link = connect();
                        if (!$link) {
                            die("Database connection failed: " . mysqli_connect_error());
                        }
                        
                        $sql = "SELECT BranchName FROM BranchMaster";
                        $result = mysqli_query($link, $sql);
                        
                        if (!$result) {
                            die("Query failed: " . mysqli_error($link));
                        }
                        
                        while($row = mysqli_fetch_assoc($result)) {
                            $selected = (strcasecmp($row['BranchName'], $branchName) === 0) ? 'selected' : '';
                            echo "<option value='".htmlspecialchars($row['BranchName'])."' $selected>".htmlspecialchars($row['BranchName'])."</option>";
                        }
                        mysqli_close($link);
                        ?>
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
                    <?php
                    // Display inventory table based on branch selection
                    if (empty($branchName)) {
                        // All branches view
                        echo '<table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable '.($sort == 'ProductID' ? 'active' : '').'" onclick="sortTable(\'ProductID\')">
                                            Product ID
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'ProductID' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'CategoryType' ? 'active' : '').'" onclick="sortTable(\'CategoryType\')">
                                            Category
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'CategoryType' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'ShapeDescription' ? 'active' : '').'" onclick="sortTable(\'ShapeDescription\')">
                                            Shape
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'ShapeDescription' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'BrandName' ? 'active' : '').'" onclick="sortTable(\'BrandName\')">
                                            Brand
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'BrandName' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'Model' ? 'active' : '').'" onclick="sortTable(\'Model\')">
                                            Model
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'Model' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th>Material</th>
                                        <th>Product Image</th>
                                        <th class="sortable '.($sort == 'TotalCount' ? 'active' : '').'" onclick="sortTable(\'TotalCount\')">
                                            Total Count
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'TotalCount' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        getInventory($sort, $order);
                        
                        echo '</tbody></table>';
                    } else {
                        // Specific branch view
                        echo '<table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable '.($sort == 'ProductID' ? 'active' : '').'" onclick="sortTable(\'ProductID\')">
                                            Product ID
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'ProductID' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'CategoryType' ? 'active' : '').'" onclick="sortTable(\'CategoryType\')">
                                            Category
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'CategoryType' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'ShapeDescription' ? 'active' : '').'" onclick="sortTable(\'ShapeDescription\')">
                                            Shape
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'ShapeDescription' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'BrandName' ? 'active' : '').'" onclick="sortTable(\'BrandName\')">
                                            Brand
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'BrandName' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th class="sortable '.($sort == 'Model' ? 'active' : '').'" onclick="sortTable(\'Model\')">
                                            Model
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'Model' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th>Material</th>
                                        <th>Price</th>
                                        <th>Product Image</th>
                                        <th class="sortable '.($sort == 'Count' ? 'active' : '').'" onclick="sortTable(\'Count\')">
                                            Count
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'Count' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        
                                        <th colspan="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        getInventory($sort, $order);
                        
                        echo '</tbody></table>';
                    }
                    ?>
                </div>
            </div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['editProductBtn'])) {
                    editProduct();
                }
                elseif (isset($_POST['saveProductBtn'])) {
                    confirmEditProduct();
                    getInventory();
                    echo 'burnik';
                }
                elseif (isset($_POST['deleteProductBtn'])) {                     
                    confirmDeleteProduct();
                    getInventory();
                }
                elseif (isset($_POST['confirmDeleteBtn'])) {
                    deleteProduct();
                    getInventory();
                }                 
            }
        ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>

        <script>
            // Modal handling
            document.addEventListener('DOMContentLoaded', function() {
                // Show modals if they exist
                const showModal = (modalId) => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        new bootstrap.Modal(modalElement).show();
                    }
                };

                showModal('editProductModal');
                showModal('deleteProductModal');
                showModal('confirmDeleteProductModal');
                showModal('confirmEditModal');
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
                
                // Preserve branch selection
                const branchSelect = document.getElementById('chooseBranch');
                if (branchSelect && branchSelect.value) {
                    urlParams.set('branch', branchSelect.value);
                } else {
                    urlParams.delete('branch');
                }
                
                // Reload the page with new parameters
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }

            // Add confirmation for delete actions
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if(!confirm('Are you sure you want to delete this product?')) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    </body>
</html>