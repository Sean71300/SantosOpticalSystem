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
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <title>Admin | Inventories</title>
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
        
        <?php include "sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h1><i class="fas fa-boxes me-2"></i> Inventory Management</h1>
                <a class="btn btn-primary" href="admin-inventory-add.php" role="button">
                    <i class="fas fa-plus me-2"></i> Add Product
                </a>            
            </div>
        
            <div class="filter-container">
                <form method="post" class="row g-3" id="branchForm">
                    <div class="col-md-8 col-sm-12">
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
                    <div class="col-md-4 col-sm-12 d-flex align-items-end">
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
                    function branchSelection($sort, $order){
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
                                        <th>Image</th>
                                        <th class="sortable '.($sort == 'TotalCount' ? 'active' : '').'" onclick="sortTable(\'TotalCount\')">
                                            Total
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'TotalCount' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        getInventory($sort, $order);
                        
                        echo '</tbody></table>';
                    }
                    
                    function branchView($sort, $order) {
                        // Specific branch view
                        echo '<table class="table table-hover text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable '.($sort == 'ProductID' ? 'active' : '').'" onclick="sortTable(\'ProductID\')">
                                            ID
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
                                        <th>Image</th>
                                        <th class="sortable '.($sort == 'Count' ? 'active' : '').'" onclick="sortTable(\'Count\')">
                                            Qty
                                            <span class="sort-icon">
                                                <i class="fas fa-sort-'.($sort == 'Count' ? (strtolower($order) == 'asc' ? 'up' : 'down') : 'up').'"></i>
                                            </span>
                                        </th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        getInventory($sort, $order);
                        
                        echo '</tbody></table>';
                    }

                    if (empty($branchName)) {
                        branchSelection($sort, $order);                   
                    } else {
                        branchView($sort, $order);
                    }
                    ?>

                    <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['editProductBtn'])) {
                                editProduct();
                            }
                            elseif (isset($_POST['saveProductBtn'])) {
                                confirmEditProduct(); 
                            }
                            elseif (isset($_POST['deleteProductBtn'])) {
                                confirmDeleteProduct();
                            }
                            elseif (isset($_POST['confirmDeleteBtn'])) {
                                deleteProduct();
                            }
                        }
                    ?>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>

        <script>
            // Mobile menu toggle
            document.getElementById('menuToggle').addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            });

            // Modal handling
            document.addEventListener('DOMContentLoaded', function() {
                // Show modals if they exist
                const showModal = (modalId) => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        
                        // Add event listener for when modal is hidden
                        modalElement.addEventListener('hidden.bs.modal', function () {
                            // If this is a success modal, reload the page
                            if (modalId === 'editProductModal' || modalId === 'deleteProductModal') {
                                const urlParams = new URLSearchParams(window.location.search);
                                window.location.href = window.location.pathname + '?' + urlParams.toString();
                            }
                        });
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
            
            // Auto-close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const sidebar = document.querySelector('.sidebar');
                const menuToggle = document.getElementById('menuToggle');
                
                if (window.innerWidth <= 768 && sidebar.classList.contains('active') && 
                    !sidebar.contains(e.target) && e.target !== menuToggle) {
                    sidebar.classList.remove('active');
                }
            });
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById("deleteErrorModal"));
                myModal.show();
            });
        </script>
    </body>
</html>