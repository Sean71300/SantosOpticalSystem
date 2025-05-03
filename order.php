<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

function getOrderHeaders($conn, $search = '', $branch = '', $status = '', $limit = 10, $offset = 0) {
    $query = "SELECT Orderhdr_id, CustomerID, BranchCode, Created_dt, Created_by FROM Order_hdr";
    
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $customerIds = [];
        $customerQuery = "SELECT CustomerID FROM customer WHERE CustomerName LIKE ?";
        $stmt = $conn->prepare($customerQuery);
        $searchParam = '%' . $search . '%';
        $stmt->bind_param('s', $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $customerIds[] = $row['CustomerID'];
        }
        $stmt->close();
        
        if (!empty($customerIds)) {
            $placeholders = implode(',', array_fill(0, count($customerIds), '?'));
            $where[] = "(Orderhdr_id LIKE ? OR CustomerID IN ($placeholders))";
            $params[] = '%' . $search . '%';
            $params = array_merge($params, $customerIds);
            $types .= str_repeat('i', count($customerIds)) . 's';
        } else {
            $where[] = "Orderhdr_id LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
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
    $result = $stmt->get_result();
    $orders = [];
    
    while ($header = $result->fetch_assoc()) {
        $customerName = getCustomerName($conn, $header['CustomerID']);
        $header['CustomerName'] = $customerName;
        $orders[] = $header;
    }
    
    $stmt->close();
    
    if (!empty($status)) {
        $filteredOrders = [];
        foreach ($orders as $order) {
            $orderStatus = getOrderStatus($conn, $order['Orderhdr_id']);
            if ($orderStatus === $status) {
                $filteredOrders[] = $order;
            }
        }
        return $filteredOrders;
    }
    
    return $orders;
}

function getOrderStatus($conn, $orderId) {
    $query = "SELECT Status FROM orderDetails WHERE OrderHdr_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $completeCount = 0;
    $cancelledCount = 0;
    $totalItems = 0;
    
    while ($row = $result->fetch_assoc()) {
        $totalItems++;
        if ($row['Status'] === 'Completed') {
            $completeCount++;
        } elseif ($row['Status'] === 'Cancelled') {
            $cancelledCount++;
        }
    }
    
    $stmt->close();
    
    if ($completeCount === $totalItems && $totalItems > 0) {
        return 'Completed';
    } elseif ($cancelledCount === $totalItems && $totalItems > 0) {
        return 'Cancelled';
    }
    return 'Pending';
}

function getCustomerName($conn, $customerId) {
    $query = "SELECT CustomerName FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['CustomerName'] : 'Unknown';
}

function getBranchName($conn, $branchCode) {
    $query = "SELECT BranchName FROM BranchMaster WHERE BranchCode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $branchCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['BranchName'] : 'Unknown';
}

function getEmployeeName($conn, $loginName) {
    $query = "SELECT EmployeeName FROM employee WHERE LoginName = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $loginName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['EmployeeName'] : 'Unknown';
}

function getOrderDetails($conn, $orderId) {
    $query = "SELECT od.Quantity, od.Status, od.ProductBranchID FROM orderDetails od WHERE od.OrderHdr_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = [];
    
    while ($row = $result->fetch_assoc()) {
        $price = getProductPrice($conn, $row['ProductBranchID']);
        $row['Price'] = (float)$price;
        $row['Quantity'] = (int)$row['Quantity'];
        $details[] = $row;
    }
    
    $stmt->close();
    return $details;
}

function getProductPrice($conn, $productBranchId) {
    $query = "SELECT ProductID FROM ProductBranchMaster WHERE ProductBranchID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $productBranchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) return 0;
    
    $productId = $row['ProductID'];
    $priceQuery = "SELECT Price FROM productMstr WHERE ProductID = ?";
    $priceStmt = $conn->prepare($priceQuery);
    $priceStmt->bind_param('i', $productId);
    $priceStmt->execute();
    $priceResult = $priceStmt->get_result();
    $priceRow = $priceResult->fetch_assoc();
    $priceStmt->close();
    
    return $priceRow ? (float)$priceRow['Price'] : 0;
}

function getOrderTotal($conn, $orderId) {
    $details = getOrderDetails($conn, $orderId);
    $total = 0;
    
    foreach ($details as $detail) {
        $total += $detail['Price'] * $detail['Quantity'];
    }
    
    return $total;
}

function countOrderHeaders($conn, $search = '', $branch = '', $status = '') {
    $query = "SELECT COUNT(Orderhdr_id) as total FROM Order_hdr";
    
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $customerIds = [];
        $customerQuery = "SELECT CustomerID FROM customer WHERE CustomerName LIKE ?";
        $stmt = $conn->prepare($customerQuery);
        $searchParam = '%' . $search . '%';
        $stmt->bind_param('s', $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $customerIds[] = $row['CustomerID'];
        }
        $stmt->close();
        
        if (!empty($customerIds)) {
            $placeholders = implode(',', array_fill(0, count($customerIds), '?'));
            $where[] = "(Orderhdr_id LIKE ? OR CustomerID IN ($placeholders))";
            $params[] = '%' . $search . '%';
            $params = array_merge($params, $customerIds);
            $types .= str_repeat('i', count($customerIds)) . 's';
        } else {
            $where[] = "Orderhdr_id LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
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
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $total = $row ? $row['total'] : 0;
    
    if (!empty($status)) {
        $filteredCount = 0;
        $allOrdersQuery = "SELECT Orderhdr_id FROM Order_hdr" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "");
        $allStmt = $conn->prepare($allOrdersQuery);
        
        if (!empty($params)) {
            $allStmt->bind_param($types, ...$params);
        }
        
        $allStmt->execute();
        $allResult = $allStmt->get_result();
        
        while ($order = $allResult->fetch_assoc()) {
            $orderStatus = getOrderStatus($conn, $order['Orderhdr_id']);
            if ($orderStatus === $status) {
                $filteredCount++;
            }
        }
        
        $allStmt->close();
        return $filteredCount;
    }
    
    return $total;
}

function getAllBranches($conn) {
    $query = "SELECT BranchCode, BranchName FROM BranchMaster";
    $result = $conn->query($query);
    return $result;
}

$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

$search = $_GET['search'] ?? '';
$branch = $_GET['branch'] ?? '';
$status = $_GET['status'] ?? '';

$conn = connect();

$orderHeaders = getOrderHeaders($conn, $search, $branch, $status, $ordersPerPage, $offset);
$totalOrders = countOrderHeaders($conn, $search, $branch, $status);
$totalPages = ceil($totalOrders / $ordersPerPage);

$orders = [];
foreach ($orderHeaders as $header) {
    $orderId = $header['Orderhdr_id'];
    $details = getOrderDetails($conn, $orderId);
    
    $itemCount = count($details);
    $totalAmount = getOrderTotal($conn, $orderId);
    $orderStatus = getOrderStatus($conn, $orderId);
    
    $orders[] = [
        'Orderhdr_id' => $orderId,
        'Created_dt' => $header['Created_dt'],
        'CustomerName' => $header['CustomerName'],
        'CreatedBy' => getEmployeeName($conn, $header['Created_by']),
        'BranchName' => getBranchName($conn, $header['BranchCode']),
        'ItemCount' => $itemCount,
        'TotalAmount' => $totalAmount,
        'Status' => $orderStatus
    ];
}

$branchesResult = getAllBranches($conn);
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
    <title>Orders Management | Santos Optical</title>
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
        .orders-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .order-card {
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .order-card:hover {
            background-color: #f8f9fa;
        }
        .order-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .order-title {
            font-weight: 500;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-complete {
            background-color: #198754;
        }
        .badge-cancelled {
            background-color: #dc3545;
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
            .filter-form .col-md-4,
            .filter-form .col-md-3,
            .filter-form .col-md-2 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }
            .order-card {
                padding: 10px;
            }
            .order-title {
                font-size: 0.9rem;
            }
            .order-time {
                font-size: 0.75rem;
            }
        }
        @media (max-width: 576px) {
            .orders-container {
                padding: 15px;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 .btn {
                margin-top: 10px;
                width: 100%;
            }
            .order-card .d-flex {
                flex-direction: column;
            }
            .order-card .badge {
                margin-bottom: 5px;
            }
        }
        .customer-select-table {
            max-height: 300px;
            overflow-y: auto;
        }
        .order-details-table {
            width: 100%;
        }
        .order-details-table th {
            background-color: #f8f9fa;
            padding: 8px;
            text-align: left;
        }
        .order-details-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart me-2"></i>Orders Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-plus me-1"></i> Add New Order
            </button>
        </div>

        <div class="orders-container">
            <form method="get" action="order.php" class="mb-4 filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search orders or customers..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="branch" class="form-label">Filter by Branch</label>
                        <select class="form-select" id="branch" name="branch">
                            <option value="">All Branches</option>
                            <?php 
                            $branchesResult->data_seek(0); 
                            while ($branchRow = $branchesResult->fetch_assoc()): ?>
                                <option value="<?php echo $branchRow['BranchCode']; ?>" 
                                    <?php echo ($branch == $branchRow['BranchCode'] ? 'selected' : ''); ?>>
                                    <?php echo $branchRow['BranchName']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo ($status == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="Completed" <?php echo ($status == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($status == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>                       
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Branch</th>
                                <th>Date</th>
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
                                    <td>
                                        <span class="badge 
                                            <?= match($order['Status']) {
                                                'Completed' => 'badge-complete',
                                                'Cancelled' => 'badge-cancelled',
                                                default => 'badge-pending'
                                            } ?>">
                                            <?= $order['Status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-order-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderDetailsModal"
                                                data-order-id="<?= $order['Orderhdr_id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Orders pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php 
                                    $newParams = $_GET;
                                    $newParams['page'] = $currentPage - 1;
                                    echo http_build_query($newParams); 
                                ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php 
                                    $newParams = $_GET;
                                    $newParams['page'] = $i;
                                    echo http_build_query($newParams); 
                                ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php 
                                    $newParams = $_GET;
                                    $newParams['page'] = $currentPage + 1;
                                    echo http_build_query($newParams); 
                                ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No orders found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOrderModalLabel">Create New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customerSearch" class="form-label">Search Customer</label>
                        <input type="text" class="form-control" id="customerSearch" placeholder="Search by name or contact number...">
                    </div>
                    
                    <div class="customer-select-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $conn = connect();
                                $customers = $conn->query("SELECT CustomerID, CustomerName, CustomerContact FROM customer ORDER BY CustomerName");
                                while ($customer = $customers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $customer['CustomerID'] ?></td>
                                        <td><?= htmlspecialchars($customer['CustomerName']) ?></td>
                                        <td><?= htmlspecialchars($customer['CustomerContact']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary select-customer" 
                                                    data-customer-id="<?= $customer['CustomerID'] ?>"
                                                    data-customer-name="<?= htmlspecialchars($customer['CustomerName']) ?>">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; 
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading order details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Order details modal
            const orderDetailsModal = document.getElementById('orderDetailsModal');
            if (orderDetailsModal) {
                orderDetailsModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const orderId = button.getAttribute('data-order-id');
                    const modalBody = orderDetailsModal.querySelector('.modal-body');
                    
                    // Load order details via AJAX
                    fetch(`orderDetails.php?id=${orderId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            modalBody.innerHTML = data;
                        })
                        .catch(error => {
                            console.error('Error loading order details:', error);
                            modalBody.innerHTML = `
                                <div class="alert alert-danger">
                                    Failed to load order details. Please try again.
                                    <p class="text-muted">${error.message}</p>
                                </div>
                            `;
                        });
                });
                
                orderDetailsModal.addEventListener('hidden.bs.modal', function() {
                    const modalBody = orderDetailsModal.querySelector('.modal-body');
                    modalBody.innerHTML = `
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Loading order details...</p>
                        </div>
                    `;
                });
            }

            // Initialize all view buttons
            document.querySelectorAll('.view-order-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    // The modal will handle the rest via the show.bs.modal event
                });
            });
        });
        </script>
</body>
</html>