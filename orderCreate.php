<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        $conn = connect();
        $customerId = $_POST['customer_id'];
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $branchCode = $_POST['branch_code'];
        $createdBy = $_SESSION['login_user'];
        
        // Validate inputs
        if (empty($customerId) || empty($productId) || empty($quantity) || empty($branchCode)) {
            $errorMessage = "All fields are required!";
        } else {
            // Generate a new Order_hdr ID
            $orderId = generate_Order_hdr_ID();
            
            // Create order header
            $orderQuery = "INSERT INTO Order_hdr (Orderhdr_id, CustomerID, BranchCode, Created_by) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param('iiss', $orderId, $customerId, $branchCode, $createdBy);
            
            if (!$stmt->execute()) {
                $errorMessage = "Error creating order: " . $conn->error;
            } else {
                // Generate order detail ID
                $orderDetailId = generate_OrderDtlID();
                
                // Get ProductBranchID
                $productBranchQuery = "SELECT ProductBranchID FROM ProductBranchMaster WHERE ProductID = ? AND BranchCode = ? LIMIT 1";
                $stmt2 = $conn->prepare($productBranchQuery);
                $stmt2->bind_param('is', $productId, $branchCode);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $productBranch = $result->fetch_assoc();
                $stmt2->close();
                
                if ($productBranch) {
                    $productBranchId = $productBranch['ProductBranchID'];
                    
                    // Create order detail
                    $detailQuery = "INSERT INTO orderDetails (OrderDtlID, OrderHdr_id, ProductBranchID, Quantity, ActivityCode, Status) 
                                    VALUES (?, ?, ?, ?, 2, 'Pending')";
                    $stmt3 = $conn->prepare($detailQuery);
                    $stmt3->bind_param('iiii', $orderDetailId, $orderId, $productBranchId, $quantity);
                    
                    if (!$stmt3->execute()) {
                        $errorMessage = "Error creating order details: " . $conn->error;
                    } else {
                        // Update stock
                        $updateQuery = "UPDATE ProductBranchMaster SET Stocks = Stocks - ? WHERE ProductID = ? AND BranchCode = ?";
                        $stmt4 = $conn->prepare($updateQuery);
                        $stmt4->bind_param('iis', $quantity, $productId, $branchCode);
                        
                        if (!$stmt4->execute()) {
                            $errorMessage = "Error updating stock: " . $conn->error;
                        } else {
                            // Log activity
                            if (function_exists('logActivity')) {
                                logActivity($_SESSION['employee_id'], $orderId, 'order', 4, "Created new order #$orderId");
                            }
                            
                            // Redirect to order details
                            header("Location: orderDetails.php?id=$orderId");
                            exit();
                        }
                        $stmt4->close();
                    }
                    $stmt3->close();
                } else {
                    $errorMessage = "Product not found in selected branch inventory!";
                }
            }
            $stmt->close();
            $conn->close();
        }
    }
}

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

// Get all branches
$branches = [];
$branchQuery = "SELECT BranchCode, BranchName FROM BranchMaster";
$result = $conn->query($branchQuery);
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

// Get all shapes for filter
$shapes = [];
$shapeQuery = "SELECT * FROM shapeMaster";
$result = $conn->query($shapeQuery);
while ($row = $result->fetch_assoc()) {
    $shapes[] = $row;
}

// Get products based on selected branch (default to employee's branch)
$selectedBranch = $employeeBranch;
if (isset($_POST['branch_code'])) {
    $selectedBranch = $_POST['branch_code'];
}

$products = [];
if ($selectedBranch) {
    $productQuery = "SELECT p.*, pb.Stocks 
                     FROM productMstr p
                     JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID
                     WHERE pb.BranchCode = ? AND p.Avail_FL = 'Available'";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param('s', $selectedBranch);
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
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <title>New Order | Santos Optical</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .order-container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .customer-info {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
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
        .product-img {
            max-height: 120px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .btn-action {
            min-width: 120px;
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="order-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-cart me-2"></i> New Order</h2>
                <a href="customerDetails.php?id=<?= $customerDetails['CustomerID'] ?? '' ?>" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2"></i> Back to Customer
                </a>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong><?php echo $errorMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($customerDetails)): ?>
                <div class="customer-info">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4><?= htmlspecialchars($customerDetails['CustomerName']) ?></h4>
                            <?php if (!empty($customerDetails['Notes'])): ?>
                                <p class="mb-0"><strong>Notes:</strong> <?= htmlspecialchars($customerDetails['Notes']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($customerDetails['CustomerContact']) ?></p>
                            <p class="mb-0"><strong>Address:</strong> <?= htmlspecialchars($customerDetails['CustomerAddress']) ?></p>
                        </div>
                    </div>
                </div>

                <form id="branchForm" method="post">
                    <input type="hidden" name="customer_id" value="<?= $customerDetails['CustomerID'] ?>">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch_code" class="form-label"><strong>Branch:</strong></label>
                                <select class="form-select" id="branch_code" name="branch_code" onchange="this.form.submit()">
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= $branch['BranchCode'] ?>" <?= ($branch['BranchCode'] == $selectedBranch) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($branch['BranchName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shapeFilter" class="form-label"><strong>Filter by Shape:</strong></label>
                                <select class="form-select" id="shapeFilter">
                                    <option value="">All Shapes</option>
                                    <?php foreach ($shapes as $shape): ?>
                                        <option value="<?= $shape['ShapeID'] ?>"><?= $shape['Description'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <h4 class="mb-3">Please select a product</h4>

                <?php if (!empty($products)): ?>
                    <div class="row" id="productsContainer">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-4 product-item" data-shape="<?= $product['ShapeID'] ?>">
                                <div class="product-card" onclick="selectProduct(this, <?= $product['ProductID'] ?>)">
                                    <?php if (!empty($product['ProductImage'])): ?>
                                        <img src="<?= $product['ProductImage'] ?>" class="img-fluid product-img" alt="<?= htmlspecialchars($product['Model']) ?>">
                                    <?php else: ?>
                                        <div class="text-center py-3 bg-light">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5><?= htmlspecialchars($product['Model']) ?></h5>
                                    <p class="mb-1"><small class="text-muted"><?= htmlspecialchars($product['CategoryType']) ?></small></p>
                                    <p class="mb-1"><strong>Brand:</strong> 
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
                                    <p class="mb-1"><strong>Shape:</strong> 
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
                                    <p class="mb-1"><strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?></p>
                                    <p class="mb-0"><strong>Stocks:</strong> <?= $product['Stocks'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form id="orderForm" method="post">
                        <input type="hidden" name="customer_id" value="<?= $customerDetails['CustomerID'] ?>">
                        <input type="hidden" name="branch_code" value="<?= $selectedBranch ?>">
                        <input type="hidden" name="product_id" id="selectedProduct">
                        
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <label for="quantity" class="form-label"><strong>Quantity:</strong></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <button type="submit" class="btn btn-primary btn-action" id="continueBtn" name="create_order" disabled>
                                <i class="fas fa-check-circle me-2"></i> Create Order
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No products available in this branch. Please check inventory.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-danger">
                    No customer selected. Please go back and select a customer.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Navigation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel in making an order ?.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="order.php?id=<?= $customerDetails['CustomerID'] ?? '' ?>" class="btn btn-danger">Yes, Go Back</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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