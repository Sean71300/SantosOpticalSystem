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
        
        <a href="Dashboard.php" class="sidebar-item <?php echo ($current_page == 'Dashboard.php') ? 'active' : ''; ?>">
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
            <a href="logs.php" class="sidebar-item <?php echo ($current_page == 'archives.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> 
                <span class="sidebar-item-text">Archives</span>
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

<style>
    /* Sidebar Styles */
    .sidebar {
        background-color: white;
        height: 100vh;
        padding: 20px 0 70px; /* Added padding at bottom for logout button */
        color: #2c3e50;
        position: fixed;
        width: 250px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        z-index: 1000;
        top: 0;
        left: 0;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .sidebar-brand {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .sidebar-header {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .sidebar-menu {
        flex: 1;
        overflow-y: auto;
        padding-bottom: 20px;
    }
    
    .sidebar-item {
        padding: 12px 20px;
        margin: 5px 0;
        border-radius: 0;
        display: flex;
        align-items: center;
        color: #2c3e50;
        transition: all 0.3s;
        text-decoration: none;
        white-space: nowrap;
    }
    
    .sidebar-item:hover {
        background-color: #f8f9fa;
    }
    
    .sidebar-item.active {
        background-color: #e9ecef;
        font-weight: 500;
    }
    
    .sidebar-item i {
        margin-right: 15px;
        width: 20px;
        text-align: center;
    }
    
    .sidebar-footer {
        position: sticky;
        bottom: 0;
        width: 100%;
        background: white;
        padding: 10px 0;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        background: #4e73df;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 12px;
        font-size: 1.2rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            padding-bottom: 70px; /* Ensure space for logout button */
        }
        
        .sidebar.active {
            transform: translateX(0);
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        
        .mobile-menu-toggle {
            display: block !important;
        }
        
        /* Adjust main content when sidebar is open */
        body.sidebar-open {
            overflow: hidden;
        }
        
        body.sidebar-open::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-footer {
            position: sticky;
            bottom: 0;
        }
    }
    
    /* Smooth transitions */
    .sidebar-item-text {
        transition: opacity 0.3s ease;
    }
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        background: #4e73df;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 12px;
        font-size: 1.2rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            padding-bottom: 70px; /* Ensure space for logout button */
        }
        
        .sidebar.active {
            transform: translateX(0);
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        
        .mobile-menu-toggle {
            display: block !important;
        }
        
        /* Adjust main content when sidebar is open */
        body.sidebar-open {
            overflow: hidden;
        }
        
        body.sidebar-open::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    }
</style>