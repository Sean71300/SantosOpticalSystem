<?php
include_once 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

// Only Super Admin (roleid 4) can access System Logs
if (session_status() === PHP_SESSION_NONE) { @session_start(); }
$rid = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0;
if ($rid !== 4) {
    header('Location: Dashboard.php');
    exit();
}

$logsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $logsPerPage;

 $query = "SELECT l.LogsID, l.Upd_dt, l.TargetType, l.TargetID, l.Description, l.ActivityCode,
           e.EmployeeName as Employee
       FROM Logs l
       JOIN employee e ON l.EmployeeID = e.EmployeeID";

$where = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "l.Description LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types .= 's';
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where[] = "l.TargetType = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

if (isset($_GET['activity']) && !empty($_GET['activity'])) {
    $where[] = "l.ActivityCode = ?";
    $params[] = $_GET['activity'];
    $types .= 'i';
}

if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}

$conn = connect();
$totalQuery = "SELECT COUNT(*) as total FROM Logs l" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "");
$stmt = $conn->prepare($totalQuery);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$totalResult = $stmt->get_result();
$totalLogs = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $logsPerPage);
$stmt->close();

$query .= " ORDER BY l.Upd_dt DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $logsPerPage;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$logsResult = $stmt->get_result();
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
    <title>System Logs | Santos Optical</title>
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
        .logs-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            /* reserve modest space at the bottom so pagination sits inside the card */
            padding-bottom: 24px;
            position: relative;
        }
        /* Keep pagination inside the logs card by letting it flow below the list.
           Allow horizontal scrolling for long page lists so it never overflows. */
        .logs-container nav[aria-label="Logs pagination"] {
            position: static;
            margin-top: 18px;
            display: block;
        }
        .logs-container .pagination {
            justify-content: flex-start; /* allow natural flow */
            gap: 6px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 6px;
        }
        .logs-container .pagination .page-item {
            display: inline-block;
        }
        .log-entry {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .log-entry:hover {
            background-color: #f8f9fa;
        }
        .log-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .log-action {
            font-weight: 500;
        }
        .badge-customer {
            background-color: #20c997;
        }
        .badge-employee {
            background-color: #6f42c1;
        }
        .badge-product {
            background-color: #fd7e14;
        }
        .badge-order {
            background-color: #d63384;
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
            .log-entry {
                padding: 10px;
            }
            .log-action {
                font-size: 0.9rem;
            }
            .log-time {
                font-size: 0.75rem;
            }
        }
        @media (max-width: 576px) {
            .logs-container {
                padding: 15px 15px 30px; /* extra bottom padding on small screens */
            }
            .logs-container nav[aria-label="Logs pagination"] {
                position: static; /* let pagination flow on very small screens */
                margin-top: 12px;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 .btn {
                margin-top: 10px;
                width: 100%;
            }
            .log-entry .d-flex {
                flex-direction: column;
            }
            .log-entry .badge {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list me-2"></i>System Logs</h2>
            <a href="Dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="logs-container">
            <form method="get" action="logs.php" class="mb-4 filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search logs..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Filter by Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="customer" <?php echo (isset($_GET['type']) && $_GET['type'] == 'customer') ? 'selected' : '' ?>>Customer</option>
                            <option value="employee" <?php echo (isset($_GET['type']) && $_GET['type'] == 'employee') ? 'selected' : '' ?>>Employee</option>
                            <option value="product" <?php echo (isset($_GET['type']) && $_GET['type'] == 'product') ? 'selected' : '' ?>>Product</option>
                            <option value="order" <?php echo (isset($_GET['type']) && $_GET['type'] == 'order') ? 'selected' : '' ?>>Order</option>
                            <option value="branch" <?php echo (isset($_GET['type']) && $_GET['type'] == 'branch') ? 'selected' : '' ?>>Branch</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="activity" class="form-label">Filter by Activity</label>
                        <select class="form-select" id="activity" name="activity">
                            <option value="">All Activities</option>
                            <option value="3" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '3') ? 'selected' : '' ?>>Added</option>
                            <option value="4" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '4') ? 'selected' : '' ?>>Edited</option>
                            <option value="5" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '5') ? 'selected' : '' ?>>Deleted</option>
                            <option value="7" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '7') ? 'selected' : '' ?>>Cancelled</option>                            
                            <option value="1" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '1') ? 'selected' : '' ?>>Completed</option>                            
                            <option value="9" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '9') ? 'selected' : '' ?>>Claimed</option>
                            <option value="8" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '8') ? 'selected' : '' ?>>Returns</option>                            
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <?php if ($logsResult->num_rows > 0): ?>
                <div class="list-group">
                    <?php while ($log = $logsResult->fetch_assoc()): ?>
                        <div class="list-group-item log-entry">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="log-time">
                                        <?php echo date('M j, Y g:i A', strtotime($log['Upd_dt'])); ?>
                                    </span>
                                    <div class="log-action mt-1">
                                        <span class="badge 
                                            <?php 
                                                switch($log['TargetType']) {
                                                    case 'customer': echo 'badge-customer'; break;
                                                    case 'employee': echo 'badge-employee'; break;
                                                    case 'product': echo 'badge-product'; break;
                                                    case 'order': echo 'badge-order'; break;
                                                    case 'branch': echo 'bg-primary text-white'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?> me-2">
                                            <?php echo ucfirst($log['TargetType']); ?>
                                        </span>
                                        <strong><?php echo ($log['TargetType'] === 'branch' ? 'Branch ' : '') . $log['Employee']; ?></strong> - 
                                        <?php 
                                            $activity = '';
                                            switch($log['ActivityCode']) {
                                                case 1: $activity = 'completed'; break;
                                                case 2: $activity = 'marked as pending'; break;
                                                case 3: $activity = 'added'; break;
                                                case 4: $activity = 'edited'; break;
                                                case 5: $activity = 'deleted'; break;
                                                case 6: $activity = 'archived'; break;                                                
                                                case 7: $activity = 'cancelled'; break;
                                                case 8: $activity = 'receives'; break;
                                                case 9: $activity = 'delivered'; break;
                                                default: $activity = 'performed an action on';
                                            }                                            
                                            echo "<strong>" . $activity . "</strong> " . $log['TargetType'] .": ". $log['Description']; 
                                            if ($log['TargetType'] === 'branch' && !empty($log['TargetID'])) {
                                                
                                            }
                                        ?>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    ID: <?php echo $log['LogsID']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <nav aria-label="Logs pagination" class="mt-4">
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

                        <?php 
                            // Sliding window pagination: show current +/- 10 pages (max 21 visible)
                            $maxVisible = 21;
                            $half = floor($maxVisible / 2); // 10
                            // Ensure current page within bounds
                            if ($totalPages <= 0) $totalPages = 1;
                            $currentPage = max(1, min($currentPage, $totalPages));

                            $start = $currentPage - $half;
                            $end = $currentPage + $half;

                            if ($start < 1) {
                                $end += 1 - $start;
                                $start = 1;
                            }
                            if ($end > $totalPages) {
                                $start -= $end - $totalPages;
                                $end = $totalPages;
                                if ($start < 1) $start = 1;
                            }

                            for ($i = $start; $i <= $end; $i++): ?>
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
                    <i class="fas fa-info-circle me-2"></i> No logs found.
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
        });
    </script>
</body>
</html>