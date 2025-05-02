<?php
include_once 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

$logsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $logsPerPage;

// Handle restore or delete actions
if (isset($_POST['action'])) {
    $conn = connect();
    
    if ($_POST['action'] == 'restore') {
        // Restore archived item
        $archiveID = (int)$_POST['archive_id'];
        $targetType = $_POST['target_type'];
        $targetID = (int)$_POST['target_id'];
        
        // Get the original table name based on target type
        $tableName = '';
        switch($targetType) {
            case 'product': $tableName = 'productMstr'; break;
            case 'employee': $tableName = 'employee'; break;
            case 'customer': $tableName = 'customer'; break;
            case 'order': $tableName = 'Order_hdr'; break;
        }
        
        if ($tableName) {
            // Update status to 'Active' in the original table
            $sql = "UPDATE $tableName SET Status = 'Active' WHERE " . 
                   ($tableName == 'Order_hdr' ? 'Orderhdr_id' : ucfirst($targetType) . 'ID') . " = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $targetID);
            $stmt->execute();
            
            // Log the restoration
            ActivityTracker1::logActivity($_SESSION['employee_id'], $targetID, $targetType, 3, 
                                       "Restored $targetType from archives");
            
            // Delete from archives
            $sql = "DELETE FROM archives WHERE ArchiveID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $archiveID);
            $stmt->execute();            
            $_SESSION['message'] = "Item restored successfully!";
            
        }
    } 
    elseif ($_POST['action'] == 'delete') {
        // Delete single archived item
        $archiveID = (int)$_POST['archive_id'];
        $targetType = $_POST['target_type'];
        $targetID = (int)$_POST['target_id'];
        
        // First delete from original table (cascade should handle this, but we'll do it explicitly)
        $tableName = '';
        switch($targetType) {
            case 'product': $tableName = 'productMstr'; break;
            case 'employee': $tableName = 'employee'; break;
            case 'customer': $tableName = 'customer'; break;
            case 'order': $tableName = 'Order_hdr'; break;
        }
        
        if ($tableName) {
            $sql = "DELETE FROM $tableName WHERE " . 
                   ($tableName == 'Order_hdr' ? 'Orderhdr_id' : ucfirst($targetType) . 'ID') . " = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $targetID);
            $stmt->execute();
            
            // Log the deletion
            ActivityTracker1::logActivity($_SESSION['employee_id'], $targetID, $targetType, 5, 
                                       "Permanently deleted $targetType from archives");
            
            // Then delete from archives
            $sql = "DELETE FROM archives WHERE ArchiveID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $archiveID);
            $stmt->execute();
            
            $_SESSION['message'] = "Item permanently deleted!";
        }
    } 
    elseif ($_POST['action'] == 'delete_all') {
        // Delete all archived items
        $targetType = $_POST['target_type'];
        
        // Get all archives of this type
        $sql = "SELECT TargetID FROM archives WHERE TargetType = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $targetType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tableName = '';
        switch($targetType) {
            case 'product': $tableName = 'productMstr'; break;
            case 'employee': $tableName = 'employee'; break;
            case 'customer': $tableName = 'customer'; break;
            case 'order': $tableName = 'Order_hdr'; break;
        }
        
        if ($tableName) {
            // Delete all records from original table
            $columnName = ($tableName == 'Order_hdr' ? 'Orderhdr_id' : ucfirst($targetType) . 'ID');
            
            // First get all target IDs to log the deletion
            $targetIDs = [];
            while ($row = $result->fetch_assoc()) {
                $targetIDs[] = $row['TargetID'];
            }
            
            // Delete from original table
            if (!empty($targetIDs)) {
                $placeholders = implode(',', array_fill(0, count($targetIDs), '?'));
                $sql = "DELETE FROM $tableName WHERE $columnName IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $types = str_repeat('i', count($targetIDs));
                $stmt->bind_param($types, ...$targetIDs);
                $stmt->execute();
                
                // Log the mass deletion
                foreach ($targetIDs as $id) {
                    ActivityTracker1::logActivity($_SESSION['employee_id'], $id, $targetType, 5, 
                                               "Permanently deleted $targetType from archives (mass deletion)");
                }
            }
            
            // Delete from archives
            $sql = "DELETE FROM archives WHERE TargetType = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $targetType);
            $stmt->execute();
            
            $_SESSION['message'] = "All archived $targetType records have been permanently deleted!";
        }
    }
    
    $conn->close();
    header("Location: archives.php");
    exit();
}

// Base query for archives
$query = "SELECT a.ArchiveID, a.TargetID, a.TargetType, a.ArchivedAt, 
                 e.EmployeeName as ArchivedBy
          FROM archives a
          JOIN employee e ON a.EmployeeID = e.EmployeeID";

$where = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "e.EmployeeName LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types .= 's';
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where[] = "a.TargetType = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}

$conn = connect();
$totalQuery = "SELECT COUNT(*) as total FROM archives a" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "");
$stmt = $conn->prepare($totalQuery);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$totalResult = $stmt->get_result();
$totalLogs = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $logsPerPage);
$stmt->close();

$query .= " ORDER BY a.ArchivedAt DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $logsPerPage;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$archivesResult = $stmt->get_result();
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
    <title>Archives | Santos Optical</title>
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
        .archives-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .archive-entry {
            border-left: 4px solid #6c757d;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .archive-entry:hover {
            background-color: #f8f9fa;
        }
        .archive-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .archive-action {
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
        .action-buttons {
            margin-top: 10px;
        }
        .delete-all-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100;
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
            .archive-entry {
                padding: 10px;
            }
            .archive-action {
                font-size: 0.9rem;
            }
            .archive-time {
                font-size: 0.75rem;
            }
        }
        @media (max-width: 576px) {
            .archives-container {
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
            .archive-entry .d-flex {
                flex-direction: column;
            }
            .archive-entry .badge {
                margin-bottom: 5px;
            }
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-archive me-2"></i>System Archives</h2>
            <a href="Dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="archives-container">
            <form method="get" action="archives.php" class="mb-4 filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by archived by..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <?php if ($archivesResult->num_rows > 0): ?>
                <div class="list-group">
                    <?php while ($archive = $archivesResult->fetch_assoc()): ?>
                        <div class="list-group-item archive-entry">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="archive-time">
                                        <?php echo date('M j, Y g:i A', strtotime($archive['ArchivedAt'])); ?>
                                    </span>
                                    <div class="archive-action mt-1">
                                        <span class="badge 
                                            <?php 
                                                switch($archive['TargetType']) {
                                                    case 'customer': echo 'badge-customer'; break;
                                                    case 'employee': echo 'badge-employee'; break;
                                                    case 'product': echo 'badge-product'; break;
                                                    case 'order': echo 'badge-order'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?> me-2">
                                            <?php echo ucfirst($archive['TargetType']); ?>
                                        </span>
                                        <strong><?php echo $archive['ArchivedBy']; ?></strong> archived 
                                        <?php echo $archive['TargetType'] ?> ID: <?php echo $archive['TargetID']; ?>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="archive_id" value="<?php echo $archive['ArchiveID']; ?>">
                                            <input type="hidden" name="target_id" value="<?php echo $archive['TargetID']; ?>">
                                            <input type="hidden" name="target_type" value="<?php echo $archive['TargetType']; ?>">
                                            <input type="hidden" name="action" value="restore">
                                            <button type="submit" class="btn btn-sm btn-success me-2">
                                                <i class="fas fa-undo me-1"></i> Restore
                                            </button>
                                        </form>
                                        
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="archive_id" value="<?php echo $archive['ArchiveID']; ?>">
                                            <input type="hidden" name="target_id" value="<?php echo $archive['TargetID']; ?>">
                                            <input type="hidden" name="target_type" value="<?php echo $archive['TargetType']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to permanently delete this archived item? All related data to it will also be lost.')">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    Archive ID: <?php echo $archive['ArchiveID']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <nav aria-label="Archives pagination" class="mt-4">
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
                    <i class="fas fa-info-circle me-2"></i> No archived records found.
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_GET['type']) && !empty($_GET['type']) && $archivesResult->num_rows > 0): ?>
            <form method="post" class="delete-all-btn">
                <input type="hidden" name="target_type" value="<?php echo $_GET['type']; ?>">
                <input type="hidden" name="action" value="delete_all">
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('WARNING: This will permanently delete ALL archived <?php echo $_GET['type']; ?> records and their original data. All related data to the archives will also be lost.')">
                    <i class="fas fa-trash-alt me-1"></i> Delete All <?php echo ucfirst($_GET['type']); ?> Archives
                </button>
            </form>
        <?php endif; ?>
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