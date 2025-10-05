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

// Inputs
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page-1)*$perPage;
$search = _get_scalar('search');
$branch = _get_scalar('branch');
$status = _get_scalar('status');

// Role-based branch scope (1=Admin,2=Employee)
$rid = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
if (in_array($rid,[1,2],true)) {
    if (empty($_SESSION['branchcode']) && !empty($_SESSION['username'])) {
        $st = $conn->prepare('SELECT BranchCode FROM employee WHERE LoginName = ? LIMIT 1');
        $st->bind_param('s', $_SESSION['username']);
        $st->execute(); $r = $st->get_result()->fetch_assoc(); $st->close();
        if ($r && !empty($r['BranchCode'])) { $_SESSION['branchcode'] = $r['BranchCode']; }
    }
    $branch = $_SESSION['branchcode'] ?? $branch;
}

// Branches list
$branches = [];
if ($rs = $conn->query('SELECT BranchCode, BranchName FROM BranchMaster ORDER BY BranchName')) { while($row=$rs->fetch_assoc()) $branches[]=$row; }

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
$totalPages = max(1, (int)ceil($total/$perPage)); if ($page>$totalPages){ $page=$totalPages; $offset=($page-1)*$perPage; }

// Headers
if ($having!=='') {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName FROM Order_hdr oh JOIN orderDetails od ON od.OrderHdr_id=oh.Orderhdr_id LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql GROUP BY oh.Orderhdr_id HAVING $having ORDER BY oh.Created_dt DESC LIMIT ? OFFSET ?";
} else {
    $sql = "SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, oh.Created_by, c.CustomerName FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID=oh.CustomerID $whereSql ORDER BY oh.Created_dt DESC LIMIT ? OFFSET ?";
}
$stmt = $conn->prepare($sql); $p=$params; $t=$types.'ii'; $p[]=$perPage; $p[]=$offset; _bind($stmt,$t,$p); $stmt->execute(); $headers=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

// AJAX: details by id (independent of current page)
if (isset($_GET['action']) && $_GET['action']==='details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = trim($_GET['id']);
    // Enforce branch scope for restricted roles
    $sqlH = 'SELECT oh.Orderhdr_id, oh.CustomerID, oh.BranchCode, oh.Created_dt, c.CustomerName FROM Order_hdr oh LEFT JOIN customer c ON c.CustomerID=oh.CustomerID WHERE oh.Orderhdr_id=?';
    if (in_array($rid,[1,2],true) && !empty($_SESSION['branchcode'])) { $sqlH .= ' AND oh.BranchCode=?'; }
    $st = $conn->prepare($sqlH);
    if (in_array($rid,[1,2],true) && !empty($_SESSION['branchcode'])) { $st->bind_param('ss',$id,$_SESSION['branchcode']); } else { $st->bind_param('s',$id); }
    $st->execute(); $hdr=$st->get_result()->fetch_assoc(); $st->close();
    if(!$hdr){ echo json_encode(['error'=>'Order not found']); exit; }

    $sd = $conn->prepare('SELECT od.Quantity, od.Status, od.ProductBranchID, p.Model, p.Price, p.CategoryType, b.BrandName FROM orderDetails od JOIN ProductBranchMaster pb ON od.ProductBranchID=pb.ProductBranchID JOIN productMstr p ON pb.ProductID=p.ProductID JOIN brandMaster b ON p.BrandID=b.BrandID WHERE od.OrderHdr_id=?');
    $sd->bind_param('s',$id); $sd->execute(); $details=$sd->get_result()->fetch_all(MYSQLI_ASSOC); $sd->close();

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
.main-container { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
@media (max-width: 992px) { .main-container { margin-left: 0; width: 100%; } }
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
                        <?php foreach($branches as $br): ?>
                        <option value="<?= htmlspecialchars($br['BranchCode']) ?>" <?= $branch==$br['BranchCode']?'selected':'' ?>><?= htmlspecialchars($br['BranchName']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
            <?php $base=['search'=>$search,'branch'=>$branch,'status'=>$status]; if($page>1): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query($base+['page'=>$page-1]) ?>">&laquo;</a></li>
            <?php endif; for($i=1;$i<=$totalPages;$i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query($base+['page'=>$i]) ?>"><?= $i ?></a></li>
            <?php endfor; if($page<$totalPages): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query($base+['page'=>$page+1]) ?>">&raquo;</a></li>
            <?php endif; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Order Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="orderDetailsBody">Loading...</div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
  </div></div>
</div>

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
      (data.Details||[]).forEach(d=>{ html += `<tr><td>${d.Model||'N/A'}</td><td>${d.BrandName||'N/A'}</td><td>${d.CategoryType||'N/A'}</td><td>₱${parseFloat(d.Price||0).toFixed(2)}</td><td>${d.Quantity||0}</td><td>${d.Status||''}</td></tr>`; });
      html += '</tbody></table></div>';
      body.innerHTML = html; modal.show();
    })
    .catch(()=>{ body.innerHTML='<div class="alert alert-danger">Error loading details</div>'; modal.show(); });
}
</script>
</body>
</html>