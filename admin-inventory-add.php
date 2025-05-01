<?php
include 'ActivityTracker.php';
include 'admin-inventory-funcs.php'; // Include the functions file
include 'loginChecker.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Inventory | Santos Optical</title>
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
        }
        
        /* Sidebar adjustments for mobile */
        .sidebar {
            background-color: white;
            height: 100vh;
            padding: 20px 0;
            color: #2c3e50;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        .mobile-menu-toggle {
            display: block !important;
        }
        
        /* Adjust form layout */
        .form-container {
            padding: 20px;
        }
        
        .product-img-container {
            margin-bottom: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .form-container {
            padding: 15px;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .d-flex.justify-content-between h1 {
            margin-bottom: 15px;
        }
        
        .d-flex.flex-row-reverse {
            flex-direction: column !important;
            gap: 10px !important;
        }
        
        .btn-action {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .form-control-lg, .form-select-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        
        .product-img-preview {
            max-height: 150px;
        }
    }
</style>
</head>
<body>
    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <?php include "sidebar.php"?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-boxes me-2"></i> Add New Inventory</h1>
                <a class="btn btn-outline-secondary" href="admin-inventory.php" role="button" data-bs-toggle="modal" 
                data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>            
            </div>
            
            <form method="post" enctype="multipart/form-data" id="addForm">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productBranch" class="form-label">Branch</label>
                        <select name="productBranch" id="productBranch" class="form-select form-control-lg" required>
                            <?php getBranch(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" name="productName" id="productName" class="form-control form-control-lg" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productBrand" class="form-label">Brand</label>
                        <select name="productBrand" id="productBrand" class="form-select form-control-lg" required>
                            <?php getBrands(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="productQty" class="form-label">Quantity</label>
                        <input type="number" name="productQty" id="productQty" class="form-control form-control-lg" min="0" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productCategory" class="form-label">Category</label>
                        <select name="productCategory" id="productCategory" class="form-select form-control-lg" required>
                            <?php getCategory(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="productShape" class="form-label">Shape</label>
                        <select name="productShape" id="productShape" class="form-select form-control-lg" required>
                            <?php getShapes(); ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productMaterial" class="form-label">Material</label>
                        <input type="text" class="form-control form-control-lg" id="productMaterial" name="productMaterial" required>
                    </div>
                    <div class="col-md-6">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="text" class="form-control form-control-lg" id="productPrice" name="productPrice" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 text-center mb-4 mb-md-0 product-img-container">
                        <img src="Images/default-product.png" alt="Product Image" id="imagePreview" class="product-img-preview">
                        <div>
                            <label for="productImg" class="btn btn-success">
                                <input type="file" name="productImg" id="productImg" accept="image/png, image/jpeg" onchange="productImagePreview(this)" style="display:none;" required>
                                <i class="fas fa-camera me-2"></i> Add Product Image
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="d-flex flex-row-reverse justify-content-end gap-3 w-100">                    
                            <button type="reset" class="btn btn-danger btn-action" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary btn-action" name="addProduct" value="addProduct">
                                <i class="fas fa-save me-2"></i> Add Product
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
            addProduct();
        }
    ?>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label='Close'></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to return to inventory list? Any unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <a href="admin-inventory.php" class="btn btn-danger">Yes, Cancel</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productImagePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetForm() {
            setTimeout(() => {
                document.getElementById('addForm').reset();
                document.getElementById('productImg').value = '';
                document.getElementById('imagePreview').src = "Images/default-product.png";
            }, 10);
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            if (successModal) {
                const myModal = new bootstrap.Modal(successModal);
                myModal.show();
            }
        });
        function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const body = document.body;
        sidebar.classList.toggle('active');
        body.classList.toggle('sidebar-open');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 992 && 
            !sidebar.contains(e.target) && 
            e.target !== menuToggle && 
            !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });

    // Auto-close sidebar when resizing to larger screens
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            document.querySelector('.sidebar').classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
    </script>
</body>
</html>