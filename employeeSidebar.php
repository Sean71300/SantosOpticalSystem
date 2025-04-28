<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-cog"></i> Admin Panel</h4>
    </div>
    
    <div class="sidebar-menu">
        <?php
        // Get current page filename
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        
        <a href="admin.php" class="sidebar-item <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <a href="customerRecords.php" class="sidebar-item <?php echo ($current_page == 'customerRecords.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Customer Information
        </a>
        
        <a href="EmployeeRecords.php" class="sidebar-item <?php echo ($current_page == 'EmployeeRecords.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Manage Employees
        </a>
        
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
    </div>
</div>