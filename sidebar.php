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
<button class="mobile-menu-toggle d-lg-none" id="mobileMenuToggle">
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
        
        <!-- Logout Button -->
        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-item text-danger">
                <i class="fas fa-sign-out-alt"></i> 
                <span class="sidebar-item-text">Logout</span>
            </a>
        </div>
    </div>
</div>

<style>
    /* Base Sidebar Styles */
    .sidebar {
        background-color: white;
        height: 100vh;
        padding: 20px 0;
        color: #2c3e50;
        position: fixed;
        width: 250px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        z-index: 1000;
        left: 0;
        top: 0;
        overflow-y: auto;
    }
    
    .sidebar-brand {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
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
        position: absolute;
        bottom: 0;
        width: 100%;
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
        }
        
        .sidebar.active {
            transform: translateX(0);
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        
        .mobile-menu-toggle {
            display: block;
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
    
    /* Smooth transitions */
    .sidebar-item-text {
        transition: opacity 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const body = document.body;
        
        // Toggle sidebar on mobile
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992 && 
                !sidebar.contains(e.target) && 
                e.target !== mobileToggle) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
        
        // Close sidebar when a link is clicked (on mobile)
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            });
        });
        
        // Auto-close sidebar when resizing to larger screens
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
    });
</script>