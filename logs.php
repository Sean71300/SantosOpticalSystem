<?php
include_once 'setup.php';
include 'loginChecker.php';
include 'ActivityTracker.php';

// Check if user is admin
if ($_SESSION['role'] != 1) {
    header("Location: unauthorized.php");
    exit();
}

// Pagination setup
$logsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $logsPerPage;

// Get total number of logs
$conn = connect();
$totalLogsQuery = "SELECT COUNT(*) as total FROM Logs";
$totalResult = $conn->query($totalLogsQuery);
$totalLogs = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $logsPerPage);

// Get logs with pagination
$logsQuery = "SELECT l.LogsID, l.Upd_dt, l.TargetType, l.TargetID, 
                     a.Description as Activity, 
                     e.EmployeeName as Employee,
                     CASE 
                         WHEN l.TargetType = 'customer' THEN c.CustomerName
                         WHEN l.TargetType = 'employee' THEN e2.EmployeeName
                         WHEN l.TargetType = 'product' THEN p.Model
                         ELSE 'Order #' || l.TargetID
                     END as TargetName
              FROM Logs l
              JOIN activityMaster a ON l.ActivityCode = a.ActivityCode
              JOIN employee e ON l.EmployeeID = e.EmployeeID
              LEFT JOIN customer c ON l.TargetType = 'customer' AND l.TargetID = c.CustomerID
              LEFT JOIN employee e2 ON l.TargetType = 'employee' AND l.TargetID = e2.EmployeeID
              LEFT JOIN productMstr p ON l.TargetType = 'product' AND l.TargetID = p.ProductID
              ORDER BY l.Upd_dt DESC
              LIMIT $logsPerPage OFFSET $offset";
$logsResult = $conn->query($logsQuery);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
    <title>System Logs</title>
    <style>
        body {
            background-color: #f5f7fa;
        }
        .sidebar {
            background-color: white;
            height: 100vh;
            padding: 20px 0;
            color: #2c3e50;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .logs-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
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
        .log-target {
            color: #0d6efd;
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
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list me-2"></i>System Logs</h2>
            <a href="admin.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="logs-container">
            <!-- Search and Filter Form -->
            <form method="get" action="logs.php" class="mb-4">
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
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="activity" class="form-label">Filter by Activity</label>
                        <select class="form-select" id="activity" name="activity">
                            <option value="">All Activities</option>
                            <option value="1" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '1') ? 'selected' : '' ?>>Purchased</option>
                            <option value="2" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '2') ? 'selected' : '' ?>>Added</option>
                            <option value="3" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '3') ? 'selected' : '' ?>>Archived</option>
                            <option value="4" <?php echo (isset($_GET['activity']) && $_GET['activity'] == '4') ? 'selected' : '' ?>>Edited</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Logs List -->
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
                                                    default: echo 'bg-secondary';
                                                }
                                            ?> me-2">
                                            <?php echo ucfirst($log['TargetType']); ?>
                                        </span>
                                        <strong><?php echo $log['Employee']; ?></strong> 
                                        <?php echo strtolower($log['Activity']); ?> 
                                        <span class="log-target"><?php echo $log['TargetName']; ?></span>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    ID: <?php echo $log['LogsID']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <nav aria-label="Logs pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>