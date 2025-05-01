<?php
include_once 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'adminFunctions.php';

// Get all counts
$customerCount = getCustomerCount();
$employeeCount = getEmployeeCount();
$inventoryCount = getInventoryCount();
$orderCount = getOrderCount();
$recentActivities = getRecentActivities();
$lowInventory = getLowInventoryProducts();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin | Dashboard</title>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        
        <style>
            :root {
                --sidebar-width: 250px;
                --sidebar-collapsed-width: 80px;
                --primary-color: #4e73df;
                --secondary-color: #858796;
                --success-color: #1cc88a;
                --info-color: #36b9cc;
                --warning-color: #f6c23e;
                --danger-color: #e74a3b;
            }
            
            body {
                background-color: #f5f7fa;
                min-height: 100vh;
                overflow-x: hidden;
            }
            
            /* Sidebar Styles */
            .sidebar {
                background-color: white;
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;
                position: fixed;
                width: var(--sidebar-width);
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                transition: all 0.3s;
                z-index: 1000;
            }
            
            .sidebar-header {
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
                color: #2c3e50;
            }
            
            .sidebar-item.active {
                background-color: #e9ecef;
                color: #2c3e50;
                font-weight: 500;
            }
            
            .sidebar-item i {
                margin-right: 10px;
                width: 20px;
                text-align: center;
                flex-shrink: 0;
            }
            
            /* Main Content */
            .main-content {
                margin-left: var(--sidebar-width);
                padding: 20px;
                transition: all 0.3s;
                min-height: 100vh;
            }
            
            /* Dashboard Cards */
            .dashboard-card {
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                padding: 20px;
                background-color: white;
                transition: transform 0.3s, box-shadow 0.3s;
                height: 100%;
            }
            
            .dashboard-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            
            .card-icon {
                font-size: 2rem;
                margin-bottom: 15px;
            }
            
            .stat-number {
                font-size: 2rem;
                font-weight: bold;
            }
            
            /* Recent Activity */
            .recent-activity {
                max-height: 400px;
                overflow-y: auto;
            }
            
            /* Low Stock Items */
            .low-stock-item {
                display: flex;
                align-items: center;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            
            .low-stock-item:last-child {
                border-bottom: none;
            }
            
            .low-stock-img {
                width: 60px;
                height: 60px;
                object-fit: cover;
                border-radius: 5px;
                margin-right: 15px;
            }
            
            /* Mobile Menu Toggle */
            .mobile-menu-btn {
                display: none;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1050;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 5px;
                padding: 5px 10px;
            }
            
            /* Responsive Adjustments */
            @media (max-width: 992px) {
                .sidebar {
                    transform: translateX(-100%);
                    width: var(--sidebar-width);
                }
                
                .sidebar.active {
                    transform: translateX(0);
                }
                
                .main-content {
                    margin-left: 0;
                    width: 100%;
                }
                
                .mobile-menu-btn {
                    display: block;
                }
                
                .dashboard-card {
                    padding: 15px;
                }
            }
            
            @media (max-width: 768px) {
                .stat-number {
                    font-size: 1.5rem;
                }
                
                .card-icon {
                    font-size: 1.5rem;
                }
            }
            
            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }
            
            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }
            
            ::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 10px;
            }
            
            ::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        </style>
    </head>

    <body>
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Sidebar -->
        <?php include "sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php
                $username = $_SESSION["username"];
                echo "<h2 class='mb-4'>Welcome back, $username</h2>";
            ?>
            
            <!-- Dashboard Cards -->
            <div class="row g-4">
                <!-- Customers Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="dashboard-card">
                        <div class="card-icon text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Customers</h5>
                        <div class="stat-number"><?php echo number_format($customerCount); ?></div>
                        <a href="customerRecords.php" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                    </div>
                </div>
                
                <!-- Employees Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="dashboard-card">
                        <div class="card-icon text-success">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5>Employees</h5>
                        <div class="stat-number"><?php echo number_format($employeeCount); ?></div>
                        <a href="employeeRecords.php" class="btn btn-sm btn-outline-success mt-2">View All</a>
                    </div>
                </div>
                
                <!-- Inventory Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="dashboard-card">
                        <div class="card-icon text-warning">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h5>Inventory</h5>
                        <div class="stat-number"><?php echo number_format($inventoryCount); ?></div>
                        <a href="admin-inventory.php" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                    </div>
                </div>
                
                <!-- Orders Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="dashboard-card">
                        <div class="card-icon text-info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h5>Orders</h5>
                        <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                        <a href="#" class="btn btn-sm btn-outline-info mt-2">View All</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Section -->
            <div class="row mt-4 g-4">
                <div class="col-lg-8 col-md-12">
                    <div class="dashboard-card">
                        <h5><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                        <div class="mt-3" style="height: 300px; background-color: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            [Sales Chart Placeholder]
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-12">
                    <div class="dashboard-card">    
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fa-solid fa-circle-exclamation me-2"></i>Low Stocks</h5>
                            <a href="admin-inventory.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-boxes-stacked"></i> Show Inventory
                            </a>
                        </div>
                        <hr class="border-1 border-black opacity-25">
                        <div class="low-stock-list">
                            <?php if (count($lowInventory) > 0): ?>
                                <?php foreach ($lowInventory as $product): ?>
                                    <div class="low-stock-item">
                                        <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="Product Image" class="low-stock-img">
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['Model']); ?></strong>
                                            <div class="text-danger">Available Stocks: <?php echo htmlspecialchars($product['Stocks']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted">No low stock products.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dashboard-card recent-activity">    
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                            <a href="logs.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list me-1"></i> Show All Logs
                            </a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentActivities as $activity): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($activity['Upd_dt'])); ?></small>
                                    <div><?php echo htmlspecialchars($activity['Description']); ?></div>
                                </div>
                                <span class="badge bg-primary rounded-pill">New</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            // Mobile menu toggle functionality
            document.getElementById('mobileMenuBtn').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.querySelector('.sidebar');
                const mobileBtn = document.getElementById('mobileMenuBtn');
                
                if (window.innerWidth <= 992 && 
                    !sidebar.contains(event.target) && 
                    event.target !== mobileBtn && 
                    !mobileBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });
            
            // Auto-hide sidebar when resizing to mobile
            window.addEventListener('resize', function() {
                const sidebar = document.querySelector('.sidebar');
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                }
            });
        </script>
    </body>
</html>