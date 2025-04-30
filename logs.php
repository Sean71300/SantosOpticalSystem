<?php
include_once 'setup.php';
include 'ActivityTracker.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $search_condition = "WHERE e.EmployeeName LIKE '%$search%' OR 
                         am.Description LIKE '%$search%' OR 
                         l.TargetType LIKE '%$search%'";
}

// Fetch total number of logs with search condition
$total_query = "SELECT COUNT(*) as total FROM Logs l
                JOIN employee e ON l.EmployeeID = e.EmployeeID
                JOIN activityMaster am ON l.ActivityCode = am.ActivityCode
                $search_condition";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch logs with employee and activity information
$query = "SELECT l.LogsID, l.Upd_dt, e.EmployeeName, 
                 am.Description as Activity, l.TargetID, l.TargetType
          FROM Logs l
          JOIN employee e ON l.EmployeeID = e.EmployeeID
          JOIN activityMaster am ON l.ActivityCode = am.ActivityCode
          $search_condition
          ORDER BY l.Upd_dt DESC
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin | System Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="customCodes/custom.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .logs-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .search-box {
            max-width: 400px;
        }
        .pagination {
            justify-content: center;
        }
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        .badge-customer {
            background-color: #4e73df;
        }
        .badge-employee {
            background-color: #1cc88a;
        }
        .badge-product {
            background-color: #f6c23e;
        }
        .badge-order {
            background-color: #e74a3b;
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-history me-2"></i> System Activity Logs</h2>
            <div>
                <form method="GET" class="d-flex search-box">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Search logs..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="logs-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Timestamp</th>
                            <th>Employee</th>
                            <th>Activity</th>
                            <th>Target ID</th>
                            <th>Target Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['LogsID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Upd_dt']); ?></td>
                                    <td><?php echo htmlspecialchars($row['EmployeeName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Activity']); ?></td>
                                    <td><?php echo htmlspecialchars($row['TargetID']); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = '';
                                        switch($row['TargetType']) {
                                            case 'customer':
                                                $badge_class = 'badge-customer';
                                                break;
                                            case 'employee':
                                                $badge_class = 'badge-employee';
                                                break;
                                            case 'product':
                                                $badge_class = 'badge-product';
                                                break;
                                            case 'order':
                                                $badge_class = 'badge-order';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['TargetType'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination mt-4">
                        <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" 
                               aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" 
                               aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="text-end mt-2">
                <span class="text-muted">
                    Showing <?php echo ($start + 1) . " to " . min($start + $limit, $total_records) . " of $total_records entries"; ?>
                </span>
            </div>
        </div>
    </div>
</body>
</html>