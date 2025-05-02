<?php
// Enable full error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connect.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'order-functions.php';

// Debug function
function debug_log($message) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

try {
    $ordersPerPage = 10;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $ordersPerPage;

    // Base query
    $query = "SELECT o.Orderhdr_id, o.CustomerID, o.Created_dt, o.Status,
                     c.CustomerName, e.EmployeeName as CreatedBy
              FROM Order_hdr o
              JOIN customer c ON o.CustomerID = c.CustomerID
              JOIN employee e ON o.Created_by = e.EmployeeID";

    $where = [];
    $params = [];
    $types = '';

    // Search filter
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where[] = "(c.CustomerName LIKE ? OR o.Orderhdr_id LIKE ?)";
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
        $types .= 'ss';
    }

    // Status filter
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "o.Status = ?";
        $params[] = $_GET['status'];
        $types .= 's';
    }

    // Add WHERE clause if filters exist
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }

    // Get total count of orders
    $conn = connect();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $totalQuery = "SELECT COUNT(*) as total FROM Order_hdr o";
    if (!empty($where)) {
        $totalQuery .= " WHERE " . implode(' AND ', $where);
    }

    debug_log("Total Query: " . $totalQuery);
    debug_log("Params: " . print_r($params, true));

    $stmt = $conn->prepare($totalQuery);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $totalResult = $stmt->get_result();
    $totalOrders = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalOrders / $ordersPerPage);
    $stmt->close();

    // Add pagination to main query
    $query .= " ORDER BY o.Created_dt DESC LIMIT ? OFFSET ?";
    $types .= 'ii';
    $params[] = $ordersPerPage;
    $params[] = $offset;

    debug_log("Main Query: " . $query);
    debug_log("Final Params: " . print_r($params, true));

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->bind_param($types, ...$params)) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $ordersResult = $stmt->get_result();
    $stmt->close();
    $conn->close();

    // Status options for filter
    $statusOptions = ['Pending', 'Processing', 'Completed', 'Cancelled'];

} catch (Exception $e) {
    // Enhanced error display for debugging
    $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'sql_error' => isset($conn) ? $conn->error : null,
        'stmt_error' => isset($stmt) ? $stmt->error : null
    ];
    
    debug_log("Full Error: " . print_r($errorDetails, true));
    
    // Show detailed error during development
    echo '<div class="alert alert-danger">';
    echo '<h4>Error Details</h4>';
    echo '<pre>' . print_r($errorDetails, true) . '</pre>';
    echo '</div>';
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
    <title>Admin | Orders</title>
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
        .order-entry {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .order-entry:hover {
            background-color: #f8f9fa;
        }
        .order-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .order-action {
            font-weight: 500;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-processing {
            background-color: #0dcaf0;
            color: #000;
        }
        .badge-completed {
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
            .order-entry {
                padding: 10px;
            }
            .order-action {
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
            .order-entry .d-flex {
                flex-direction: column;
            }
            .order-entry .badge {
                margin-bottom: 5px;
            }
        }
        /* Modal styles */
        .customer-list-table {
            max-height: 400px;
            overflow-y: auto;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <?php include "sidebar.php"?>
    
    <div class="main-content">
        <div class="orders-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fa-solid fa-pen-to-square me-2"></i>Orders</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                    <i class="fas fa-plus me-2"></i>New Order
                </button>            
            </div>

            <form method="get" action="order.php" class="mb-4 filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by customer or order ID..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '' ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
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
                                <th>Date</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['Orderhdr_id']; ?></td>
                                    <td><?php echo $order['CustomerName']; ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($order['Created_dt'])); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                                switch($order['Status']) {
                                                    case 'Pending': echo 'badge-pending'; break;
                                                    case 'Processing': echo 'badge-processing'; break;
                                                    case 'Completed': echo 'badge-completed'; break;
                                                    case 'Cancelled': echo 'badge-cancelled'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                            <?php echo $order['Status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $order['CreatedBy']; ?></td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['Orderhdr_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($order['Status'] == 'Pending' || $order['Status'] == 'Processing'): ?>
                                            <a href="edit-order.php?id=<?php echo $order['Orderhdr_id']; ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
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
                                    echo http_build_query(array_merge(
                                        $_GET,
                                        ['page' => $currentPage - 1]
                                    )); 
                                ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php 
                                    echo http_build_query(array_merge(
                                        $_GET,
                                        ['page' => $i]
                                    )); 
                                ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php 
                                    echo http_build_query(array_merge(
                                        $_GET,
                                        ['page' => $currentPage + 1]
                                    )); 
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

    <!-- New Order Modal -->
    <div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="newOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newOrderModalLabel">Create New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h5>How would you like to create this order?</h5>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-plus fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">New Customer</h5>
                                    <p class="card-text">Create a new customer profile and then add their order.</p>
                                    <a href="customerCreate.php?redirect=order.php" class="btn btn-primary">
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
                                    <p class="card-text">Select an existing customer to create an order for them.</p>
                                    <button type="button" class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#customerListCollapse">
                                        <i class="fas fa-list me-1"></i> Select Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="collapse" id="customerListCollapse">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Select Existing Customer</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="customer-list-table">
                                    <table class="table table-hover mb-0">
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
                                            $customerQuery = "SELECT CustomerID, CustomerName, CustomerContact FROM customer WHERE Status = 'Active' ORDER BY CustomerName";
                                            $customerResult = $conn->query($customerQuery);
                                            
                                            if ($customerResult->num_rows > 0) {
                                                while ($customer = $customerResult->fetch_assoc()) {
                                                    echo '<tr>
                                                        <td>'.$customer['CustomerID'].'</td>
                                                        <td>'.$customer['CustomerName'].'</td>
                                                        <td>'.$customer['CustomerContact'].'</td>
                                                        <td>
                                                            <a href="order-create.php?customer_id='.$customer['CustomerID'].'" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-check me-1"></i> Select
                                                            </a>
                                                        </td>
                                                    </tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center">No active customers found</td></tr>';
                                            }
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

            // Handle modal state when returning from customer creation
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('showModal')) {
                const modal = new bootstrap.Modal(document.getElementById('newOrderModal'));
                modal.show();
            }
        });
    </script>
</body>
</html>