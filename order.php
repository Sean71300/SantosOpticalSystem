<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

// Database functions without any joins
function getOrderHeaders($conn, $search = '', $branch = '', $limit = 10, $offset = 0) {
    $query = "SELECT Orderhdr_id, CustomerID, BranchCode, Created_dt, Created_by FROM Order_hdr";
    
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $where[] = "Orderhdr_id LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }
    
    if (!empty($branch)) {
        $where[] = "BranchCode = ?";
        $params[] = $branch;
        $types .= 's';
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }
    
    $query .= " ORDER BY Created_dt DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function getCustomerDetails($conn, $customerId) {
    $query = "SELECT CustomerName FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getBranchDetails($conn, $branchCode) {
    $query = "SELECT BranchName FROM BranchMaster WHERE BranchCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $branchCode);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getEmployeeDetails($conn, $loginName) {
    $query = "SELECT EmployeeName FROM employee WHERE LoginName = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $loginName);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getOrderItems($conn, $orderId) {
    $query = "SELECT ProductBranchID, Quantity, Status FROM orderDetails WHERE OrderHdr_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    return $stmt->get_result();
}

function getProductPrice($conn, $productBranchId) {
    $query = "SELECT ProductID FROM ProductBranchMaster WHERE ProductBranchID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $productBranchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) return 0;
    
    $query = "SELECT Price FROM productMstr WHERE ProductID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product['ProductID']);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = $result->fetch_assoc();
    
    return $price['Price'] ?? 0;
}

function countOrderHeaders($conn, $search = '', $branch = '') {
    $query = "SELECT COUNT(Orderhdr_id) as total FROM Order_hdr";
    
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $where[] = "Orderhdr_id LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= 's';
    }
    
    if (!empty($branch)) {
        $where[] = "BranchCode = ?";
        $params[] = $branch;
        $types .= 's';
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

function getAllBranches($conn) {
    $query = "SELECT BranchCode, BranchName FROM BranchMaster";
    return $conn->query($query);
}

// Main processing
$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

$search = $_GET['search'] ?? '';
$branch = $_GET['branch'] ?? '';
$status = $_GET['status'] ?? '';

$conn = connect();

// Get order headers
$orderHeaders = getOrderHeaders($conn, $search, $branch, $ordersPerPage, $offset);
$totalOrders = countOrderHeaders($conn, $search, $branch);
$totalPages = ceil($totalOrders / $ordersPerPage);

// Process orders to get additional details
$orders = [];
while ($header = $orderHeaders->fetch_assoc()) {
    $orderId = $header['Orderhdr_id'];
    
    // Get customer details
    $customer = getCustomerDetails($conn, $header['CustomerID']);
    $customerName = $customer['CustomerName'] ?? 'Unknown';
    
    // Get branch details
    $branchDetails = getBranchDetails($conn, $header['BranchCode']);
    $branchName = $branchDetails['BranchName'] ?? 'Unknown';
    
    // Get employee details
    $employee = getEmployeeDetails($conn, $header['Created_by']);
    $createdBy = $employee['EmployeeName'] ?? 'Unknown';
    
    // Get order items and calculate totals
    $items = getOrderItems($conn, $orderId);
    $itemCount = 0;
    $totalAmount = 0;
    $orderStatus = 'Pending';
    
    while ($item = $items->fetch_assoc()) {
        $itemCount++;
        $price = getProductPrice($conn, $item['ProductBranchID']);
        $totalAmount += $price * $item['Quantity'];
        
        // Determine order status
        if ($item['Status'] === 'Complete') {
            $orderStatus = 'Complete';
        } elseif ($item['Status'] === 'Cancelled' && $orderStatus !== 'Complete') {
            $orderStatus = 'Cancelled';
        }
    }
    
    // Apply status filter if set
    if (!empty($status) && $orderStatus !== $status) {
        continue;
    }
    
    $orders[] = [
        'Orderhdr_id' => $orderId,
        'Created_dt' => $header['Created_dt'],
        'CustomerName' => $customerName,
        'CreatedBy' => $createdBy,
        'BranchName' => $branchName,
        'ItemCount' => $itemCount,
        'TotalAmount' => $totalAmount,
        'Status' => $orderStatus
    ];
}

// Get branches for filter dropdown
$branchesResult = getAllBranches($conn);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <!-- [Keep your existing head section exactly the same] -->
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <!-- [Keep your existing header and filter form exactly the same] -->

        <?php if (!empty($orders)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Branch</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['Orderhdr_id']) ?></td>
                                <td><?= htmlspecialchars($order['CustomerName']) ?></td>
                                <td><?= htmlspecialchars($order['BranchName']) ?></td>
                                <td><?= date('M j, Y', strtotime($order['Created_dt'])) ?></td>
                                <td><?= $order['ItemCount'] ?></td>
                                <td>₱<?= number_format($order['TotalAmount'], 2) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= match($order['Status']) {
                                            'Complete' => 'badge-complete',
                                            'Cancelled' => 'badge-cancelled',
                                            default => 'badge-pending'
                                        } ?>">
                                        <?= $order['Status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="orderDetails.php?id=<?= $order['Orderhdr_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- [Keep your existing pagination exactly the same] -->
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> No orders found.
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

            // Customer search functionality
            const customerSearch = document.getElementById('customerSearch');
            if (customerSearch) {
                customerSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.customer-select-table tbody tr');
                    
                    rows.forEach(row => {
                        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const contact = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                        
                        if (name.includes(searchTerm) || contact.includes(searchTerm) || id.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Customer selection
            document.querySelectorAll('.select-customer').forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-customer-id');
                    const customerName = this.getAttribute('data-customer-name');
                    
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addOrderModal'));
                    modal.hide();
                    
                    // Redirect to order creation page with customer ID
                    window.location.href = `orderCreate.php?customer_id=${customerId}`;
                });
            });
        });
    </script>
</body>
</html>

    