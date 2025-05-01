<?php
// Set panel title based on role
$panel = " Panel"; 
$isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;

if ($isAdmin === true) {
    $panel = "Admin". $panel;
} else {
    $panel = "Employee". $panel;
}
?>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle d-lg-none" id="mobileMenuToggle" style="display: none;">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand text-center py-3">
        <a class="navbar-brand fw-bold d-flex flex-column align-items-center" href="index.php">
            <img src="Images/logo.png" alt="Logo" width="60" height="80" class="mb-2 sidebar-logo">
            <span class="sidebar-brand-text">Santos Optical</span>
        </a>
    </div>
    
    <!-- Sidebar Header -->
    <div class="sidebar-header text-center">
        <h4><i class="fas fa-cog"></i> <?php echo $panel ?></h4>
    </div>
    
    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;
        ?>
        
        <a href="admin.php" class="sidebar-item <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> 
            <span class="sidebar-item-text">Dashboard</span>
        </a>
        
        <a href="customerRecords.php" class="sidebar-item <?php echo ($current_page == 'customerRecords.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> 
            <span class="sidebar-item-text">Customer Information</span>
        </a>
        
        <?php if ($isAdmin): ?>
            <a href="employeeRecords.php" class="sidebar-item <?php echo ($current_page == 'employeeRecords.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> 
                <span class="sidebar-item-text">Manage Employees</span>
            </a>
                
            <a href="admin-inventory.php" class="sidebar-item <?php echo ($current_page == 'admin-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> 
                <span class="sidebar-item-text">Manage Inventory</span>
            </a>
        <?php endif; ?>

        <?php if (!$isAdmin): ?>
            <a href="Employee-inventory.php" class="sidebar-item <?php echo ($current_page == 'Employee-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> 
                <span class="sidebar-item-text">Manage Inventory</span>
            </a>
        <?php endif; ?>
        
        <a href="order.php" class="sidebar-item <?php echo ($current_page == 'order.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> 
            <span class="sidebar-item-text">Manage Orders</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="logs.php" class="sidebar-item <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> 
                <span class="sidebar-item-text">System Logs</span>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Logout Button - Fixed at bottom -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-item text-danger">
            <i class="fas fa-sign-out-alt"></i> 
            <span class="sidebar-item-text">Logout</span>
        </a>
    </div>
</div>