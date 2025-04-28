<?php
    $panel = " Panel"; 
    $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;
    
    if ($isAdmin === true) {
        $panel = "Admin". $panel;
    } 
    else {
        $panel = "Employee". $panel;
    }
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-cog"></i> <?php echo $panel ?></h4>
    </div>
    
    <div class="sidebar-menu">
        <?php 
        // Get current page filename
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Check if user is admin (assuming you have this in your session)
        $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;
        ?>
        
        <a href="admin.php" class="sidebar-item <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <a href="customerRecords.php" class="sidebar-item <?php echo ($current_page == 'customerRecords.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Customer Information
        </a>
        
        <?php if ($isAdmin): ?>
            <a href="EmployeeRecords.php" class="sidebar-item <?php echo ($current_page == 'EmployeeRecords.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> Manage Employees
            </a>
        <?php endif; ?>
        
        <a href="admin-inventory.php" class="sidebar-item <?php echo ($current_page == 'admin-inventory.php') ? 'active' : ''; ?>">
            <i class="fas fa-boxes"></i> Manage Inventory
        </a>
        
        <a href="order.php" class="sidebar-item">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        
        <a href="#" class="sidebar-item">
            <i class="fas fa-box-open"></i> Products
        </a>
        
        <a href="#" class="sidebar-item">
            <i class="fas fa-layer-group"></i> Collections
        </a>
        
        <a href="#" class="sidebar-item">
            <i class="fas fa-archive"></i> Package
        </a>
        
        <!-- Logout Button -->
        <div class="sidebar-footer" style="position: absolute; bottom: 0; width: 100%;">
            <a href="logout.php" class="sidebar-item text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>