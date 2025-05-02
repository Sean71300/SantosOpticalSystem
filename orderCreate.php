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

// Fetch customer details with notes
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
            $unavailable_products = [];
            
            // First pass: Check all products for availability
            foreach ($selected_products as $index => $product_id) {
                $quantity = $quantities[$index];
                
                // Check product availability at branch
                $check_query = "SELECT Stocks FROM ProductBranchMaster 
                               WHERE ProductID = ? AND BranchCode = ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("ii", $product_id, $branch_code);
                $stmt->execute();
                $check_result = $stmt->get_result();
                
                if ($check_result->num_rows === 0) {
                    $unavailable_products[] = $product_id;
                    continue;
                }
                
                $stock_row = $check_result->fetch_assoc();
                if ($quantity > $stock_row['Stocks']) {
                    $unavailable_products[] = $product_id;
                }
            }
            
            if (!empty($unavailable_products)) {
                $product_ids = implode(", ", $unavailable_products);
                throw new Exception("The following products are not available at selected branch: $product_ids");
            }
            
            // Second pass: Process the order if all products are available
            $order_hdr_query = "INSERT INTO Order_hdr (CustomerID, BranchCode, Created_by) 
                                VALUES (?, ?, ?)";
            $stmt = $conn->prepare($order_hdr_query);
            $stmt->bind_param("iis", $customer_id, $branch_code, $_SESSION['EmployeeName']);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            foreach ($selected_products as $index => $product_id) {
                $quantity = $quantities[$index];
                
                $product_branch_query = "SELECT ProductBranchID FROM ProductBranchMaster 
                                       WHERE ProductID = ? AND BranchCode = ?";
                $stmt = $conn->prepare($product_branch_query);
                $stmt->bind_param("ii", $product_id, $branch_code);
                $stmt->execute();
                $pb_result = $stmt->get_result();
                $pb_row = $pb_result->fetch_assoc();
                $product_branch_id = $pb_row['ProductBranchID'];
                
                $order_detail_query = "INSERT INTO orderDetails 
                                      (OrderHdr_id, ProductBranchID, Quantity, ActivityCode, Status) 
                                      VALUES (?, ?, ?, 2, 'Pending')";
                $stmt = $conn->prepare($order_detail_query);
                $stmt->bind_param("iii", $order_id, $product_branch_id, $quantity);
                $stmt->execute();
                
                $update_stock_query = "UPDATE ProductBranchMaster 
                                      SET Stocks = Stocks - ? 
                                      WHERE ProductBranchID = ?";
                $stmt = $conn->prepare($update_stock_query);
                $stmt->bind_param("ii", $quantity, $product_branch_id);
                $stmt->execute();
            }
            
            $activity = new ActivityTracker();
            $activity->logActivity(
                $_SESSION['EmployeeID'],
                $order_id,
                'order',
                3,
                "Created new order for customer ID: $customer_id"
            );
            
            $conn->commit();
            $successMessage = "Order created successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = $e->getMessage();
        }
    }
}

// Fetch branches
$branches_query = "SELECT * FROM BranchMaster";
$branches_result = $conn->query($branches_query);

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
    <style>
        body {
            background-color: #f5f7fa;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            height: calc(100vh - 100px);
            display: flex;
            flex-direction: column;
        }
        .branch-select-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 20px;
        }
        .branch-select-col {
            flex-grow: 1;
        }
        .products-container {
            flex: 1;
            overflow-y: auto;
            margin-top: 15px;
        }
        .product-card {
            height: 100%;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }
        .product-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .product-img {
            height: 150px;
            object-fit: contain;
            padding: 15px;
        }
        .quantity-control {
            position: absolute;
            bottom: 10px;
            right: 10px;
            display: none;
        }
        .product-card.selected .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .customer-notes {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .branch-select-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"?>

    <div class="main-content">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-cart-plus me-2"></i> New Order</h1>
                <a href="customers.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $errorMessage ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $successMessage ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Name:</strong> <?= htmlspecialchars($customer['CustomerName']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Contact:</strong> <?= htmlspecialchars($customer['CustomerContact']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Address:</strong> <?= htmlspecialchars($customer['CustomerAddress']) ?></p>
                        </div>
                    </div>
                    <?php if (!empty($customer['Notes'])): ?>
                        <div class="customer-notes">
                            <strong>Notes:</strong> <?= htmlspecialchars($customer['Notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST">
                <div class="branch-select-row">
                    <div class="branch-select-col">
                        <label class="form-label">Select Branch</label>
                        <select class="form-select" id="branch_code" name="branch_code" required>
                            <option value="">-- Select Branch --</option>
                            <?php while ($branch = $branches_result->fetch_assoc()): ?>
                                <option value="<?= $branch['BranchCode'] ?>" 
                                    <?= isset($_POST['branch_code']) && $_POST['branch_code'] == $branch['BranchCode'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['BranchName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="button" id="checkAvailabilityBtn" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Check Availability
                    </button>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Available Products</h5>
                    </div>
                    <div class="card-body products-container">
                        <?php if ($products_result->num_rows > 0): ?>
                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                <?php $products_result->data_seek(0); ?>
                                <?php while ($product = $products_result->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="card product-card" data-product-id="<?= $product['ProductID'] ?>">
                                            <img src="<?= htmlspecialchars($product['ProductImage']) ?>" 
                                                 class="product-img card-img-top" 
                                                 alt="<?= htmlspecialchars($product['Model']) ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($product['Model']) ?></h5>
                                                <p class="card-text">
                                                    <strong>Category:</strong> <?= htmlspecialchars($product['CategoryType']) ?><br>
                                                    <strong>Brand:</strong> <?= htmlspecialchars(getBrandName($product['BrandID'])) ?><br>
                                                    <strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?><br>
                                                    <span class="stock-info" id="stock-<?= $product['ProductID'] ?>">
                                                        <strong>Stocks:</strong> Select branch and check availability
                                                    </span>
                                                </p>
                                                <div class="quantity-control">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-decrease">-</button>
                                                    <input type="number" class="form-control form-control-sm quantity-input" 
                                                           name="quantities[]" min="1" value="1" 
                                                           data-product-id="<?= $product['ProductID'] ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-increase">+</button>
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

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="create_order" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Product selection
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('.quantity-control')) {
                    this.classList.toggle('selected');
                    const input = this.querySelector('input[name="products[]"]');
                    input.disabled = !this.classList.contains('selected');
                }
            });
        });

        // Quantity controls
        document.querySelectorAll('.quantity-increase').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                input.value = parseInt(input.value) + 1;
            });
        });

        document.querySelectorAll('.quantity-decrease').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.nextElementSibling;
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            });
        });

        // Check availability
        document.getElementById('checkAvailabilityBtn').addEventListener('click', function() {
            const branchCode = document.getElementById('branch_code').value;
            if (!branchCode) {
                alert('Please select a branch first');
                return;
            }

            document.querySelectorAll('.product-card').forEach(card => {
                const productId = card.dataset.productId;
                const stockElement = document.getElementById(`stock-${productId}`);
                
                fetch(`get_stock.php?product_id=${productId}&branch_code=${branchCode}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            stockElement.innerHTML = `<strong>Stocks:</strong> Error: ${data.error}`;
                        } else if (data.stock !== undefined) {
                            stockElement.innerHTML = `<strong>Stocks:</strong> ${data.stock}`;
                            
                            // Update quantity input max if product is selected
                            if (card.classList.contains('selected')) {
                                const quantityInput = card.querySelector('.quantity-input');
                                quantityInput.max = data.stock;
                                if (quantityInput.value > data.stock) {
                                    quantityInput.value = data.stock;
                                }
                            }
                        } else {
                            stockElement.innerHTML = `<strong>Stocks:</strong> Not available`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        stockElement.innerHTML = `<strong>Stocks:</strong> Error loading`;
                    });
            });
        });
    </script>
</body>
</html>