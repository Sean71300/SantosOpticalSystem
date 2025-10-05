<?php
// Orders Management (clean implementation)
// - Session before auth
// - SQL-level status filter (HAVING)
// - Role-based branch scoping (Admin/Employee restricted to own branch)
// - Pagination + filter preservation
// - AJAX details endpoint fetches by ID directly (works even if not on current page)

error_reporting(E_ALL);
ini_set('display_errors', 1);

function _order_log($msg){
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'order_debug.log', '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
}

set_error_handler(function($severity, $message, $file, $line){ _order_log("PHP Error: $message in $file:$line [$severity]"); return false; });
set_exception_handler(function($ex){ _order_log('Uncaught: '.$ex->getMessage()); http_response_code(500); echo '<h3>Server error</h3>'; exit; });
register_shutdown_function(function(){ $e = error_get_last(); if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_COMPILE_ERROR,E_CORE_ERROR])){ _order_log('Shutdown: '.json_encode($e)); http_response_code(500); echo '<h3>Server shutdown error</h3>'; }});

if (session_status() === PHP_SESSION_NONE) { @session_start(); }
require_once __DIR__.'/setup.php';
require_once __DIR__.'/loginChecker.php';

$conn = connect();
if (!$conn) { die('DB connection error'); }

function _get_scalar($key){ if(!isset($_GET[$key])) return ''; $v=$_GET[$key]; return is_array($v)? reset($v): trim((string)$v); }
function _bind(&$stmt,$types,&$params){ if(!$params) return; $refs=[]; foreach($params as $k=>$v){ $refs[$k]=&$params[$k]; } array_unshift($refs,$types); return call_user_func_array([$stmt,'bind_param'],$refs); }

// ---- Order actions (Complete / Claim / Cancel / Return) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    if ($orderId > 0) {
        // Enforce branch scope for Admin/Employee
        $ridAct = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
        $restrictedAct = in_array($ridAct, [1,2], true);
        $allowed = true;
        if ($restrictedAct && !empty($_SESSION['branchcode'])) {
            $chk = $conn->prepare('SELECT BranchCode FROM Order_hdr WHERE Orderhdr_id = ?');
            $chk->bind_param('i', $orderId); $chk->execute();
            $bc = $chk->get_result()->fetch_assoc()['BranchCode'] ?? null; $chk->close();
            if ($bc !== $_SESSION['branchcode']) { $allowed = false; }
        }
        if ($allowed) {
            // Helper to fetch details for stock restore
            $fetchDetails = function($cid) use ($conn) {
                $s = $conn->prepare('SELECT Quantity, ProductBranchID FROM orderDetails WHERE OrderHdr_id = ?');
                $s->bind_param('i', $cid); $s->execute(); $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close(); return $rows;
            };
            try {
                $conn->begin_transaction();
                if (isset($_POST['complete_order'])) {
                    $u = $conn->prepare("UPDATE orderDetails SET Status='Completed', ActivityCode=1 WHERE OrderHdr_id=?");
                    $u->bind_param('i', $orderId); $u->execute(); $u->close();
                    // Log
                    if (function_exists('generate_LogsID')) {
                        $LID = generate_LogsID();
                        $CID = 0; $q=$conn->prepare('SELECT CustomerID FROM Order_hdr WHERE Orderhdr_id=?'); $q->bind_param('i',$orderId); $q->execute(); $r=$q->get_result()->fetch_assoc(); $q->close(); $CID = (int)($r['CustomerID']??0);
                        $CName = function_exists('getCustomerName') ? getCustomerName($conn, $CID) : '';
                        $desc = "#$orderId for customer ".$CName;
                        $lg = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 1, ?)");
                        $emp = (int)($_SESSION['id'] ?? 0); $lg->bind_param('iiis', $LID, $emp, $orderId, $desc); $lg->execute(); $lg->close();
                    }
                } elseif (isset($_POST['claim_order'])) {
                    $u = $conn->prepare("UPDATE orderDetails SET Status='Claimed', ActivityCode=9 WHERE OrderHdr_id=?");
                    $u->bind_param('i', $orderId); $u->execute(); $u->close();
                    if (function_exists('generate_LogsID')) {
                        $LID = generate_LogsID();
                        $CID = 0; $q=$conn->prepare('SELECT CustomerID FROM Order_hdr WHERE Orderhdr_id=?'); $q->bind_param('i',$orderId); $q->execute(); $r=$q->get_result()->fetch_assoc(); $q->close(); $CID = (int)($r['CustomerID']??0);
                        $CName = function_exists('getCustomerName') ? getCustomerName($conn, $CID) : '';
                        $desc = "#$orderId for customer ".$CName;
                        $lg = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 9, ?)");
                        $emp = (int)($_SESSION['id'] ?? 0); $lg->bind_param('iiis', $LID, $emp, $orderId, $desc); $lg->execute(); $lg->close();
                    }
                } elseif (isset($_POST['cancel_order'])) {
                    $u = $conn->prepare("UPDATE orderDetails SET Status='Cancelled', ActivityCode=7 WHERE OrderHdr_id=?");
                    $u->bind_param('i', $orderId); $u->execute(); $u->close();
                    // Restore stocks
                    foreach ($fetchDetails($orderId) as $d) {
                        $rs = $conn->prepare('UPDATE ProductBranchMaster SET Stocks = Stocks + ? WHERE ProductBranchID = ?');
                        $qty=(int)$d['Quantity']; $pbid=(int)$d['ProductBranchID']; $rs->bind_param('ii', $qty, $pbid); $rs->execute(); $rs->close();
                    }
                    if (function_exists('generate_LogsID')) {
                        $LID = generate_LogsID();
                        $CID = 0; $q=$conn->prepare('SELECT CustomerID FROM Order_hdr WHERE Orderhdr_id=?'); $q->bind_param('i',$orderId); $q->execute(); $r=$q->get_result()->fetch_assoc(); $q->close(); $CID = (int)($r['CustomerID']??0);
                        $CName = function_exists('getCustomerName') ? getCustomerName($conn, $CID) : '';
                        $desc = "#$orderId for customer ".$CName;
                        $lg = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 7, ?)");
                        $emp = (int)($_SESSION['id'] ?? 0); $lg->bind_param('iiis', $LID, $emp, $orderId, $desc); $lg->execute(); $lg->close();
                    }
                } elseif (isset($_POST['return_order'])) {
                    $u = $conn->prepare("UPDATE orderDetails SET Status='Returned', ActivityCode=8 WHERE OrderHdr_id=?");
                    $u->bind_param('i', $orderId); $u->execute(); $u->close();
                    // Restore stocks on return
                    foreach ($fetchDetails($orderId) as $d) {
                        $rs = $conn->prepare('UPDATE ProductBranchMaster SET Stocks = Stocks + ? WHERE ProductBranchID = ?');
                        $qty=(int)$d['Quantity']; $pbid=(int)$d['ProductBranchID']; $rs->bind_param('ii', $qty, $pbid); $rs->execute(); $rs->close();
                    }
                    if (function_exists('generate_LogsID')) {
                        $LID = generate_LogsID();
                        $CID = 0; $q=$conn->prepare('SELECT CustomerID FROM Order_hdr WHERE Orderhdr_id=?'); $q->bind_param('i',$orderId); $q->execute(); $r=$q->get_result()->fetch_assoc(); $q->close(); $CID = (int)($r['CustomerID']??0);
                        $CName = function_exists('getCustomerName') ? getCustomerName($conn, $CID) : '';
                        $desc = "#$orderId from customer ".$CName;
                        $lg = $conn->prepare("INSERT INTO Logs (LogsID, EmployeeID, TargetID, TargetType, ActivityCode, Description) VALUES (?, ?, ?, 'order', 8, ?)");
                        $emp = (int)($_SESSION['id'] ?? 0); $lg->bind_param('iiis', $LID, $emp, $orderId, $desc); $lg->execute(); $lg->close();
                    }
                }
                $conn->commit();
            } catch (Throwable $t) {
                $conn->rollback(); _order_log('Action error: '.$t->getMessage());
            }
        }
    }
    header('Location: order.php');
    exit;
}

// Inputs
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;
$search = _get_scalar('search');
$branch = _get_scalar('branch');
$status = _get_scalar('status');

// Role-based branch scope (1=Admin,2=Employee)
$rid = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
$restrictedRole = in_array($rid,[1,2],true);
if ($restrictedRole) {
    // Try to populate session branch from multiple reliable sources
    if (empty($_SESSION['branchcode'])) {
        // 1) Lookup by logged-in employee ID, if available
        if (!empty($_SESSION['id'])) {
            $st = $conn->prepare('SELECT BranchCode FROM employee WHERE EmployeeID = ? LIMIT 1');
            $st->bind_param('i', $_SESSION['id']);
            if ($st->execute()) { $r = $st->get_result()->fetch_assoc(); if ($r && !empty($r['BranchCode'])) $_SESSION['branchcode'] = $r['BranchCode']; }
            $st->close();
        }
        // 2) Fallback: lookup by username/login name
        if (empty($_SESSION['branchcode']) && !empty($_SESSION['username'])) {
            $st = $conn->prepare('SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1');
            $st->bind_param('s', $_SESSION['username']);
            if ($st->execute()) { $r = $st->get_result()->fetch_assoc(); if ($r && !empty($r['BranchCode'])) $_SESSION['branchcode'] = $r['BranchCode']; }
            $st->close();
        }
    }
    $branch = $_SESSION['branchcode'] ?? $branch;
    if (empty($branch)) { _order_log('Warning: restricted role has no BranchCode in session and lookups failed.'); }
}

// Resolve a display name for the selected/locked branch (used to show a static control for Admin/Employee)
$branchDisplayName = '';
if (!empty($branch)) {
    $bnStmt = $conn->prepare('SELECT BranchName FROM BranchMaster WHERE BranchCode = ? LIMIT 1');
    $bnStmt->bind_param('s', $branch);
    if ($bnStmt->execute()) { $branchDisplayName = ($bnStmt->get_result()->fetch_assoc()['BranchName'] ?? ''); }
    $bnStmt->close();
}

// Branches list
$branches = [];
if ($rs = $conn->query('SELECT BranchCode, BranchName FROM BranchMaster ORDER BY BranchName')) { while($row=$rs->fetch_assoc()) $branches[]=$row; }

// Canonical base query params for building links/redirects (compute AFTER branch scoping)
$baseQuery = ['search'=>$search,'branch'=>$branch,'status'=>$status];

// Where
$where=[]; $params=[]; $types='';
if ($search !== '') { $where[]='(oh.Orderhdr_id LIKE ? OR c.CustomerName LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; $types.='ss'; }
if ($branch !== '') { $where[]='oh.BranchCode = ?'; $params[]=$branch; $types.='s'; }
$whereSql = $where? 'WHERE '.implode(' AND ',$where): '';

// HAVING for status
$having='';
if ($status==='Completed') $having = "SUM(od.Status != 'Completed') = 0";
elseif ($status==='Cancelled') $having = "SUM(od.Status != 'Cancelled') = 0";
elseif ($status==='Returned') $having = "SUM(od.Status != 'Returned') = 0";
elseif ($status==='Claimed') $having = "SUM(od.Status != 'Claimed') = 0";
elseif ($status==='Pending') $having = "NOT (SUM(od.Status != 'Completed') = 0 OR SUM(od.Status != 'Cancelled') = 0 OR SUM(od.Status != 'Returned') = 0 OR SUM(od.Status != 'Claimed') = 0)";

// Count
if ($having!=='') {
    $countSql = "SELECT COUNT(*) total FROM (SELECT oh.Orderhdr_id FROM Order_hdr oh JOIN orderDetails od ON od.OrderHdr_id=oh.Orderhdr_id LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql GROUP BY oh.Orderhdr_id HAVING $having) t";
    $cs = $conn->prepare($countSql); _bind($cs,$types,$params); $cs->execute(); $total=(int)($cs->get_result()->fetch_assoc()['total']??0); $cs->close();
} else {
    $countSql = "SELECT COUNT(*) total FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql";
    $cs = $conn->prepare($countSql); _bind($cs,$types,$params); $cs->execute(); $total=(int)($cs->get_result()->fetch_assoc()['total']??0); $cs->close();
}
$totalPages = max(1, (int)ceil($total/$perPage));
if ($page>$totalPages){
    $page=$totalPages; $offset=($page-1)*$perPage;
}
// Debug trace to help diagnose pagination mismatches on user reports
_order_log("REQ page=$page total=$total totalPages=$totalPages offset=$offset search='".str_replace(["\n","\r"],' ', $search)."' branch='".$branch."' status='".$status."' having=".($having!==''?'1':'0'));

// Headers
// Use a stable ordering with a tiebreaker
$orderBy = "ORDER BY oh.Created_dt DESC, oh.Orderhdr_id DESC";
if ($having!=='') {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName FROM Order_hdr oh JOIN orderDetails od ON od.OrderHdr_id=oh.Orderhdr_id LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql GROUP BY oh.Orderhdr_id HAVING $having $orderBy LIMIT ".(int)$offset.", ".(int)$perPage;
} else {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql $orderBy LIMIT ".(int)$offset.", ".(int)$perPage;
}
$stmt = $conn->prepare($sql); $p=$params; $t=$types; _bind($stmt,$t,$p); $stmt->execute(); $headers=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

// If we somehow got an empty page while there are results, clamp to last page
if (!$headers && $total>0 && $page>1) {
    _order_log("Empty page $page with total=$total; redirecting to last page=$totalPages");
    header('Location: order.php?'.http_build_query(array_merge($baseQuery,['page'=>$totalPages])));
    exit;
}

// AJAX: details by id (independent of current page)
if (isset($_GET['action']) && $_GET['action']==='details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = trim($_GET['id']);
    // Enforce branch scope for restricted roles
    $sqlH = 'SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, c.CustomerName FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID=oh.CustomerID WHERE oh.Orderhdr_id=?';
    if ($restrictedRole && !empty($_SESSION['branchcode'])) { $sqlH .= ' AND oh.BranchCode=?'; }
    $st = $conn->prepare($sqlH);
    if ($restrictedRole && !empty($_SESSION['branchcode'])) { $st->bind_param('ss',$id,$_SESSION['branchcode']); } else { $st->bind_param('s',$id); }
    $st->execute(); $hdr=$st->get_result()->fetch_assoc(); $st->close();
    if(!$hdr){ echo json_encode(['error'=>'Order not found']); exit; }

    $sd = $conn->prepare("SELECT od.Quantity, od.Status, od.ProductBranchID, p.Model,
        CAST(REPLACE(REPLACE(REPLACE(p.Price, '₱', ''), ',', ''), 'PHP', '') AS DECIMAL(12,2)) AS Price,
        p.CategoryType, b.BrandName
        FROM orderDetails od
        JOIN ProductBranchMaster pb ON od.ProductBranchID=pb.ProductBranchID
        JOIN productMstr p ON pb.ProductID=p.ProductID
        JOIN brandMaster b ON p.BrandID=b.BrandID
        WHERE od.OrderHdr_id=?");
    $sd->bind_param('s',$id); $sd->execute(); $details=$sd->get_result()->fetch_all(MYSQLI_ASSOC); $sd->close();
    // Normalize numeric fields to avoid NaN on client
    foreach ($details as &$d) { $d['Price'] = (float)($d['Price'] ?? 0); $d['Quantity'] = (int)($d['Quantity'] ?? 0); }
    unset($d);

    $totalItems=count($details); $c=$x=$rtn=$clm=0; foreach($details as $d){ if($d['Status']==='Completed')$c++; elseif($d['Status']==='Cancelled')$x++; elseif($d['Status']==='Returned')$rtn++; elseif($d['Status']==='Claimed')$clm++; }
    $orderStatus = $totalItems>0 ? ($c===$totalItems?'Completed':($x===$totalItems?'Cancelled':($rtn===$totalItems?'Returned':($clm===$totalItems?'Claimed':'Pending')))) : 'Pending';

    $bn=''; $sb=$conn->prepare('SELECT BranchName FROM BranchMaster WHERE BranchCode=?'); $sb->bind_param('s',$hdr['BranchCode']); $sb->execute(); $bn=$sb->get_result()->fetch_assoc()['BranchName']??''; $sb->close();

    echo json_encode([
        'Orderhdr_id'=>$hdr['Orderhdr_id'],
        'CustomerName'=>$hdr['CustomerName']??'Unknown',
        'BranchName'=>$bn,
        'Created_dt'=>$hdr['Created_dt'],
        'Status'=>$orderStatus,
        'Details'=>$details
    ]); exit;
}

// Build orders for table (lightweight)
$orders=[];
foreach($headers as $h){
    $id=$h['Orderhdr_id'];
    $sd=$conn->prepare('SELECT od.Quantity, od.Status, od.ProductBranchID, p.Model, p.Price, p.CategoryType, b.BrandName FROM orderDetails od JOIN ProductBranchMaster pb ON od.ProductBranchID=pb.ProductBranchID JOIN productMstr p ON pb.ProductID=p.ProductID JOIN brandMaster b ON p.BrandID=b.BrandID WHERE od.OrderHdr_id=?');
    $sd->bind_param('s',$id); $sd->execute(); $details=$sd->get_result()->fetch_all(MYSQLI_ASSOC); $sd->close();
    $totalItems=count($details); $c=$x=$rtn=$clm=0; foreach($details as $d){ if($d['Status']==='Completed')$c++; elseif($d['Status']==='Cancelled')$x++; elseif($d['Status']==='Returned')$rtn++; elseif($d['Status']==='Claimed')$clm++; }
    $statusComputed = $totalItems>0 ? ($c===$totalItems?'Completed':($x===$totalItems?'Cancelled':($rtn===$totalItems?'Returned':($clm===$totalItems?'Claimed':'Pending')))) : 'Pending';
    $sb=$conn->prepare('SELECT BranchName FROM BranchMaster WHERE BranchCode=?'); $sb->bind_param('s',$h['BranchCode']); $sb->execute(); $bn=$sb->get_result()->fetch_assoc()['BranchName']??''; $sb->close();
    $orders[]=[ 'Orderhdr_id'=>$id, 'CustomerName'=>$h['CustomerName']??'Unknown', 'BranchName'=>$bn, 'Created_dt'=>$h['Created_dt'], 'Status'=>$statusComputed ];
}

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
/* Make the main content occupy the full width beside the fixed 250px sidebar */
.main-content, .main-container { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
@media (max-width: 992px) { .main-content, .main-container { margin-left: 0; width: 100%; } }
.card-round { border-radius: 10px; }
.badge-status { padding: .4em .6em; border-radius: .5rem; }
/* Explicit status colors */
.badge-status.pending { background-color: #ffc107; color: #000; }
.badge-status.completed { background-color: #198754; }
.badge-status.cancelled { background-color: #dc3545; }
.badge-status.returned { background-color: #fd7e14; }
.badge-status.claimed { background-color: #0dcaf0; color: #000; }
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h2><i class="fas fa-shopping-cart me-2"></i> Orders Management</h2>
    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="fas fa-plus me-1"></i> Add New Order</button>
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
                    <?php if ($restrictedRole): ?>
                        <input class="form-control" value="<?= htmlspecialchars($branchDisplayName ?: $branch) ?>" readonly>
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($branch) ?>">
                        <div class="form-text">Locked to your branch</div>
                    <?php else: ?>
                        <select name="branch" class="form-select">
                            <option value="">All Branches</option>
                            <?php foreach($branches as $br): ?>
                            <option value="<?= htmlspecialchars($br['BranchCode']) ?>" <?= $branch==$br['BranchCode']?'selected':'' ?>><?= htmlspecialchars($br['BranchName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach(['Pending','Completed','Cancelled','Returned','Claimed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-round p-3 mb-4">
        <?php if(!$headers): ?>
            <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> No orders found.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light"><tr><th>Order ID</th><th>Customer</th><th>Branch</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($orders as $o): ?>
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
                <nav class="mt-3"><ul class="pagination justify-content-center">
                        <?php if($page>1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($baseQuery,['page'=>$page-1])) ?>">&laquo;</a></li>
                        <?php endif; for($i=1;$i<=$totalPages;$i++): ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($baseQuery,['page'=>$i])) ?>"><?= $i ?></a></li>
                        <?php endfor; if($page<$totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($baseQuery,['page'=>$page+1])) ?>">&raquo;</a></li>
                        <?php endif; ?>
                </ul></nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add Order modal: select customer -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Create New Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Search Customer</label>
                <input type="text" class="form-control" id="customerSearch" placeholder="Search by name or contact number...">
            </div>
            <div class="table-responsive" style="max-height:360px; overflow:auto;">
                <table class="table table-hover" id="customerTable">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Contact</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php
                        $custRs = $conn->query("SELECT CustomerID, CustomerName, CustomerContact FROM customer ORDER BY CustomerName LIMIT 300");
                        if ($custRs) { while($c = $custRs->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($c['CustomerID']) ?></td>
                                <td><?= htmlspecialchars($c['CustomerName']) ?></td>
                                <td><?= htmlspecialchars($c['CustomerContact']) ?></td>
                                <td><button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='orderCreate.php?customer_id=<?= urlencode($c['CustomerID']) ?>'">Select</button></td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div></div>
</div>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Order Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="orderDetailsBody">Loading...</div>
    </div></div>
</div>

<!-- Hidden forms for actions -->
<form id="completeOrderForm" method="post" style="display:none"><input type="hidden" name="complete_order" value="1"><input type="hidden" name="order_id" id="completeOrderId"></form>
<form id="cancelOrderForm" method="post" style="display:none"><input type="hidden" name="cancel_order" value="1"><input type="hidden" name="order_id" id="cancelOrderId"></form>
<form id="claimOrderForm" method="post" style="display:none"><input type="hidden" name="claim_order" value="1"><input type="hidden" name="order_id" id="claimOrderId"></form>
<form id="returnOrderForm" method="post" style="display:none"><input type="hidden" name="return_order" value="1"><input type="hidden" name="order_id" id="returnOrderId"></form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDetails(id){
  const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
  const body = document.getElementById('orderDetailsBody');
  body.innerHTML = '<div class="text-center py-4">Loading...</div>';
  fetch('order.php?action=details&id='+encodeURIComponent(id))
    .then(r=>r.json())
    .then(data=>{
      if(data.error){ body.innerHTML='<div class="alert alert-danger">'+data.error+'</div>'; modal.show(); return; }
      let html = `<div class="row">
        <div class="col-md-6"><h6>Order Info</h6><p><strong>ID:</strong> ${data.Orderhdr_id}</p><p><strong>Date:</strong> ${new Date(data.Created_dt).toLocaleString()}</p></div>
        <div class="col-md-6"><h6>Customer</h6><p>${data.CustomerName}</p></div>
      </div>`;
      html += '<h6 class="mt-3">Items</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Brand</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th></tr></thead><tbody>';
            (data.Details||[]).forEach(d=>{ const price = Number.isFinite(d.Price)? d.Price : parseFloat(d.Price||0); html += `<tr><td>${d.Model||'N/A'}</td><td>${d.BrandName||'N/A'}</td><td>${d.CategoryType||'N/A'}</td><td>₱${(price||0).toFixed(2)}</td><td>${d.Quantity||0}</td><td>${d.Status||''}</td></tr>`; });
            html += '</tbody></table></div>';

            // Action buttons by status
            const status = (data.Status||'').toLowerCase();
            if (status === 'pending') {
                html += `<div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-success" id="btnComplete"><i class="fas fa-check-circle me-1"></i> Complete Order</button>
                    <button type="button" class="btn btn-danger" id="btnCancel"><i class="fas fa-times-circle me-1"></i> Cancel Order</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>`;
            } else if (status === 'completed') {
                html += `<div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-primary" id="btnClaim"><i class="fas fa-box-open me-1"></i> Claim Order</button>
                    <button type="button" class="btn btn-danger" id="btnCancel"><i class="fas fa-times-circle me-1"></i> Cancel Order</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>`;
            } else if (status === 'claimed') {
                html += `<div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-warning" id="btnReturn"><i class="fas fa-undo me-1"></i> Return</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>`;
            } else {
                html += `<div class="d-flex justify-content-end mt-3"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>`;
            }

            body.innerHTML = html; modal.show();

            // Wire buttons
            const oid = data.Orderhdr_id;
            const bComplete = document.getElementById('btnComplete');
            const bCancel = document.getElementById('btnCancel');
            const bClaim = document.getElementById('btnClaim');
            const bReturn = document.getElementById('btnReturn');
            if (bComplete) bComplete.addEventListener('click', ()=>{ if (confirm('Mark this order as Completed?')) { document.getElementById('completeOrderId').value=oid; document.getElementById('completeOrderForm').submit(); }});
            if (bCancel) bCancel.addEventListener('click', ()=>{ if (confirm('Cancel this order? This will restore stocks.')) { document.getElementById('cancelOrderId').value=oid; document.getElementById('cancelOrderForm').submit(); }});
            if (bClaim) bClaim.addEventListener('click', ()=>{ if (confirm('Mark this order as Claimed?')) { document.getElementById('claimOrderId').value=oid; document.getElementById('claimOrderForm').submit(); }});
            if (bReturn) bReturn.addEventListener('click', ()=>{ if (confirm('Return this order? This will restore stocks.')) { document.getElementById('returnOrderId').value=oid; document.getElementById('returnOrderForm').submit(); }});
    })
    .catch(()=>{ body.innerHTML='<div class="alert alert-danger">Error loading details</div>'; modal.show(); });
}

// Client-side filter for customers in the Add Order modal
document.addEventListener('DOMContentLoaded', ()=>{
    const input = document.getElementById('customerSearch');
    if (!input) return;
    input.addEventListener('input', ()=>{
        const term = input.value.toLowerCase();
        document.querySelectorAll('#customerTable tbody tr').forEach(tr=>{
            const id = tr.children[0].textContent.toLowerCase();
            const name = tr.children[1].textContent.toLowerCase();
            const contact = tr.children[2].textContent.toLowerCase();
            tr.style.display = (id.includes(term) || name.includes(term) || contact.includes(term)) ? '' : 'none';
        });
    });
});
</script>
</body>
</html>