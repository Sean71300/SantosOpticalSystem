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
            <img src="Images/logo.png" alt="Logo" width="60" height="80" class="mb-2 d-none d-md-block"> 
            <img src="Images/logo.png" alt="Logo" width="40" height="60" class="mb-2 d-md-none">
            <span class="d-none d-md-inline">Santos Optical</span>
            <span class="d-md-none">SO</span>
        </a>
    </div>
    
    <div class="sidebar-header text-center">
        <h4 class="d-none d-md-block"><i class="fas fa-cog"></i> <?php echo $panel ?></h4>
        <h5 class="d-md-none"><i class="fas fa-cog"></i> <?php echo $isAdmin ? 'Admin' : 'Emp' ?></h5>
    </div>
    
    <div class="sidebar-menu">
        <?php 
        // Get current page filename
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Check if user is admin (assuming you have this in your session)
        $isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;
        ?>
        
        <a href="admin.php" class="sidebar-item <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> 
            <span class="d-none d-md-inline">Dashboard</span>
            <span class="d-md-none">Home</span>
        </a>
        
        <a href="customerRecords.php" class="sidebar-item <?php echo ($current_page == 'customerRecords.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> 
            <span class="d-none d-md-inline">Customer Information</span>
            <span class="d-md-none">Customers</span>
        </a>
        
        <?php if ($isAdmin): ?>
            <a href="employeeRecords.php" class="sidebar-item <?php echo ($current_page == 'employeeRecords.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> 
                <span class="d-none d-md-inline">Manage Employees</span>
                <span class="d-md-none">Employees</span>
            </a>
                
            <a href="admin-inventory.php" class="sidebar-item <?php echo ($current_page == 'admin-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> 
                <span class="d-none d-md-inline">Manage Inventory</span>
                <span class="d-md-none">Inventory</span>
            </a>
        <?php endif; ?>

        <?php if (!$isAdmin): ?>
            <a href="Employee-inventory.php" class="sidebar-item <?php echo ($current_page == 'Employee-inventory.php') ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> 
                <span class="d-none d-md-inline">Manage Inventory</span>
                <span class="d-md-none">Inventory</span>
            </a>
        <?php endif; ?>
        
        <a href="order.php" class="sidebar-item <?php echo ($current_page == 'order.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> 
            <span class="d-none d-md-inline">Manage Orders</span>
            <span class="d-md-none">Orders</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="logs.php" class="sidebar-item <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> 
                <span class="d-none d-md-inline">System Logs</span>
                <span class="d-md-none">Logs</span>
            </a>
        <?php endif; ?>
        
        <!-- Logout Button -->
        <div class="sidebar-footer" style="position: absolute; bottom: 0; width: 100%;">
            <a href="logout.php" class="sidebar-item text-danger">
                <i class="fas fa-sign-out-alt"></i> 
                <span class="d-none d-md-inline">Logout</span>
            </a>
        </div>
    </div>
</div>

<style>
    /* Sidebar Responsive Styles */
    .sidebar {
        width: 250px;
        transition: all 0.3s;
        z-index: 1000;
    }
    
    /* Mobile styles */
    @media (max-width: 768px) {
        .sidebar {
            width: 80px;
            overflow: hidden;
        }
        
        .sidebar:hover, .sidebar.active {
            width: 250px;
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        
        .sidebar:hover .d-md-none,
        .sidebar.active .d-md-none {
            display: none !important;
        }
        
        .sidebar:hover .d-none.d-md-inline,
        .sidebar.active .d-none.d-md-inline {
            display: inline !important;
        }
        
        .sidebar-brand span {
            font-size: 0.8rem;
        }
    }
    
    /* Tablet styles */
    @media (min-width: 769px) and (max-width: 992px) {
        .sidebar {
            width: 200px;
        }
        
        .sidebar-item span {
            font-size: 0.9rem;
        }
    }
</style>

<script>
    // Make sidebar expand on hover for mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        
        if (window.innerWidth <= 768) {
            sidebar.addEventListener('mouseenter', function() {
                this.classList.add('active');
            });
            
            sidebar.addEventListener('mouseleave', function() {
                this.classList.remove('active');
            });
        }
        
        // Update behavior on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    });
</script>