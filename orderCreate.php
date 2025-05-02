<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

$orderSuccess = false;
$orderDetails = [];
$errorMessage = '';

// Process the order if confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $conn = connect();
    $customerId = $_POST['customer_id'];
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $branchCode = $_POST['branch_code'];
    $createdBy = $_SESSION['full_name'];
    $employeeId = $_SESSION['id'] ?? null;
    
    if (empty($customerId) || empty($productId) || empty($quantity) || empty($branchCode)) {
        $errorMessage = "All fields are required!";
    } else {
        $conn->begin_transaction();
        
        try {
            $orderId = generate_Order_hdr_ID();
            
            $orderQuery = "INSERT INTO Order_hdr (Orderhdr_id, CustomerID, BranchCode, Created_by) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($orderQuery);
            $stmt->bind_param('iiss', $orderId, $customerId, $branchCode, $createdBy);
            $stmt->execute();
            $stmt->close();
            
            $orderDetailId = generate_OrderDtlID();
            
            $productBranchQuery = "SELECT pb.ProductBranchID, p.Model, p.Price, p.CategoryType 
                                  FROM ProductBranchMaster pb
                                  JOIN productMstr p ON pb.ProductID = p.ProductID
                                  WHERE pb.ProductID = ? AND pb.BranchCode = ? LIMIT 1";
            $stmt = $conn->prepare($productBranchQuery);
            $stmt->bind_param('is', $productId, $branchCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $productData = $result->fetch_assoc();
            $stmt->close();
            
            if ($productData) {
                $productBranchId = $productData['ProductBranchID'];
                
                $detailQuery = "INSERT INTO orderDetails (OrderDtlID, OrderHdr_id, ProductBranchID, Quantity, ActivityCode, Status) 
                                VALUES (?, ?, ?, ?, 2, 'Pending')";
                $stmt = $conn->prepare($detailQuery);
                $stmt->bind_param('iiii', $orderDetailId, $orderId, $productBranchId, $quantity);
                $stmt->execute();
                $stmt->close();
                
                $updateQuery = "UPDATE ProductBranchMaster SET Stocks = Stocks - ? WHERE ProductID = ? AND BranchCode = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('iis', $quantity, $productId, $branchCode);
                $stmt->execute();
                $stmt->close();
                
                $customerQuery = "SELECT CustomerName FROM customer WHERE CustomerID = ?";
                $stmt = $conn->prepare($customerQuery);
                $stmt->bind_param('i', $customerId);
                $stmt->execute();
                $customerResult = $stmt->get_result();
                $customerData = $customerResult->fetch_assoc();
                $stmt->close();
                
                $branchQuery = "SELECT BranchName FROM BranchMaster WHERE BranchCode = ?";
                $stmt = $conn->prepare($branchQuery);
                $stmt->bind_param('s', $branchCode);
                $stmt->execute();
                $branchResult = $stmt->get_result();
                $branchData = $branchResult->fetch_assoc();
                $stmt->close();

                $price = (float)str_replace(['₱', ','], '', $productData['Price']);
                $total = $price * $quantity;
                
                $orderDetails = [
                    'order_id' => $orderId,
                    'customer_name' => $customerData['CustomerName'] ?? '',
                    'branch_name' => $branchData['BranchName'] ?? '',
                    'product_model' => $productData['Model'] ?? '',
                    'product_category' => $productData['CategoryType'] ?? '',
                    'quantity' => $quantity,
                    'price' => '₱' . number_format($price, 2),
                    'total' => '₱' . number_format($total, 2),
                    'status' => 'Pending',
                    'date_created' => date('Y-m-d H:i:s')
                ];
                
                $logId = generate_LogsID();
                $logDescription = "#$orderId for customer " . $customerData['CustomerName'];
                $logQuery = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) 
                            VALUES (?, ?, ?, 'order', 3, ?)";
                $stmt = $conn->prepare($logQuery);
                $stmt->bind_param('iiis', $logId, $employeeId, $orderId, $logDescription);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $orderSuccess = true;
                
            } else {
                throw new Exception("Product not found in selected branch inventory!");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = "Error creating order: " . $e->getMessage();
        }
        
        $conn->close();
    }
}

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

$branches = [];
$branchQuery = "SELECT BranchCode, BranchName FROM BranchMaster";
$result = $conn->query($branchQuery);
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

$shapes = [];
$shapeQuery = "SELECT * FROM shapeMaster";
$result = $conn->query($shapeQuery);
while ($row = $result->fetch_assoc()) {
    $shapes[] = $row;
}

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
                <a href="order.php?id=<?= $customerDetails['CustomerID'] ?? '' ?>" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal">
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

                    <form id="orderForm">
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
                            <button type="button" class="btn btn-primary btn-action" id="continueBtn" disabled onclick="prepareOrder()">
                                <i class="fas fa-check-circle me-2"></i> Create Order
                            </button>
                        </div>
                    </form>

                    <!-- Hidden form for actual order submission -->
                    <form id="hiddenOrderForm" method="post" style="display: none;">
                        <input type="hidden" name="confirm_order" value="1">
                        <input type="hidden" name="customer_id" id="hiddenCustomerId">
                        <input type="hidden" name="product_id" id="hiddenProductId">
                        <input type="hidden" name="quantity" id="hiddenQuantity">
                        <input type="hidden" name="branch_code" id="hiddenBranchCode">
                    </form>

                    <!-- Order Confirmation Modal -->
                    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="confirmOrderModalLabel">Confirm Order</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to create this order?</p>
                                    <div id="orderSummary">
                                        <!-- Order summary will be inserted here by JavaScript -->
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="submitOrder()">Confirm Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
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

    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Order Created Successfully</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($orderSuccess && !empty($orderDetails)): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>Order Summary</h5>
                                <p><strong>Order ID:</strong> <?= $orderDetails['order_id'] ?></p>
                                <p><strong>Customer:</strong> <?= $orderDetails['customer_name'] ?></p>
                                <p><strong>Branch:</strong> <?= $orderDetails['branch_name'] ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-warning"><?= $orderDetails['status'] ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date Created:</strong> <?= $orderDetails['date_created'] ?></p>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= $orderDetails['product_model'] ?></td>
                                        <td><?= $orderDetails['product_category'] ?></td>
                                        <td><?= $orderDetails['quantity'] ?></td>
                                        <td><?= $orderDetails['price'] ?></td>
                                        <td><?= $orderDetails['total'] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Confirm Navigation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want cancel this order ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="order.php?id=<?= $customerDetails['CustomerID'] ?? '' ?>" class="btn btn-danger">Yes, Go Back</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        <?php if ($orderSuccess): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        <?php endif; ?>

        function prepareOrder() {
            const productId = document.getElementById('selectedProduct').value;
            const quantity = document.getElementById('quantity').value;
            const customerId = document.querySelector('input[name="customer_id"]').value;
            const branchCode = document.querySelector('input[name="branch_code"]').value;
            
            if (!productId) {
                alert('Please select a product first');
                return;
            }
            
            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity');
                return;
            }
            
            // Get the selected product card
            const productCard = document.querySelector('.product-card.selected');
            if (!productCard) {
                alert('Please select a product first');
                return;
            }
            
            // Get product details safely
            const productName = productCard.querySelector('h5')?.textContent || 'Unknown Product';
            const priceElement = productCard.querySelector('p:nth-of-type(4)');
            const productPrice = priceElement?.textContent.replace('Price: ', '') || '₱0.00';
            const categoryElement = productCard.querySelector('p:nth-of-type(2) small');
            const productCategory = categoryElement?.textContent || 'Unknown Category';
            
            // Get customer name safely
            const customerNameElement = document.querySelector('.customer-info h4');
            const customerName = customerNameElement?.textContent || 'Unknown Customer';
            
            // Calculate total
            const priceValue = parseFloat(productPrice.replace('₱', '').replace(',', '')) || 0;
            const total = (priceValue * quantity).toFixed(2);
            
            // Update confirmation modal content
            const orderSummary = document.getElementById('orderSummary');
            orderSummary.innerHTML = `
                <div class="mb-3">
                    <p><strong>Customer:</strong> ${customerName}</p>
                    <p><strong>Product:</strong> ${productName}</p>
                    <p><strong>Category:</strong> ${productCategory}</p>
                    <p><strong>Quantity:</strong> ${quantity}</p>
                    <p><strong>Unit Price:</strong> ${productPrice}</p>
                    <p><strong>Total:</strong> ₱${total}</p>
                </div>
            `;
            
            // Set values for hidden form
            document.getElementById('hiddenCustomerId').value = customerId;
            document.getElementById('hiddenProductId').value = productId;
            document.getElementById('hiddenQuantity').value = quantity;
            document.getElementById('hiddenBranchCode').value = branchCode;
            
            // Show confirmation modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmOrderModal'));
            confirmModal.show();
        }
        
        function submitOrder() {
            document.getElementById('hiddenOrderForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
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
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            element.classList.add('selected');
            document.getElementById('selectedProduct').value = productId;
            document.getElementById('continueBtn').disabled = false;
        }
    </script>
</body>
</html>