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
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
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
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
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
        .product-img-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
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
            
            <form method="post" enctype="multipart/form-data">
                <div class="row mb-4">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
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
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <label for="productMaterial" class="form-label">Material</label>
                        <input type="text" class="form-control form-control-lg" id="productMaterial" name="productMaterial" required>
                    </div>
                    <div class="col-md-6">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="text" class="form-control form-control-lg" id="productPrice" name="productPrice" required>
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="Images/default-product.png" alt="Product Image" class="product-img-preview">
                        <div>
                            <label for="productImg" class="btn btn-success">
                                <input type="file" name="productImg" id="productImg" accept="image/png, image/jpeg" onchange="productImagePreview(this)" style="display:none;" required>
                                <i class="fas fa-camera me-2"></i> Add Product Image
                            </label>
                        </div>
                    </div>
                </div>
                
                <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
                        addProduct();
                    }
                ?>
                
                <div class="d-flex justify-content-end gap-3 mt-5">                    
                    <button type="reset" class="btn btn-danger btn-action">
                        <i class="fas fa-undo me-2"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary btn-action" name="addProduct" value="addProduct">
                        <i class="fas fa-save me-2"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="admin-inventory.php" class="btn btn-danger">Yes, Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productImagePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector(".product-img-preview").src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }

            var myModal = new bootstrap.Modal(document.getElementById("successModal"));
            myModal.show();
        }
    </script>
</body>
</html>