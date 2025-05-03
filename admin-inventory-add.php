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
        
        @media (max-width: 991.98px) {
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
        
        /* Main content adjustments */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        
        /* Form container */
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        
        /* Form elements */
        .form-label {
            font-weight: 500;
        }
        
        .btn-action {
            min-width: 120px;
        }
        
        /* Spinner for input number */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;            
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Product image preview */
        .product-img-preview {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1050;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 991.98px) {
            .mobile-menu-toggle {
                display: block;
            }
        }
        
        /* Responsive form adjustments */
        @media (max-width: 767.98px) {
            .form-container {
                padding: 20px 15px;
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
            
            .product-img-container {
                margin-bottom: 20px;
            }

            .branch-checkbox {
                transform: scale(1.2);
                margin-right: 10px;
            }

            .quantity-input {
                margin-left: 28px;
                max-width: 200px;
            }

            .form-check-label {
                font-size: 1.1rem;
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
                    <div class="col-12">
                        <label class="form-label">Select Branches</label>
                        <div class="border p-3 rounded" id="branchCheckboxContainer">
                            <?php displayBranchesWithCheckboxes(); ?>
                        </div>
                    </div>
                </div>

                <div class="row mb-4" id="branchQuantitiesContainer">
                    <!-- Dynamic quantity fields will be added here by JavaScript -->
                </div>
             
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" name="productName" id="productName" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productBrand" class="form-label">Brand</label>
                        <select name="productBrand" id="productBrand" class="form-select form-control-lg" required>
                            <?php getBrands(); ?>
                        </select>
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
                        <img src="Images/default-product.png" alt="Product Image" id="imagePreview" class="product-img-preview" style="height: 600px; width: 600px;">
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
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('branchCheckboxContainer');
        
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('branch-checkbox')) {
                const checkbox = e.target;
                const quantityDiv = checkbox.parentElement.querySelector('.quantity-input');
                
                if (checkbox.checked && !quantityDiv) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'quantity-input mt-2';
                    wrapper.innerHTML = `
                        <input type="number" name="qtys[${checkbox.value}]" 
                            class="form-control form-control-sm" 
                            placeholder="Quantity for ${checkbox.nextElementSibling.textContent}"
                            min="0" required>
                    `;
                    checkbox.parentElement.appendChild(wrapper);
                } else if (!checkbox.checked && quantityDiv) {
                    quantityDiv.remove();
                }
            }
        });
    });
    </script>
</body>
</html>