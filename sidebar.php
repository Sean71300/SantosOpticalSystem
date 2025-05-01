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

<div class="sidebar">
    <!-- Added logo and brand name at the top -->
    <div class="sidebar-brand text-center py-3">
        <a class="navbar-brand fw-bold d-flex flex-column align-items-center" href="index.php">
            <img src="Images/logo.png" alt="Logo" width="60" height="80" class="mb-2"> 
            <span>Santos Optical</span>
        </a>
    </div>
    
   
    <div class="sidebar-header text-center">
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
            <a href="employeeRecords.php" class="sidebar-item <?php echo ($current_page == 'employeeRecords.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> Manage Employees
            </a>
                
            <a href="admin-inventory.php" class="sidebar-item <?php echo ($current_page == 'admin-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> Manage Inventory
            </a>
        <?php endif; ?>

        <?php if (!$isAdmin): ?>
            <a href="employee-inventory.php" class="sidebar-item <?php echo ($current_page == 'Employee-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Inventory
            </a>
        <?php endif; ?>
        
        <a href="order.php" class="sidebar-item <?php echo ($current_page == 'order.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Manage Orders
        </a>

        <?php if ($isAdmin): ?>
            <a href="logs.php" class="sidebar-item <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> System Logs
            </a>
        <?php endif; ?>
        
        <!-- Logout Button -->
        <div class="sidebar-footer" style="position: absolute; bottom: 0; width: 100%;">
            <a href="logout.php" class="sidebar-item text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>