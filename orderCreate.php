<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

// Check if customer ID is provided
if (!isset($_GET['customer_id'])) {
    header("Location: customers.php");
    exit();
}

$customer_id = $_GET['customer_id'];

// Fetch customer details
$customer_query = "SELECT * FROM customer WHERE CustomerID = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();

if ($customer_result->num_rows === 0) {
    header("Location: customers.php");
    exit();
}

$customer = $customer_result->fetch_assoc();

// Fetch available products
$products_query = "SELECT * FROM productMstr WHERE Avail_FL = 'Available'";
$products_result = $conn->query($products_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $selected_products = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $branch_code = $_POST['branch_code'];
    
    if (empty($selected_products)) {
        $errorMessage = "Please select at least one product.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order header
            $order_hdr_query = "INSERT INTO Order_hdr (CustomerID, BranchCode, Created_by) 
                                VALUES (?, ?, ?)";
            $stmt = $conn->prepare($order_hdr_query);
            $stmt->bind_param("iis", $customer_id, $branch_code, $_SESSION['EmployeeName']);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order details
            foreach ($selected_products as $index => $product_id) {
                $quantity = $quantities[$index];
                
                // Get product branch info
                $product_branch_query = "SELECT * FROM ProductBranchMaster 
                                       WHERE ProductID = ? AND BranchCode = ?";
                $stmt = $conn->prepare($product_branch_query);
                $stmt->bind_param("ii", $product_id, $branch_code);
                $stmt->execute();
                $pb_result = $stmt->get_result();
                
                if ($pb_result->num_rows > 0) {
                    $pb_row = $pb_result->fetch_assoc();
                    $product_branch_id = $pb_row['ProductBranchID'];
                    $current_stock = $pb_row['Stocks'];
                    
                    if ($quantity > $current_stock) {
                        throw new Exception("Not enough stock for product ID: $product_id");
                    }
                    
                    // Insert order detail
                    $order_detail_query = "INSERT INTO orderDetails 
                                          (OrderHdr_id, ProductBranchID, Quantity, ActivityCode, Status) 
                                          VALUES (?, ?, ?, 2, 'Pending')";
                    $stmt = $conn->prepare($order_detail_query);
                    $stmt->bind_param("iii", $order_id, $product_branch_id, $quantity);
                    $stmt->execute();
                    
                    // Update stock
                    $update_stock_query = "UPDATE ProductBranchMaster 
                                          SET Stocks = Stocks - ? 
                                          WHERE ProductBranchID = ?";
                    $stmt = $conn->prepare($update_stock_query);
                    $stmt->bind_param("ii", $quantity, $product_branch_id);
                    $stmt->execute();
                } else {
                    throw new Exception("Product not available at selected branch");
                }
            }
            
            // Log the activity
            $activity = new ActivityTracker();
            $activity->logActivity(
                $_SESSION['EmployeeID'],
                $order_id,
                'order',
                3, // Added
                "Created new order for customer ID: $customer_id"
            );
            
            // Commit transaction
            $conn->commit();
            
            $successMessage = "Order created successfully!";
            // header("Location: order.php?order_id=$order_id");
            // exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = "Error creating order: " . $e->getMessage();
        }
    }
}

// Fetch branches
$branches_query = "SELECT * FROM BranchMaster";
$branches_result = $conn->query($branches_query);

// Function to get brand name
function getBrandName($brand_id) {
    global $conn;
    $query = "SELECT BrandName FROM brandMaster WHERE BrandID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['BrandName'];
}

// Function to get product stock at branch
function getProductStock($product_id, $branch_code) {
    global $conn;
    $query = "SELECT Stocks FROM ProductBranchMaster 
              WHERE ProductID = ? AND BranchCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $product_id, $branch_code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['Stocks'];
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Order | Santos Optical</title>
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
        .product-card {
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .product-img {
            height: 180px;
            object-fit: contain;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .quantity-input {
            display: none;
            margin-top: 10px;
        }
        .product-card.selected .quantity-input {
            display: block;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-cart-plus me-2"></i> New Order</h1>
                <a class="btn btn-outline-secondary" href="customers.php" role="button" data-bs-toggle="modal" 
                data-bs-target="#cancelModal">
                    <i class="fas fa-arrow-left me-2"></i> Back to Customers
                </a>            
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong><?php echo $errorMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong><?php echo $successMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?= htmlspecialchars($customer['CustomerName']) ?></p>
                            <p><strong>Contact:</strong> <?= htmlspecialchars($customer['CustomerContact']) ?></p>
                            <p><strong>Address:</strong> <?= htmlspecialchars($customer['CustomerAddress']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" id="orderCreate">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Branch</label>
                        <select class="form-select form-control-lg" id="branch_code" name="branch_code" required>
                            <option value="">-- Select Branch --</option>
                            <?php while ($branch = $branches_result->fetch_assoc()): ?>
                                <option value="<?= $branch['BranchCode'] ?>">
                                    <?= htmlspecialchars($branch['BranchName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Available Products</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($products_result->num_rows > 0): ?>
                                    <div class="row row-cols-1 row-cols-md-3 g-4" id="products-container">
                                        <?php while ($product = $products_result->fetch_assoc()): ?>
                                            <?php 
                                            $brand_name = getBrandName($product['BrandID']);
                                            ?>
                                            <div class="col">
                                                <div class="card product-card h-100" onclick="toggleSelection(this, <?= $product['ProductID'] ?>)">
                                                    <img src="<?= htmlspecialchars($product['ProductImage']) ?>" 
                                                         class="product-img" 
                                                         alt="<?= htmlspecialchars($product['Model']) ?>">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?= htmlspecialchars($product['Model']) ?></h5>
                                                        <p class="card-text">
                                                            <strong>Category:</strong> <?= htmlspecialchars($product['CategoryType']) ?><br>
                                                            <strong>Brand:</strong> <?= htmlspecialchars($brand_name) ?><br>
                                                            <strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?><br>
                                                            <span class="stock-info" id="stock-<?= $product['ProductID'] ?>">
                                                                <strong>Stocks:</strong> Select branch to view availability
                                                            </span>
                                                        </p>
                                                        <div class="quantity-input">
                                                            <label class="form-label">Quantity</label>
                                                            <input type="number" 
                                                                   class="form-control" 
                                                                   name="quantities[]" 
                                                                   min="1" 
                                                                   value="1"
                                                                   data-product-id="<?= $product['ProductID'] ?>">
                                                            <input type="hidden" name="products[]" value="<?= $product['ProductID'] ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">No available products found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5">                    
                    <button type="submit" class="btn btn-primary btn-action" name="create_order">
                        <i class="fas fa-save me-2"></i> Create Order
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to return to customer list? Any unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="customers.php" class="btn btn-danger">Yes, Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSelection(card, productId) {
            card.classList.toggle('selected');
            const quantityInput = card.querySelector('.quantity-input');
            const productInput = card.querySelector('input[name="products[]"]');
            
            if (card.classList.contains('selected')) {
                quantityInput.style.display = 'block';
                productInput.disabled = false;
                
                // Update max quantity based on selected branch
                const branchSelect = document.getElementById('branch_code');
                if (branchSelect.value) {
                    updateMaxQuantity(productId, branchSelect.value);
                }
            } else {
                quantityInput.style.display = 'none';
                productInput.disabled = true;
            }
        }

        // Update stock info when branch is selected
        document.getElementById('branch_code').addEventListener('change', function() {
            const branchCode = this.value;
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productId = card.querySelector('input[name="products[]"]').value;
                const stockElement = document.getElementById(`stock-${productId}`);
                
                if (branchCode) {
                    // Fetch stock via AJAX
                    fetch(`get_stock.php?product_id=${productId}&branch_code=${branchCode}`)
                        .then(response => response.json())
                        .then(data => {
                            stockElement.innerHTML = `<strong>Stocks:</strong> ${data.stock}`;
                            
                            // Update max quantity for selected products
                            if (card.classList.contains('selected')) {
                                const quantityInput = card.querySelector('input[name="quantities[]"]');
                                quantityInput.max = data.stock;
                                if (quantityInput.value > data.stock) {
                                    quantityInput.value = data.stock;
                                }
                            }
                        });
                } else {
                    stockElement.innerHTML = '<strong>Stocks:</strong> Select branch to view availability';
                }
            });
        });

        function updateMaxQuantity(productId, branchCode) {
            fetch(`get_stock.php?product_id=${productId}&branch_code=${branchCode}`)
                .then(response => response.json())
                .then(data => {
                    const quantityInput = document.querySelector(`input[name="quantities[]"][data-product-id="${productId}"]`);
                    if (quantityInput) {
                        quantityInput.max = data.stock;
                        if (quantityInput.value > data.stock) {
                            quantityInput.value = data.stock;
                        }
                    }
                });
        }
    </script>
</body>
</html>