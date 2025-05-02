<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

// Get the logged-in employee's branch
$employeeBranch = '';
$conn = connect();
$employeeQuery = "SELECT BranchCode FROM employee WHERE LoginName = ?";
$stmt = $conn->prepare($employeeQuery);
$stmt->bind_param('s', $_SESSION['login_user']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $employeeBranch = $row['BranchCode'];
}
$stmt->close();

// Get customer details if customer_id is provided
$customerDetails = [];
if (isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];
    $customerQuery = "SELECT * FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $customerDetails = $result->fetch_assoc();
    $stmt->close();
}

// Get branch name
$branchName = '';
if ($employeeBranch) {
    $branchQuery = "SELECT BranchName FROM BranchMaster WHERE BranchCode = ?";
    $stmt = $conn->prepare($branchQuery);
    $stmt->bind_param('s', $employeeBranch);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $branchName = $row['BranchName'];
    }
    $stmt->close();
}

// Get all shapes for filter
$shapes = [];
$shapeQuery = "SELECT * FROM shapeMaster";
$result = $conn->query($shapeQuery);
while ($row = $result->fetch_assoc()) {
    $shapes[] = $row;
}

// Get products based on branch
$products = [];
if ($employeeBranch) {
    $productQuery = "SELECT p.*, pb.Stocks 
                     FROM productMstr p
                     JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID
                     WHERE pb.BranchCode = ? AND p.Avail_FL = 'Available'";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param('s', $employeeBranch);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <title>New Order | Santos Optical</title>
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
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: margin 0.3s ease;
        }
        .order-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .product-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.2);
        }
        .product-card.selected {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
        .customer-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
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
        }
        @media (max-width: 768px) {
            .filter-row .col-md-6, 
            .filter-row .col-md-3 {
                margin-bottom: 10px;
            }
        }
        .back-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart me-2"></i>New Order</h2>
            <a href="customerDetails.php?id=<?= $customerDetails['CustomerID'] ?? '' ?>" class="btn btn-outline-secondary back-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Customer
            </a>
        </div>

        <div class="order-container">
            <?php if (!empty($customerDetails)): ?>
                <div class="customer-info">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Name:</strong> <?= htmlspecialchars($customerDetails['CustomerName']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Contact:</strong> <?= htmlspecialchars($customerDetails['CustomerContact']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Address:</strong> <?= htmlspecialchars($customerDetails['CustomerAddress']) ?></p>
                        </div>
                    </div>
                    <?php if (!empty($customerDetails['Notes'])): ?>
                        <p><strong>Notes:</strong> <?= htmlspecialchars($customerDetails['Notes']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="row filter-row mb-3">
                    <div class="col-md-6">
                        <label for="branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($branchName) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="shapeFilter" class="form-label">Filter by Shape</label>
                        <select class="form-select" id="shapeFilter">
                            <option value="">All Shapes</option>
                            <?php foreach ($shapes as $shape): ?>
                                <option value="<?= $shape['ShapeID'] ?>"><?= $shape['Description'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h4 class="mb-3">Please select a product</h4>

                <div class="row" id="productsContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 product-item" data-shape="<?= $product['ShapeID'] ?>">
                            <div class="product-card" onclick="selectProduct(this, <?= $product['ProductID'] ?>)">
                                <?php if (!empty($product['ProductImage'])): ?>
                                    <img src="<?= $product['ProductImage'] ?>" class="img-fluid mb-2" alt="<?= htmlspecialchars($product['Model']) ?>" style="max-height: 150px;">
                                <?php endif; ?>
                                <h5><?= htmlspecialchars($product['Model']) ?></h5>
                                <p><strong>Category:</strong> <?= htmlspecialchars($product['CategoryType']) ?></p>
                                <p><strong>Brand:</strong> 
                                    <?php 
                                        $brandName = 'Unknown';
                                        $conn = connect();
                                        $brandQuery = "SELECT BrandName FROM brandMaster WHERE BrandID = ?";
                                        $stmt = $conn->prepare($brandQuery);
                                        $stmt->bind_param('i', $product['BrandID']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if ($row = $result->fetch_assoc()) {
                                            $brandName = $row['BrandName'];
                                        }
                                        $stmt->close();
                                        $conn->close();
                                        echo htmlspecialchars($brandName);
                                    ?>
                                </p>
                                <p><strong>Shape:</strong> 
                                    <?php 
                                        $shapeName = 'Unknown';
                                        $conn = connect();
                                        $shapeQuery = "SELECT Description FROM shapeMaster WHERE ShapeID = ?";
                                        $stmt = $conn->prepare($shapeQuery);
                                        $stmt->bind_param('i', $product['ShapeID']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if ($row = $result->fetch_assoc()) {
                                            $shapeName = $row['Description'];
                                        }
                                        $stmt->close();
                                        $conn->close();
                                        echo htmlspecialchars($shapeName);
                                    ?>
                                </p>
                                <p><strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?></p>
                                <p><strong>Stocks:</strong> <?= $product['Stocks'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form id="orderForm" action="processOrder.php" method="post">
                    <input type="hidden" name="customer_id" value="<?= $customerDetails['CustomerID'] ?>">
                    <input type="hidden" name="branch_code" value="<?= $employeeBranch ?>">
                    <input type="hidden" name="selected_product" id="selectedProduct">
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="continueBtn" disabled>
                            <i class="fas fa-arrow-right me-2"></i> Continue to Order Details
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    No customer selected. Please go back and select a customer.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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
            
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('active');
                        body.classList.remove('sidebar-open');
                    }
                });
            });
            
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            });

            // Shape filter functionality
            const shapeFilter = document.getElementById('shapeFilter');
            if (shapeFilter) {
                shapeFilter.addEventListener('change', function() {
                    const selectedShape = this.value;
                    const productItems = document.querySelectorAll('.product-item');
                    
                    productItems.forEach(item => {
                        if (selectedShape === '' || item.getAttribute('data-shape') === selectedShape) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });

        function selectProduct(element, productId) {
            // Remove selected class from all product cards
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Set the selected product ID
            document.getElementById('selectedProduct').value = productId;
            
            // Enable the continue button
            document.getElementById('continueBtn').disabled = false;
        }
    </script>
</body>
</html>