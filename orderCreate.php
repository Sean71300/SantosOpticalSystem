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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_availability'])) {
        // Handle availability check
        $_SESSION['selected_branch'] = $_POST['branch_code'];
        $_SESSION['selected_shape'] = $_POST['shape_filter'] ?? '';
        header("Location: ".$_SERVER['PHP_SELF']."?customer_id=".$customer_id);
        exit();
    } elseif (isset($_POST['create_order'])) {
        // Handle order creation
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        $branch_code = $_POST['branch_code'];
        
        if (empty($product_id)) {
            $errorMessage = "Please select a product.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Verify product availability
                $check_query = "SELECT pb.ProductBranchID, pb.Stocks 
                               FROM ProductBranchMaster pb
                               WHERE pb.ProductID = ? 
                               AND pb.BranchCode = ?
                               AND pb.Stocks >= ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("iii", $product_id, $branch_code, $quantity);
                $stmt->execute();
                $check_result = $stmt->get_result();
                
                if ($check_result->num_rows === 0) {
                    throw new Exception("Selected product is not available at the chosen branch or insufficient stock");
                }
                
                $stock_row = $check_result->fetch_assoc();
                
                // Create order header
                $order_hdr_query = "INSERT INTO Order_hdr (CustomerID, BranchCode, Created_by) 
                                    VALUES (?, ?, ?)";
                $stmt = $conn->prepare($order_hdr_query);
                $stmt->bind_param("iis", $customer_id, $branch_code, $_SESSION['EmployeeName']);
                $stmt->execute();
                $order_id = $conn->insert_id;
                
                // Add order detail
                $order_detail_query = "INSERT INTO orderDetails 
                                      (OrderHdr_id, ProductBranchID, Quantity, ActivityCode, Status) 
                                      VALUES (?, ?, ?, 2, 'Pending')";
                $stmt = $conn->prepare($order_detail_query);
                $stmt->bind_param("iii", $order_id, $stock_row['ProductBranchID'], $quantity);
                $stmt->execute();
                
                // Update stock
                $update_stock_query = "UPDATE ProductBranchMaster 
                                      SET Stocks = Stocks - ? 
                                      WHERE ProductBranchID = ?";
                $stmt = $conn->prepare($update_stock_query);
                $stmt->bind_param("ii", $quantity, $stock_row['ProductBranchID']);
                $stmt->execute();
                
                // Log the activity
                $activity = new ActivityTracker();
                $activity->logActivity(
                    $_SESSION['EmployeeID'],
                    $order_id,
                    'order',
                    3,
                    "Created new order for customer ID: $customer_id"
                );
                
                $conn->commit();
                $_SESSION['successMessage'] = "Order created successfully!";
                header("Location: order.php?order_id=$order_id");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $errorMessage = $e->getMessage();
            }
        }
    }
}

// Fetch branches
$branches_query = "SELECT * FROM BranchMaster";
$branches_result = $conn->query($branches_query);

// Fetch available shapes for filter
$shapes_query = "SELECT DISTINCT ShapeID FROM productMstr";
$shapes_result = $conn->query($shapes_query);

// Fetch products based on filters
$products_query = "SELECT p.* FROM productMstr p 
                  WHERE p.Avail_FL = 'Available'";
                  
if (isset($_SESSION['selected_branch'])) {
    $products_query .= " AND EXISTS (
        SELECT 1 FROM ProductBranchMaster pb 
        WHERE pb.ProductID = p.ProductID 
        AND pb.BranchCode = '".$_SESSION['selected_branch']."'
        AND pb.Stocks > 0
    )";
}

if (!empty($_SESSION['selected_shape'])) {
    $products_query .= " AND p.ShapeID = '".$_SESSION['selected_shape']."'";
}

$products_result = $conn->query($products_query);

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

function getShapeName($shape_id) {
    global $conn;
    $query = "SELECT Description FROM shapeMaster WHERE ShapeID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $shape_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['Description'];
}

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
    <style>
        body {
            background-color: #f5f7fa;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .customer-info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            height: 100%;
        }
        .product-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .product-img {
            height: 120px;
            object-fit: contain;
            display: block;
            margin: 0 auto 15px;
        }
        .stock-info {
            font-weight: bold;
        }
        .notes-box {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .filter-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .back-btn-container {
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="container py-4">
        <div class="back-btn-container">
            <a href="customers.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Customers
            </a>
        </div>

        <div class="form-container">
            <h1 class="mb-4"><i class="fas fa-cart-plus me-2"></i> New Order</h1>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <?= $errorMessage ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="customer-info-box">
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
                    <div class="notes-box">
                        <strong>Notes:</strong> <?= htmlspecialchars($customer['Notes']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" id="orderForm">
                <div class="filter-container">
                    <div class="row filter-row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Select Branch</label>
                            <select class="form-select" name="branch_code" required>
                                <option value="">-- Select Branch --</option>
                                <?php while ($branch = $branches_result->fetch_assoc()): ?>
                                    <option value="<?= $branch['BranchCode'] ?>" 
                                        <?= (isset($_SESSION['selected_branch']) && $_SESSION['selected_branch'] == $branch['BranchCode']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($branch['BranchName']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Shape</label>
                            <select class="form-select" name="shape_filter">
                                <option value="">All Shapes</option>
                                <?php while ($shape = $shapes_result->fetch_assoc()): ?>
                                    <option value="<?= $shape['ShapeID'] ?>" 
                                        <?= (isset($_SESSION['selected_shape']) && $_SESSION['selected_shape'] == $shape['ShapeID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(getShapeName($shape['ShapeID'])) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" name="check_availability" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Available Products</h4>
                
                <?php if ($products_result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="product-card" onclick="selectProduct(this, <?= $product['ProductID'] ?>)">
                                    <img src="<?= htmlspecialchars($product['ProductImage']) ?>" 
                                         class="product-img" 
                                         alt="<?= htmlspecialchars($product['Model']) ?>">
                                    <h5><?= htmlspecialchars($product['Model']) ?></h5>
                                    <p>
                                        <strong>Category:</strong> <?= htmlspecialchars($product['CategoryType']) ?><br>
                                        <strong>Brand:</strong> <?= htmlspecialchars(getBrandName($product['BrandID'])) ?><br>
                                        <strong>Shape:</strong> <?= htmlspecialchars(getShapeName($product['ShapeID'])) ?><br>
                                        <strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?><br>
                                        <?php if (isset($_SESSION['selected_branch'])): ?>
                                            <span class="stock-info">
                                                <strong>Stocks:</strong> <?= getProductStock($product['ProductID'], $_SESSION['selected_branch']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="quantity-control mt-3" style="display: none;">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" 
                                               name="quantity" min="1" value="1"
                                               max="<?= isset($_SESSION['selected_branch']) ? getProductStock($product['ProductID'], $_SESSION['selected_branch']) : '' ?>">
                                    </div>
                                    <input type="hidden" name="product_id" value="">
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php if (isset($_SESSION['selected_branch'])): ?>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="create_order" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Create Order
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php if (isset($_SESSION['selected_branch'])): ?>
                            No products available matching your filters.
                        <?php else: ?>
                            Please select a branch and apply filters to see available products.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        function selectProduct(card, productId) {
            // Remove selection from all cards
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.quantity-control').style.display = 'none';
            });
            
            // Select clicked card
            card.classList.add('selected');
            card.querySelector('.quantity-control').style.display = 'block';
            card.querySelector('input[name="product_id"]').value = productId;
        }
    </script>
</body>
</html>