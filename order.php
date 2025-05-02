<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

$conn = connect();

$baseQuery = "SELECT Orderhdr_id, CustomerID, BranchCode, Created_dt, Created_by FROM Order_hdr";
$where = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "Orderhdr_id LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types .= 's';
}

if (isset($_GET['branch']) && !empty($_GET['branch'])) {
    $where[] = "BranchCode = ?";
    $params[] = $_GET['branch'];
    $types .= 's';
}

$totalQuery = "SELECT COUNT(Orderhdr_id) as total FROM Order_hdr";
if (!empty($where)) {
    $totalQuery .= " WHERE " . implode(' AND ', $where);
}
$stmt = $conn->prepare($totalQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalResult = $stmt->get_result();
$totalOrders = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $ordersPerPage);
$stmt->close();

$query = $baseQuery;
if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}
$query .= " ORDER BY Created_dt DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $ordersPerPage;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$ordersResult = $stmt->get_result();

$orders = [];
while ($order = $ordersResult->fetch_assoc()) {
    $customerStmt = $conn->prepare("SELECT CustomerName FROM customer WHERE CustomerID = ?");
    $customerStmt->bind_param('i', $order['CustomerID']);
    $customerStmt->execute();
    $customer = $customerStmt->get_result()->fetch_assoc();
    
    $branchStmt = $conn->prepare("SELECT BranchName FROM BranchMaster WHERE BranchCode = ?");
    $branchStmt->bind_param('s', $order['BranchCode']);
    $branchStmt->execute();
    $branch = $branchStmt->get_result()->fetch_assoc();
    
    $employeeStmt = $conn->prepare("SELECT EmployeeName FROM employee WHERE LoginName = ?");
    $employeeStmt->bind_param('s', $order['Created_by']);
    $employeeStmt->execute();
    $employee = $employeeStmt->get_result()->fetch_assoc();
    
    $itemsStmt = $conn->prepare("SELECT ProductBranchID, Quantity, Status FROM orderDetails WHERE OrderHdr_id = ?");
    $itemsStmt->bind_param('i', $order['Orderhdr_id']);
    $itemsStmt->execute();
    $items = $itemsStmt->get_result();
    
    $itemCount = 0;
    $totalAmount = 0.0;
    $status = 'Pending';
    
    while ($item = $items->fetch_assoc()) {
        $itemCount++;
        $productStmt = $conn->prepare("SELECT ProductID FROM ProductBranchMaster WHERE ProductBranchID = ?");
        $productStmt->bind_param('i', $item['ProductBranchID']);
        $productStmt->execute();
        $productBranch = $productStmt->get_result()->fetch_assoc();
        
        $priceStmt = $conn->prepare("SELECT Price FROM productMstr WHERE ProductID = ?");
        $priceStmt->bind_param('i', $productBranch['ProductID']);
        $priceStmt->execute();
        $price = $priceStmt->get_result()->fetch_assoc();
        
        $totalAmount += (float)$price['Price'] * (int)$item['Quantity'];
        
        if ($item['Status'] === 'Complete') {
            $status = 'Complete';
        } elseif ($item['Status'] === 'Cancelled' && $status !== 'Complete') {
            $status = 'Cancelled';
        }
    }
    
    if (isset($_GET['status']) && !empty($_GET['status']) && $status !== $_GET['status']) continue;
    
    $orders[] = [
        'Orderhdr_id' => $order['Orderhdr_id'],
        'Created_dt' => $order['Created_dt'],
        'CustomerName' => $customer['CustomerName'] ?? 'Unknown',
        'BranchName' => $branch['BranchName'] ?? 'Unknown',
        'CreatedBy' => $employee['EmployeeName'] ?? 'Unknown',
        'ItemCount' => $itemCount,
        'TotalAmount' => $totalAmount,
        'Status' => $status
    ];
}

$branchesResult = $conn->query("SELECT BranchCode, BranchName FROM BranchMaster");
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
        body {background-color:#f5f7fa;padding-top:60px}.sidebar{background-color:white;height:100vh;padding:20px 0 70px;color:#2c3e50;position:fixed;width:250px;box-shadow:2px 0 5px rgba(0,0,0,0.1);z-index:1000}.main-content{margin-left:250px;padding:20px;width:calc(100% - 250px);transition:margin 0.3s ease}.orders-container{background-color:white;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);padding:20px}.order-card{border-left:4px solid #0d6efd;padding:15px;margin-bottom:15px;transition:all 0.3s}.order-card:hover{background-color:#f8f9fa}.order-time{font-size:0.85rem;color:#6c757d}.order-title{font-weight:500}.badge-pending{background-color:#ffc107;color:#000}.badge-complete{background-color:#198754}.badge-cancelled{background-color:#dc3545}@media (max-width:992px){.sidebar{transform:translateX(-100%)}.sidebar.active{transform:translateX(0)}.main-content{margin-left:0;width:100%}}@media (max-width:768px){.filter-form .col-md-4,.filter-form .col-md-3,.filter-form .col-md-2{flex:0 0 100%;max-width:100%;margin-bottom:10px}.order-card{padding:10px}.order-title{font-size:0.9rem}.order-time{font-size:0.75rem}}@media (max-width:576px){.orders-container{padding:15px}.d-flex.justify-content-between.align-items-center.mb-4{flex-direction:column;align-items:flex-start}.d-flex.justify-content-between.align-items-center.mb-4 .btn{margin-top:10px;width:100%}.order-card .d-flex{flex-direction:column}.order-card .badge{margin-bottom:5px}}.customer-select-table{max-height:300px;overflow-y:auto}
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
                               placeholder="Search orders..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="branch" class="form-label">Filter by Branch</label>
                        <select class="form-select" id="branch" name="branch">
                            <option value="">All Branches</option>
                            <?php while ($branch = $branchesResult->fetch_assoc()): ?>
                                <option value="<?= $branch['BranchCode'] ?>" 
                                    <?= (($_GET['branch'] ?? '') === $branch['BranchCode'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['BranchName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?= (($_GET['status'] ?? '') === 'Pending' ? 'selected' : '') ?>>Pending</option>
                            <option value="Complete" <?= (($_GET['status'] ?? '') === 'Complete' ? 'selected' : '') ?>>Complete</option>
                            <option value="Cancelled" <?= (($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : '') ?>>Cancelled</option>
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
                                        <span class="badge <?= match($order['Status']) {
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

                <nav aria-label="Orders pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" aria-label="Next">
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

    <!-- Add Order Modal (keep existing modal structure) -->

<script>document.addEventListener('DOMContentLoaded',function(){const e=document.getElementById('sidebar'),t=document.getElementById('mobileMenuToggle'),n=document.body;t&&t.addEventListener('click',function(t){t.stopPropagation(),e.classList.toggle('active'),n.classList.toggle('sidebar-open')}),document.addEventListener('click',function(t){window.innerWidth<=992&&!e.contains(t.target)&&(!t||t.target!==t)&&(e.classList.remove('active'),n.classList.remove('sidebar-open'))}),document.querySelectorAll('.sidebar-item').forEach(e=>{e.addEventListener('click',function(){window.innerWidth<=992&&(e.classList.remove('active'),n.classList.remove('sidebar-open'))})}),window.addEventListener('resize',function(){window.innerWidth>992&&(e.classList.remove('active'),n.classList.remove('sidebar-open'))});const a=document.getElementById('customerSearch');a&&a.addEventListener('input',function(){const e=this.value.toLowerCase();document.querySelectorAll('.customer-select-table tbody tr').forEach(t=>{const n=t.querySelector('td:nth-child(2)').textContent.toLowerCase(),a=t.querySelector('td:nth-child(3)').textContent.toLowerCase(),i=t.querySelector('td:nth-child(1)').textContent.toLowerCase();n.includes(e)||a.includes(e)||i.includes(e)?t.style.display="":t.style.display="none"})}),document.querySelectorAll('.select-customer').forEach(e=>{e.addEventListener('click',function(){const e=this.getAttribute('data-customer-id'),t=bootstrap.Modal.getInstance(document.getElementById('addOrderModal'));t.hide(),window.location.href=`orderCreate.php?customer_id=${e}`})})});</script>

</body>
</html>