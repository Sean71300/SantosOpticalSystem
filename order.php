<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'ActivityTracker.php'; 
include 'loginChecker.php';
<?php
// Fresh implementation of Orders Management page
// - Robust GET sanitization
// - Filter by search / branch / status (status computed at DB level)
// - Server-side pagination
// - AJAX order details endpoint handled inline

error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'setup.php';
include 'loginChecker.php';

// Helpers
function _get_scalar($key) {
    if (!isset($_GET[$key])) return '';
    $v = $_GET[$key];
    if (is_array($v)) return reset($v);
    return trim((string)$v);
}

function bind_params_stmt($stmt, $types, $params) {
    if (empty($params)) return;
    $refs = [];
    foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
    array_unshift($refs, $types);
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

$conn = connect();
if (!$conn) die('DB connection error');

// Pagination & filter inputs
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$search = _get_scalar('search');
$branch = _get_scalar('branch');
$status = _get_scalar('status');

// Fetch branches for the branch filter
$branches = [];
$brRes = $conn->query("SELECT BranchCode, BranchName FROM BranchMaster ORDER BY BranchName");
if ($brRes) while ($r = $brRes->fetch_assoc()) $branches[] = $r;

// Role-based branch scoping: employees/admins see only their branch
$roleId = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
if (in_array($roleId, [1,2], true)) {
    $sessionBranch = $_SESSION['branchcode'] ?? '';
    if (empty($sessionBranch)) {
        $st = $conn->prepare("SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1");
        if ($st && isset($_SESSION['username'])) {
            $st->bind_param('s', $_SESSION['username']);
            $st->execute();
            $res = $st->get_result();
            if ($row = $res->fetch_assoc()) { $_SESSION['branchcode'] = $row['BranchCode']; }
            $st->close();
        }
    }
    $branch = $_SESSION['branchcode'] ?? $branch;
}

// Build where conditions (for header-level filters)
$where = [];
$params = [];
$types = '';
if ($search !== '') {
    // search against Orderhdr_id or customer name
    $where[] = "(oh.Orderhdr_id LIKE ? OR c.CustomerName LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss';
}
if ($branch !== '') {
    $where[] = "oh.BranchCode = ?";
    $params[] = $branch; $types .= 's';
}
$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Map requested status to HAVING condition
$having = '';
switch ($status) {
    case 'Completed': $having = "SUM(od.Status != 'Completed') = 0"; break;
    case 'Cancelled': $having = "SUM(od.Status != 'Cancelled') = 0"; break;
    case 'Returned':  $having = "SUM(od.Status != 'Returned') = 0"; break;
    case 'Claimed':   $having = "SUM(od.Status != 'Claimed') = 0"; break;
    case 'Pending':
        $having = "NOT (SUM(od.Status != 'Completed') = 0 OR SUM(od.Status != 'Cancelled') = 0 OR SUM(od.Status != 'Returned') = 0 OR SUM(od.Status != 'Claimed') = 0)";
        break;
    default: $having = '';
}

// Count total matching orders
if ($having !== '') {
    $countSql = "SELECT COUNT(*) as total FROM (SELECT oh.Orderhdr_id FROM Order_hdr oh JOIN orderDetails od ON od.OrderHdr_id = oh.Orderhdr_id LEFT JOIN customer c ON c.CustomerID = oh.CustomerID $whereSql GROUP BY oh.Orderhdr_id HAVING $having) t";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) bind_params_stmt($countStmt, $types, $params);
    $countStmt->execute();
    $total = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
    $countStmt->close();
} else {
    $countSql = "SELECT COUNT(*) as total FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID = oh.CustomerID $whereSql";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) bind_params_stmt($countStmt, $types, $params);
    $countStmt->execute();
    $total = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
    $countStmt->close();
}

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page-1)*$perPage; }

// Fetch headers for current page
if ($having !== '') {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName
            FROM Order_hdr oh
            JOIN orderDetails od ON od.OrderHdr_id = oh.Orderhdr_id
            LEFT JOIN customer c ON c.CustomerID = oh.CustomerID
            $whereSql
            GROUP BY oh.Orderhdr_id
            HAVING $having
            ORDER BY oh.Created_dt DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $params2 = $params; $types2 = $types . 'ii';
    $params2[] = $perPage; $params2[] = $offset;
    if (!empty($params2)) bind_params_stmt($stmt, $types2, $params2);
    $stmt->execute();
    $res = $stmt->get_result();
    $headers = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName
            FROM Order_hdr oh
            LEFT JOIN customer c ON c.CustomerID = oh.CustomerID
            $whereSql
            ORDER BY oh.Created_dt DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $params2 = $params; $types2 = $types . 'ii';
    $params2[] = $perPage; $params2[] = $offset;
    if (!empty($params2)) bind_params_stmt($stmt, $types2, $params2);
    $stmt->execute();
    $res = $stmt->get_result();
    $headers = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Build orders array with details and computed status
$orders = [];
foreach ($headers as $h) {
    $orderId = $h['Orderhdr_id'];
    // fetch details
    $dStmt = $conn->prepare("SELECT od.Quantity, od.Status, od.ProductBranchID, p.Model, p.Price, p.CategoryType, b.BrandName FROM orderDetails od JOIN ProductBranchMaster pb ON od.ProductBranchID = pb.ProductBranchID JOIN productMstr p ON pb.ProductID = p.ProductID JOIN brandMaster b ON p.BrandID = b.BrandID WHERE od.OrderHdr_id = ?");
    $dStmt->bind_param('s', $orderId);
    $dStmt->execute();
    $details = $dStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $dStmt->close();

    // compute order status same as earlier logic
    $complete = $cancel = $returned = $claimed = 0; $totalItems = 0;
    foreach ($details as $det) {
        $totalItems++;
        switch ($det['Status']) {
            case 'Completed': $complete++; break;
            case 'Cancelled': $cancel++; break;
            case 'Returned': $returned++; break;
            case 'Claimed': $claimed++; break;
        }
    }
    if ($totalItems > 0) {
        if ($complete === $totalItems) $orderStatus = 'Completed';
        elseif ($cancel === $totalItems) $orderStatus = 'Cancelled';
        elseif ($returned === $totalItems) $orderStatus = 'Returned';
        elseif ($claimed === $totalItems) $orderStatus = 'Claimed';
        else $orderStatus = 'Pending';
    } else { $orderStatus = 'Pending'; }

    // fetch branch name
    $bStmt = $conn->prepare("SELECT BranchName FROM BranchMaster WHERE BranchCode = ?");
    $bStmt->bind_param('s', $h['BranchCode']);
    $bStmt->execute();
    $bName = $bStmt->get_result()->fetch_assoc()['BranchName'] ?? '';
    $bStmt->close();

    $orders[] = [
        'Orderhdr_id' => $orderId,
        'CustomerName' => $h['CustomerName'] ?? 'Unknown',
        'BranchName' => $bName,
        'Created_dt' => $h['Created_dt'],
        'Status' => $orderStatus,
        'Details' => $details
    ];
}

// If this is an AJAX request for details, respond JSON
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = $_GET['id'];
    foreach ($orders as $o) if ($o['Orderhdr_id'] == $id) { echo json_encode($o); exit; }
    echo json_encode(['error' => 'Order not found']); exit;
}

// HTML
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Orders Management</title>
<style>
body { background:#f5f7fa; padding-top:60px; }
.main-container { margin: 0 auto; max-width: 1200px; }
.card-round { border-radius: 10px; }
.badge-status { padding: .4em .6em; border-radius: .5rem; }
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-container mx-auto px-3">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h2><i class="fas fa-shopping-cart me-2"></i> Orders Management</h2>
        <button class="btn btn-primary" onclick="location.href='orderCreate.php'"><i class="fas fa-plus me-1"></i> Add New Order</button>
    </div>

    <div class="card card-round p-4 mb-4">
        <form method="get" action="order.php">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search orders or customers...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Branch</label>
                    <select name="branch" class="form-select">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $br): ?>
                            <option value="<?= htmlspecialchars($br['BranchCode']) ?>" <?= $branch == $br['BranchCode'] ? 'selected' : '' ?>><?= htmlspecialchars($br['BranchName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['Pending','Completed','Cancelled','Returned','Claimed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary"> <i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-round p-3 mb-4">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info"> <i class="fas fa-info-circle me-2"></i> No orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr><th>Order ID</th><th>Customer</th><th>Branch</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['Orderhdr_id']) ?></td>
                                <td><?= htmlspecialchars($o['CustomerName']) ?></td>
                                <td><?= htmlspecialchars($o['BranchName']) ?></td>
                                <td><?= date('M j, Y', strtotime($o['Created_dt'])) ?></td>
                                <td><span class="badge badge-status <?= strtolower($o['Status']) ?>"><?= htmlspecialchars($o['Status']) ?></span></td>
                                <td><button class="btn btn-sm btn-outline-primary" onclick="showDetails('<?= htmlspecialchars($o['Orderhdr_id']) ?>')">View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php
                        $base = ['search'=>$search,'branch'=>$branch,'status'=>$status];
                        if ($page>1):
                    ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($base,['page'=>$page-1])) ?>">&laquo;</a></li>
                    <?php endif; ?>
                    <?php for ($i=1;$i<=$totalPages;$i++): ?>
                        <li class="page-item <?= $i==$page ? 'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($base,['page'=>$i])) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page<$totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($base,['page'=>$page+1])) ?>">&raquo;</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Details modal (Bootstrap) -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orderDetailsBody">Loading...</div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDetails(id){
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const body = document.getElementById('orderDetailsBody');
    body.innerHTML = '<div class="text-center py-4">Loading...</div>';
    fetch(`order.php?action=details&id=${encodeURIComponent(id)}`)
      .then(r=>r.json())
      .then(data=>{
          if (data.error) { body.innerHTML = '<div class="alert alert-danger">'+data.error+'</div>'; return; }
          let html = `<div class="row"><div class="col-md-6"><h6>Order Info</h6><p><strong>ID:</strong> ${data.Orderhdr_id}</p><p><strong>Date:</strong> ${new Date(data.Created_dt).toLocaleString()}</p></div><div class="col-md-6"><h6>Customer</h6><p>${data.CustomerName}</p></div></div>`;
          html += '<h6 class="mt-3">Items</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Brand</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th></tr></thead><tbody>';
          data.Details.forEach(d=>{
              html += `<tr><td>${d.Model||'N/A'}</td><td>${d.BrandName||'N/A'}</td><td>${d.CategoryType||'N/A'}</td><td>₱${parseFloat(d.Price||0).toFixed(2)}</td><td>${d.Quantity||0}</td><td>${d.Status||''}</td></tr>`;
          });
          html += '</tbody></table></div>';
          body.innerHTML = html; modal.show();
      }).catch(e=>{ body.innerHTML = '<div class="alert alert-danger">Error loading details</div>'; });
}
</script>
</body>
</html>
    
    if (!empty($status)) {
        // Use a grouped query to count orders matching the computed status
        switch ($status) {
            case 'Completed':
                $having = "SUM(od.Status != 'Completed') = 0";
                break;
            case 'Cancelled':
                $having = "SUM(od.Status != 'Cancelled') = 0";
                break;
            case 'Returned':
                $having = "SUM(od.Status != 'Returned') = 0";
                break;
            case 'Claimed':
                $having = "SUM(od.Status != 'Claimed') = 0";
                break;
            default:
                $having = "NOT (SUM(od.Status != 'Completed') = 0 OR SUM(od.Status != 'Cancelled') = 0 OR SUM(od.Status != 'Returned') = 0 OR SUM(od.Status != 'Claimed') = 0)";
        }

        $countQuery = "SELECT COUNT(*) as total FROM (SELECT oh.Orderhdr_id FROM Order_hdr oh JOIN orderDetails od ON od.OrderHdr_id = oh.Orderhdr_id";
        if (!empty($where)) {
            $countQuery .= " WHERE " . implode(' AND ', $where);
        }
        $countQuery .= " GROUP BY oh.Orderhdr_id HAVING " . $having . ") as t";

        $countStmt = $conn->prepare($countQuery);
        $params2 = $params;
        if (!empty($params2)) {
            bind_params_stmt($countStmt, $types, $params2);
        }
        $countStmt->execute();
        $res = $countStmt->get_result()->fetch_assoc();
        $countStmt->close();
        return (int)($res['total'] ?? 0);
    }
    
    return $total;
}

function getAllBranches($conn) {
    $query = "SELECT BranchCode, BranchName FROM BranchMaster";
    $result = $conn->query($query);
    return $result;
}

// Handle order claiming

if (isset($_POST['return_order']) && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $conn = connect();
    
    // Get order details before cancellation
    $orderDetails = getOrderDetails($conn, $orderId);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update order status to Cancelled and activity code
        $updateQuery = "UPDATE orderDetails SET Status = 'Returned', ActivityCode = 8 WHERE OrderHdr_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $orderId); // Assuming activityCode is a string
        $stmt->execute();
        $stmt->close();
        
        // Restore product quantities
        foreach ($orderDetails as $detail) {
            $restoreQuery = "UPDATE ProductBranchMaster SET Stocks = Stocks + ? WHERE ProductBranchID = ?";
            $stmt = $conn->prepare($restoreQuery);
            $stmt->bind_param('ii', $detail['Quantity'], $detail['ProductBranchID']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Log the cancellation
        
        $LID=generate_LogsID();
        $CID=getCustomerID($conn, $orderId);
        $CName=getCustomerName($conn, $CID);
        $description = "#$orderId from customer ". $CName;
        $logQuery = "INSERT INTO Logs (LogsID,EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?,?, ?, 'order', 8, ?)";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param('iiis',$LID, $_SESSION['id'], $orderId, $description);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to refresh the page
        header("Location: order.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error cancelling order: " . $e->getMessage());
    }
}

// Handle order claiming
if (isset($_POST['claim_order'])) {
    $orderId = $_POST['order_id'];
    $conn = connect();
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update order status to Completed and activity code
        $updateQuery = "UPDATE orderDetails SET Status = 'Claimed', ActivityCode = 9 WHERE OrderHdr_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Log the completion
        $LID = generate_LogsID();
        $CID = getCustomerID($conn, $orderId);
        $CName = getCustomerName($conn, $CID);
        $description = "#$orderId for customer ". $CName;
        $logQuery = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 9, ?)";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param('iiis', $LID, $_SESSION['id'], $orderId, $description);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to refresh the page
        header("Location: order.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error completing order: " . $e->getMessage());
    }
}

// Handle order completion
if (isset($_POST['complete_order'])) {
    $orderId = $_POST['order_id'];
    $conn = connect();
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update order status to Completed and activity code
        $updateQuery = "UPDATE orderDetails SET Status = 'Completed', ActivityCode = 1 WHERE OrderHdr_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Log the completion
        $LID = generate_LogsID();
        $CID = getCustomerID($conn, $orderId);
        $CName = getCustomerName($conn, $CID);
        $description = "#$orderId for customer ". $CName;
        $logQuery = "INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 1, ?)";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param('iiis', $LID, $_SESSION['id'], $orderId, $description);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to refresh the page
        header("Location: order.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error completing order: " . $e->getMessage());
    }
}

// Handle order cancellation
if (isset($_POST['cancel_order']) && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $conn = connect();
    
    // Get order details before cancellation
    $orderDetails = getOrderDetails($conn, $orderId);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update order status to Cancelled and activity code
        $updateQuery = "UPDATE orderDetails SET Status = 'Cancelled', ActivityCode = 7 WHERE OrderHdr_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $orderId); // Assuming activityCode is a string
        $stmt->execute();
        $stmt->close();
        
        // Restore product quantities
        foreach ($orderDetails as $detail) {
            $restoreQuery = "UPDATE ProductBranchMaster SET Stocks = Stocks + ? WHERE ProductBranchID = ?";
            $stmt = $conn->prepare($restoreQuery);
            $stmt->bind_param('ii', $detail['Quantity'], $detail['ProductBranchID']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Log the cancellation
        
        $LID=generate_LogsID();
        $CID=getCustomerID($conn, $orderId);
        $CName=getCustomerName($conn, $CID);
        $description = "#$orderId for customer ". $CName;
        $logQuery = "INSERT INTO Logs (LogsID,EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?,?, ?, 'order', 7, ?)";
        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param('iiis',$LID, $_SESSION['id'], $orderId, $description);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to refresh the page
        header("Location: order.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error cancelling order: " . $e->getMessage());
    }
}

$ordersPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

// Ensure GET parameters are scalars (avoid array injection causing warnings)
function _get_scalar($key) {
    if (!isset($_GET[$key])) return '';
    $v = $_GET[$key];
    if (is_array($v)) return reset($v);
    return $v;
}

$search = _get_scalar('search');
$branch = _get_scalar('branch');
$status = _get_scalar('status');

// Enforce branch scoping for Admins (roleid = 1) and Employees (roleid = 2)
$roleId = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
$isRestrictedRole = ($roleId === 1 || $roleId === 2);
if ($isRestrictedRole) {
    // Prefer branch from session; if missing, fetch from DB using login name and cache in session
    $sessionBranch = $_SESSION['branchcode'] ?? '';
    if (empty($sessionBranch)) {
        $tmpConn = connect();
        if ($tmpConn) {
            $empStmt = $tmpConn->prepare("SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1");
            if ($empStmt && isset($_SESSION['username'])) {
                $empStmt->bind_param('s', $_SESSION['username']);
                $empStmt->execute();
                $empRes = $empStmt->get_result();
                if ($row = $empRes->fetch_assoc()) {
                    $_SESSION['branchcode'] = (string)$row['BranchCode'];
                    $sessionBranch = $_SESSION['branchcode'];
                }
                $empStmt->close();
            }
            $tmpConn->close();
        }
    }
    // Force branch filter to employee's branch regardless of query params
    $branch = $sessionBranch;
}

$conn = connect();

$orderHeaders = getOrderHeaders($conn, $search, $branch, $status, $ordersPerPage, $offset);
$totalOrders = countOrderHeaders($conn, $search, $branch, $status);
$totalPages = ceil($totalOrders / $ordersPerPage);

// Defensive fallback: if there are orders overall but the requested page returned
// no headers (possible edge-case when filters/pagination mismatch), reset to
// page 1 and fetch again so the user still sees results instead of "No orders found".
if (empty($orderHeaders) && $totalOrders > 0) {
    $currentPage = 1;
    $offset = 0;
    $orderHeaders = getOrderHeaders($conn, $search, $branch, $status, $ordersPerPage, $offset);
    // Recompute totalPages just in case
    $totalPages = ceil($totalOrders / $ordersPerPage);
}

$orders = [];
foreach ($orderHeaders as $header) {
    $orderId = $header['Orderhdr_id'];
    $details = getOrderDetails($conn, $orderId);
    
    $customerQuery = "SELECT CustomerName, CustomerContact, CustomerAddress FROM customer WHERE CustomerID = ?";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param('s', $header['CustomerID']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $totalAmount = 0;
    foreach ($details as $detail) {
        $totalAmount += $detail['Price'] * $detail['Quantity'];
    }
    
    $orders[] = [
        'Orderhdr_id' => $orderId,
        'Created_dt' => $header['Created_dt'],
        'CustomerName' => $header['CustomerName'],
        'CustomerContact' => $customer['CustomerContact'] ?? '',
        'CustomerAddress' => $customer['CustomerAddress'] ?? '',
        'CreatedBy' => getEmployeeName($conn, $header['Created_by']),
        'BranchName' => getBranchName($conn, $header['BranchCode']),
        'BranchLocation' => getBranchLocation($conn, $header['BranchCode']),
        'BranchContact' => getBranchContact($conn, $header['BranchCode']),
        'ItemCount' => count($details),
        'TotalAmount' => $totalAmount,
        'Status' => getOrderStatus($conn, $orderId),
        'Details' => $details
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
        .badge-returned {
            background-color: #fd7e14;
        }
        .badge-claimed {
            background-color: #17a2b8;
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
        .modal-footer .btn {
            margin-left: 5px;
            margin-right: 5px;
        }

        #completeOrderBtn {
            background-color: #28a745;
            border-color: #28a745;
        }

        #editOrderBtn {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .order-details-container {
            padding-bottom: 20px;
        }
        .order-details-footer {
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
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
            <input type="hidden" name="page" value="1">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search orders or customers..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <?php 
                    $roleLocal = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0; 
                    $isRestrictedLocal = ($roleLocal === 1 || $roleLocal === 2);
                ?>
                <?php if (!$isRestrictedLocal): ?>
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
                <?php else: ?>
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars(getBranchName(connect(), $_SESSION['branchcode'] ?? '')); ?>" disabled>
                </div>
                <?php endif; ?>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo ($status == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="Completed" <?php echo ($status == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($status == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                        <option value="Returned" <?php echo ($status == 'Returned' ? 'selected' : ''); ?>>Returned</option>
                        <option value="Claimed" <?php echo ($status == 'Claimed' ? 'selected' : ''); ?>>Claimed</option>
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
                                            'Returned' => 'badge-returned',
                                            'Claimed' => 'badge-claimed',
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
                    <?php
                        // Build canonical base params from current variables so pagination
                        // links always preserve the active filters (don't rely on raw 
                        // \\$_GET which may be modified elsewhere).
                        $baseParams = [
                            'search' => $search,
                            'branch' => $branch,
                            'status' => $status
                        ];
                    ?>
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($baseParams, ['page' => $currentPage - 1])); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($baseParams, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($baseParams, ['page' => $currentPage + 1])); ?>" aria-label="Next">
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
        </div>
    </div>
</div>

<form id="claimOrderForm" method="post" style="display: none;">
    <input type="hidden" name="claim_order" value="1">
    <input type="hidden" name="order_id" id="claimOrderId">
</form>
<form id="cancelOrderForm" method="post" style="display: none;">
    <input type="hidden" name="cancel_order" value="1">
    <input type="hidden" name="order_id" id="cancelOrderId">
</form>
<form id="completeOrderForm" method="post" style="display: none;">
    <input type="hidden" name="complete_order" value="1">
    <input type="hidden" name="order_id" id="completeOrderId">
</form>
<form id="returnOrderForm" method="post" style="display: none;">
    <input type="hidden" name="return_order" value="1">
    <input type="hidden" name="order_id" id="returnOrderId">
</form>

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
                const customerName = this.getAttribute('data-customer-name');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('addOrderModal'));
                modal.hide();
                
                window.location.href = `orderCreate.php?customer_id=${customerId}`;
            });
        });

        const ordersData = <?php echo json_encode($orders); ?>;
        
        document.querySelectorAll('.view-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const order = ordersData.find(o => o.Orderhdr_id == orderId);
                
                if (order) {
                    const modalBody = document.getElementById('orderDetailsContent');
                    
                    let html = `
                        <div class="order-details-container">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Order Information</h5>
                                    <p><strong>Order ID:</strong> ${order.Orderhdr_id}</p>
                                    <p><strong>Date Created:</strong> ${new Date(order.Created_dt).toLocaleString()}</p>
                                    <p><strong>Created By:</strong> ${order.CreatedBy}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Branch Information</h5>
                                    <p><strong>Branch:</strong> ${order.BranchName}</p>
                                    <p><strong>Location:</strong> ${order.BranchLocation}</p>
                                    <p><strong>Contact:</strong> ${order.BranchContact}</p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Customer Information</h5>
                                    <p><strong>Name:</strong> ${order.CustomerName}</p>
                                    <p><strong>Contact:</strong> ${order.CustomerContact}</p>
                                    <p><strong>Address:</strong> ${order.CustomerAddress}</p>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Order Items</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered order-details-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Brand</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                    order.Details.forEach(detail => {
                        const statusClass = detail.Status === 'Completed' ? 'badge-complete' : 
                                            detail.Status === 'Cancelled' ? 'badge-cancelled' :
                                            detail.Status === 'Returned' ? 'badge-returned' :
                                            detail.Status === 'Claimed' ? 'badge-claimed' : 'badge-pending';
                        
                        html += `
                            <tr>
                                <td>${detail.Model || 'N/A'}</td>
                                <td>${detail.BrandName || 'N/A'}</td>
                                <td>${detail.CategoryType || 'N/A'}</td>
                                <td>₱${detail.Price.toFixed(2)}</td>
                                <td>${detail.Quantity}</td>
                                <td><span class="badge ${statusClass}">${detail.Status}</span></td>
                            </tr>`;
                    });

                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>`;
                    
                    // Only show action buttons if order is pending
                    if (order.Status === 'Pending') {
                        html += `
                        <div class="order-details-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-success me-2" id="completeOrderBtn">
                                <i class="fas fa-check-circle me-1"></i> Complete Order
                            </button>
                            <button type="button" class="btn btn-danger me-2" id="cancelOrderBtn">
                                <i class="fas fa-times-circle me-1"></i> Cancel Order
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                        </div>`;
                    } else if (order.Status === 'Completed') {
                        html += `
                        <div class="order-details-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-primary me-2" id="claimOrderBtn">
                                <i class="fas fa-check-circle me-1"></i> Claim Order
                            </button>
                            <button type="button" class="btn btn-danger me-2" id="cancelOrderBtn">
                                <i class="fas fa-times-circle me-1"></i> Cancel Order
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                        </div>`;
                    } else if (order.Status === 'Claimed') {
                        html += `
                        <div class="order-details-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-warning me-2" id="returnOrderBtn">
                                <i class="fas fa-undo me-1"></i> Return
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                        </div>`;
                    } else {
                        html += `
                        <div class="order-details-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                        </div>`;
                    }
                    
                    modalBody.innerHTML = html;

                    const claimBtn = document.getElementById('claimOrderBtn');
                    if (claimBtn) {
                        claimBtn.addEventListener('click', function() {
                            if (confirm('This Product will now be claimed by the customer.')) {
                                document.getElementById('claimOrderId').value = order.Orderhdr_id;
                                document.getElementById('claimOrderForm').submit();
                            }
                        });
                    }

                    const completeBtn = document.getElementById('completeOrderBtn');
                    if (completeBtn) {
                        completeBtn.addEventListener('click', function() {
                            if (confirm('Are you sure you want to mark this order as completed?')) {
                                document.getElementById('completeOrderId').value = order.Orderhdr_id;
                                document.getElementById('completeOrderForm').submit();
                            }
                        });
                    }
                    // Add event listener for cancel button if it exists
                    const cancelBtn = document.getElementById('cancelOrderBtn');
                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', function() {
                            if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                                document.getElementById('cancelOrderId').value = order.Orderhdr_id;
                                document.getElementById('cancelOrderForm').submit();
                            }
                        });
                    }
                    const returnBtn = document.getElementById('returnOrderBtn');
                    if (returnBtn) {
                        returnBtn.addEventListener('click', function() {
                            if (confirm('The Product will now be returned by the customer.')) {
                                document.getElementById('returnOrderId').value = order.Orderhdr_id;
                                document.getElementById('returnOrderForm').submit();
                            }
                        });
                    }
                } else {
                    document.getElementById('orderDetailsContent').innerHTML = `
                        <div class="alert alert-danger">Order details not found.</div>`;
                }
            });
        });
    });
</script>
</body>
</html>