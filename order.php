<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';

function getOrders($conn, $search, $branch, $status, $limit, $offset) {
    $query = "SELECT o.Orderhdr_id, o.Created_dt, c.CustomerName, 
                     e.EmployeeName as CreatedBy, b.BranchName,
                     COUNT(od.OrderDtlID) as ItemCount,
                     SUM(p.Price * od.Quantity) as TotalAmount,
                     MAX(od.Status) as Status
              FROM Order_hdr o
              JOIN customer c ON o.CustomerID = c.CustomerID
              JOIN employee e ON o.Created_by = e.LoginName
              JOIN BranchMaster b ON o.BranchCode = b.BranchCode
              LEFT JOIN orderDetails od ON o.Orderhdr_id = od.OrderHdr_id
              LEFT JOIN ProductBranchMaster pbm ON od.ProductBranchID = pbm.ProductBranchID
              LEFT JOIN productMstr p ON pbm.ProductID = p.ProductID";
    
    $where = "";
    $types = "";
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (c.CustomerName LIKE ? OR o.Orderhdr_id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
    
    if (!empty($branch)) {
        $where .= " AND o.BranchCode = ?";
        $params[] = $branch;
        $types .= "i";
    }
    
    if (!empty($status)) {
        $where .= " AND od.Status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . substr($where, 5);
    }
    
    $query .= " GROUP BY o.Orderhdr_id ORDER BY o.Created_dt DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function countTotalOrders($conn, $search, $branch, $status) {
    $query = "SELECT COUNT(DISTINCT o.Orderhdr_id) as total 
              FROM Order_hdr o
              JOIN customer c ON o.CustomerID = c.CustomerID
              LEFT JOIN orderDetails od ON o.Orderhdr_id = od.OrderHdr_id";
    
    $where = "";
    $types = "";
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND (c.CustomerName LIKE ? OR o.Orderhdr_id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
    
    if (!empty($branch)) {
        $where .= " AND o.BranchCode = ?";
        $params[] = $branch;
        $types .= "i";
    }
    
    if (!empty($status)) {
        $where .= " AND od.Status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . substr($where, 5);
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function countTotalOrders($conn, $search, $branch, $status) {
    $query = "SELECT COUNT(DISTINCT o.Orderhdr_id) as total 
              FROM Order_hdr o
              JOIN customer c ON o.CustomerID = c.CustomerID";
    
    $where = "";
    $types = "";
    
    if (!empty($search)) {
        $where .= " AND (c.CustomerName LIKE ? OR o.Orderhdr_id LIKE ?)";
        $types .= "ss";
    }
    
    if (!empty($branch)) {
        $where .= " AND o.BranchCode = ?";
        $types .= "i";
    }
    
    if (!empty($status)) {
        $where .= " AND od.Status = ?";
        $types .= "s";
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . substr($where, 5);
    }
    
    $stmt = $conn->prepare($query);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param($types, $searchParam, $searchParam, $branch, $status);
    } elseif (!empty($branch)) {
        $stmt->bind_param($types, $branch, $status);
    } elseif (!empty($status)) {
        $stmt->bind_param($types, $status);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function getAllBranches($conn) {
    $query = "SELECT BranchCode, BranchName FROM BranchMaster";
    $result = $conn->query($query);
    return $result;
}

function getActiveCustomers($conn) {
    $query = "SELECT CustomerID, CustomerName, CustomerContact FROM customer WHERE Status = 'Active' ORDER BY CustomerName";
    $result = $conn->query($query);
    return $result;
}

$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$conn = connect();
$ordersResult = getOrders($conn, $search, $branch, $status, $ordersPerPage, $offset);
$totalOrders = countTotalOrders($conn, $search, $branch, $status);
$totalPages = ceil($totalOrders / $ordersPerPage);
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
                            <?php while ($branchRow = $branchesResult->fetch_assoc()): ?>
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
                            <option value="Complete" <?php echo ($status == 'Complete' ? 'selected' : ''); ?>>Complete</option>
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

            <?php if ($ordersResult->num_rows > 0): ?>
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
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['Orderhdr_id']; ?></td>
                                    <td><?php echo $order['CustomerName']; ?></td>
                                    <td><?php echo $order['BranchName']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['Created_dt'])); ?></td>
                                    <td><?php echo $order['ItemCount']; ?></td>
                                    <td>₱<?php echo number_format($order['TotalAmount'], 2); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                                switch($order['Status']) {
                                                    case 'Complete': echo 'badge-complete'; break;
                                                    case 'Cancelled': echo 'badge-cancelled'; break;
                                                    default: echo 'badge-pending';
                                                }
                                            ?>">
                                            <?php echo $order['Status'] ?? 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orderDetails.php?id=<?php echo $order['Orderhdr_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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
                    <div class="text-center mb-4">
                        <h5>Choose an option to create a new order:</h5>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-plus fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Add New Customer</h5>
                                    <p class="card-text">Create a new customer profile and then add their order.</p>
                                    <a href="customerCreate.php?redirect=orders.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add New Customer
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x mb-3 text-success"></i>
                                    <h5 class="card-title">Existing Customer</h5>
                                    <p class="card-text">Select from existing customers to create an order.</p>
                                    <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#customerSelectSection">
                                        <i class="fas fa-list me-1"></i> Select Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="collapse" id="customerSelectSection">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>Select Existing Customer</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="customerSearch" placeholder="Search customers...">
                                </div>
                                <div class="customer-select-table">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer ID</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $conn = connect();
                                            $customersResult = getActiveCustomers($conn);
                                            
                                            while ($customer = $customersResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $customer['CustomerID']; ?></td>
                                                    <td><?php echo $customer['CustomerName']; ?></td>
                                                    <td><?php echo $customer['CustomerContact']; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary select-customer" 
                                                                data-customer-id="<?php echo $customer['CustomerID']; ?>"
                                                                data-customer-name="<?php echo htmlspecialchars($customer['CustomerName']); ?>">
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
                        </div>
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

            document.querySelectorAll('.select-customer').forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-customer-id');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addOrderModal'));
                    modal.hide();
                    window.location.href = `orderCreate.php?customer_id=${customerId}`;
                });
            });
        });
    </script>
</body>
</html>