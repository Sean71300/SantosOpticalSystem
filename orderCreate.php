<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'connect.php';
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
$products_query = "SELECT p.*, pb.Stocks, b.BranchName 
                   FROM productMstr p
                   JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID
                   JOIN BranchMaster b ON pb.BranchCode = b.BranchCode
                   WHERE p.Avail_FL = 'Available' AND pb.Stocks > 0
                   ORDER BY p.CategoryType, p.BrandID";
$products_result = $conn->query($products_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $selected_products = $_POST['products'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $branch_code = $_POST['branch_code'];
    
    if (empty($selected_products)) {
        $error = "Please select at least one product.";
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
                
                // Get product branch ID
                $product_branch_query = "SELECT ProductBranchID FROM ProductBranchMaster 
                                         WHERE ProductID = ? AND BranchCode = ?";
                $stmt = $conn->prepare($product_branch_query);
                $stmt->bind_param("ii", $product_id, $branch_code);
                $stmt->execute();
                $pb_result = $stmt->get_result();
                $pb_row = $pb_result->fetch_assoc();
                $product_branch_id = $pb_row['ProductBranchID'];
                
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
            
            header("Location: order.php?order_id=$order_id");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating order: " . $e->getMessage();
        }
    }
}

// Fetch branches for dropdown
$branches_query = "SELECT * FROM BranchMaster";
$branches_result = $conn->query($branches_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order - Santos Optical</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Create New Order</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Order</li>
                    </ol>
                </nav>
            </div>
        </div>

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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="branch_code" class="form-label">Select Branch</label>
                    <select class="form-select" id="branch_code" name="branch_code" required>
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
                                <div class="row row-cols-1 row-cols-md-3 g-4">
                                    <?php while ($product = $products_result->fetch_assoc()): ?>
                                        <div class="col">
                                            <div class="card product-card h-100" onclick="toggleSelection(this, <?= $product['ProductID'] ?>)">
                                                <img src="<?= htmlspecialchars($product['ProductImage']) ?>" 
                                                     class="card-img-top p-3" 
                                                     alt="<?= htmlspecialchars($product['Model']) ?>"
                                                     style="height: 200px; object-fit: contain;">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?= htmlspecialchars($product['Model']) ?></h5>
                                                    <p class="card-text">
                                                        <strong>Category:</strong> <?= htmlspecialchars($product['CategoryType']) ?><br>
                                                        <strong>Brand:</strong> <?= htmlspecialchars(getBrandName($product['BrandID'])) ?><br>
                                                        <strong>Price:</strong> <?= htmlspecialchars($product['Price']) ?><br>
                                                        <strong>Stocks:</strong> <?= htmlspecialchars($product['Stocks']) ?><br>
                                                        <strong>Branch:</strong> <?= htmlspecialchars($product['BranchName']) ?>
                                                    </p>
                                                    <div class="input-group mb-3" style="display: none;">
                                                        <input type="number" 
                                                               class="form-control quantity-input" 
                                                               name="quantities[]" 
                                                               min="1" 
                                                               max="<?= $product['Stocks'] ?>" 
                                                               value="1">
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

            <div class="row">
                <div class="col">
                    <button type="submit" name="create_order" class="btn btn-primary">Create Order</button>
                    <a href="customers.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSelection(card, productId) {
            card.classList.toggle('selected');
            const quantityInput = card.querySelector('.quantity-input');
            const productInput = card.querySelector('input[name="products[]"]');
            
            if (card.classList.contains('selected')) {
                quantityInput.parentElement.style.display = 'flex';
                productInput.disabled = false;
            } else {
                quantityInput.parentElement.style.display = 'none';
                productInput.disabled = true;
            }
        }
    </script>
</body>
</html>

<?php
// Helper function to get brand name
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