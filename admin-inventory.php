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

function branchView($sort, $order) {
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

if (empty($branchName)) {
branchSelection($sort, $order);                   
} else {
branchView($sort, $order);
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
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <title>Admin | Inventories</title>
        <style>
            body {
                background-color: #f5f7fa;
                padding-top: 60px;
            }
            .sidebar {
                background-color: white;
                height: 100vh;
                padding: 20px 0 70px;
                color: #2c3e50;
                position: fixed;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                z-index: 1000;
                transition: transform 0.3s ease;
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
                width: calc(100% - 250px);
                transition: margin 0.3s ease;
            }
            .table-container, .filter-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
                margin-bottom: 20px;
                overflow-x: auto;
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
                white-space: nowrap;
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
            .mobile-menu-toggle {
                display: none;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1100;
                background: #4e73df;
                color: white;
                border: none;
                border-radius: 5px;
                padding: 8px 12px;
                font-size: 1.2rem;
            }
            
            /* Responsive Styles */
            @media (max-width: 992px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.active {
                    transform: translateX(0);
                }
                .main-content {
                    margin-left: 0;
                    width: 100%;
                }
                .mobile-menu-toggle {
                    display: block;
                }
                body.sidebar-open {
                    overflow: hidden;
                }
                body.sidebar-open::after {
                    content: '';
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 999;
                }
            }
            
            @media (max-width: 768px) {
                .filter-form .col-md-8,
                .filter-form .col-md-4 {
                    flex: 0 0 100%;
                    max-width: 100%;
                }
                .d-flex.justify-content-between.align-items-center.mb-4 {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                }
                .table th, .table td {
                    padding: 8px 5px;
                    font-size: 0.85rem;
                }
                .product-img {
                    width: 40px;
                    height: 40px;
                }
            }
            
            @media (max-width: 576px) {
                .main-content {
                    padding: 15px;
                }
                .table-container, .filter-container {
                    padding: 15px;
                }
                .action-btn {
                    margin: 2px 0;
                    display: block;
                    width: 100%;
                }
                .table-responsive {
                    border: 0;
                }
                .table thead {
                    display: none;
                }
                .table, .table tbody, .table tr, .table td {
                    display: block;
                    width: 100%;
                }
                .table tr {
                    margin-bottom: 15px;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 10px;
                }
                .table td {
                    text-align: right;
                    padding-left: 50%;
                    position: relative;
                    border-bottom: 1px solid #dee2e6;
                }
                .table td::before {
                    content: attr(data-label);
                    position: absolute;
                    left: 10px;
                    width: 45%;
                    padding-right: 10px;
                    font-weight: bold;
                    text-align: left;
                }
                .product-img {
                    margin: 0 auto;
                }
            }
        </style>
    </head>

    <body>
        <button class="mobile-menu-toggle d-lg-none" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
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
                <form method="post" class="row g-3 filter-form" id="branchForm">
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
                    if (empty($branchName)) {
                        branchSelection($sort, $order);                   
                    } else {
                        branchView($sort, $order);
                    }

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

        <script>
            // Mobile sidebar toggle
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const mobileToggle = document.getElementById('mobileMenuToggle');
                const body = document.body;
                
                if (mobileToggle) {
                    mobileToggle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sidebar.classList.toggle('active');
                        body.classList.toggle('sidebar-open');
                    });
                }
                
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 992 && 
                        !sidebar.contains(e.target) && 
                        (!mobileToggle || e.target !== mobileToggle)) {
                        sidebar.classList.remove('active');
                        body.classList.remove('sidebar-open');
                    }
                });
                
                // Add data-labels for mobile table view
                const addDataLabels = () => {
                    if (window.innerWidth <= 576px) {
                        document.querySelectorAll('thead th').forEach((th, index) => {
                            const label = th.textContent.trim();
                            document.querySelectorAll(`tbody td:nth-child(${index + 1})`).forEach(td => {
                                td.setAttribute('data-label', label);
                            });
                        });
                    }
                };
                
                addDataLabels();
                window.addEventListener('resize', addDataLabels);
                
                // Table sorting function
                window.sortTable = function(column) {
                    const urlParams = new URLSearchParams(window.location.search);
                    let currentSort = urlParams.get('sort') || 'ProductID';
                    let currentOrder = urlParams.get('order') || 'ASC';
                    
                    let newOrder = 'ASC';
                    if (currentSort === column) {
                        newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
                    }
                    
                    urlParams.set('sort', column);
                    urlParams.set('order', newOrder);
                    
                    const branchSelect = document.getElementById('chooseBranch');
                    if (branchSelect && branchSelect.value) {
                        urlParams.set('branch', branchSelect.value);
                    } else {
                        urlParams.delete('branch');
                    }
                    
                    window.location.href = window.location.pathname + '?' + urlParams.toString();
                };

                // Modal handling
                const showModal = (modalId) => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        
                        modalElement.addEventListener('hidden.bs.modal', function () {
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
        </script>
    </body>
</html>