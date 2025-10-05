<?php
// Fresh implementation of Orders Management page
// - Robust GET sanitization
// - Filter by search / branch / status (status computed at DB level)
// - Server-side pagination
// - AJAX order details endpoint handled inline

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple runtime logger to capture fatal errors that cause HTTP 500.
function _order_log($msg) {
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $file = $logDir . DIRECTORY_SEPARATOR . 'order_debug.log';
    $time = date('Y-m-d H:i:s');
    @file_put_contents($file, "[{$time}] " . $msg . PHP_EOL, FILE_APPEND);
}

set_error_handler(function($severity, $message, $file, $line) {
    _order_log("PHP Error: {$message} in {$file}:{$line} (severity={$severity})");
    // let normal error handling continue
    return false;
});

set_exception_handler(function($ex){
    _order_log("Uncaught Exception: " . $ex->getMessage() . " in " . $ex->getFile() . ":" . $ex->getLine());
    http_response_code(500);
    echo "<h3>Server error</h3><p>Check logs/order_debug.log for details.</p>";
    exit;
});

register_shutdown_function(function(){
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        _order_log("Shutdown error: " . json_encode($err));
        http_response_code(500);
        echo "<h3>Server shutdown error</h3><p>Check logs/order_debug.log for details.</p>";
        exit;
    }
});
// Ensure session is started BEFORE auth checks so $_SESSION is available
if (session_status() === PHP_SESSION_NONE) { @session_start(); }
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

// If this is an AJAX request for details, respond JSON (fetch by id directly)
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = trim((string)$_GET['id']);
    $ridAjax = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
    $restricted = in_array($ridAjax, [1,2], true);
    $branchAjax = $_SESSION['branchcode'] ?? '';

    $sqlH = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, c.CustomerName FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID = oh.CustomerID WHERE oh.Orderhdr_id = ?";
    if ($restricted && $branchAjax !== '') { $sqlH .= " AND oh.BranchCode = ?"; }
    $stmtH = $conn->prepare($sqlH);
    if ($restricted && $branchAjax !== '') { $stmtH->bind_param('ss', $id, $branchAjax); } else { $stmtH->bind_param('s', $id); }
    $stmtH->execute();
    $hdr = $stmtH->get_result()->fetch_assoc();
    $stmtH->close();
    if (!$hdr) { echo json_encode(['error' => 'Order not found']); exit; }

    $stmtD = $conn->prepare("SELECT od.Quantity, od.Status, od.ProductBranchID, p.Model, p.Price, p.CategoryType, b.BrandName FROM orderDetails od JOIN ProductBranchMaster pb ON od.ProductBranchID = pb.ProductBranchID JOIN productMstr p ON pb.ProductID = p.ProductID JOIN brandMaster b ON p.BrandID = b.BrandID WHERE od.OrderHdr_id = ?");
    $stmtD->bind_param('s', $id);
    $stmtD->execute();
    $details = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtD->close();

    $complete = $cancel = $returned = $claimed = 0; $totalItems = 0;
    foreach ($details as $det) { $totalItems++; if($det['Status']==='Completed') $complete++; elseif($det['Status']==='Cancelled') $cancel++; elseif($det['Status']==='Returned') $returned++; elseif($det['Status']==='Claimed') $claimed++; }
    $orderStatus = $totalItems>0 ? ($complete===$totalItems?'Completed':($cancel===$totalItems?'Cancelled':($returned===$totalItems?'Returned':($claimed===$totalItems?'Claimed':'Pending')))) : 'Pending';

    $stmtB = $conn->prepare("SELECT BranchName FROM BranchMaster WHERE BranchCode = ?");
    $stmtB->bind_param('s', $hdr['BranchCode']);
    $stmtB->execute();
    $bName = $stmtB->get_result()->fetch_assoc()['BranchName'] ?? '';
    $stmtB->close();

    echo json_encode([
        'Orderhdr_id' => $hdr['Orderhdr_id'],
        'CustomerName' => $hdr['CustomerName'] ?? 'Unknown',
        'BranchName' => $bName,
        'Created_dt' => $hdr['Created_dt'],
        'Status' => $orderStatus,
        'Details' => $details
    ]);
    exit;
}

// HTML
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
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