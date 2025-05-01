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
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <title>Admin | Dashboard</title>
        <style>
            body {
                background-color: #f5f7fa;
                padding-top: 60px;
            }
            .sidebar {
                background-color: white;
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;
                position: fixed;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
                z-index: 1000;
                top: 0;
                left: 0;
                transition: transform 0.3s ease;
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
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
                width: calc(100% - 250px);
                transition: margin 0.3s ease;
            }
            .dashboard-card {
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                padding: 20px;
                background-color: white;
                transition: transform 0.3s;
            }
            .dashboard-card:hover {
                transform: translateY(-5px);
            }
            .card-icon {
                font-size: 2rem;
                margin-bottom: 15px;
            }
            .stat-number {
                font-size: 2rem;
                font-weight: bold;
            }
            .recent-activity {
                max-height: 400px;
                overflow-y: auto;
            }
            
            /* Mobile styles */
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
                .mobile-menu-toggle {
                    display: block !important;
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
            }
            
            /* Fix for logout button */
            .sidebar-footer {
                position: fixed;
                bottom: 0;
                width: 250px;
                background: white;
                padding: 10px 0;
                box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
            }
            
            /* Responsive cards */
            @media (max-width: 768px) {
                .col-md-3 {
                    flex: 0 0 50%;
                    max-width: 50%;
                }
                .stat-number {
                    font-size: 1.5rem;
                }
            }
            @media (max-width: 576px) {
                .col-md-3 {
                    flex: 0 0 100%;
                    max-width: 100%;
                }
                .main-content {
                    padding: 15px;
                }
            }
        </style>
    </head>

    <body>
        <?php include "sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php
                $username = $_SESSION["username"];
                echo "<h2 class='mb-4'>Welcome back, $username</h2>";
            ?>
            
            <!-- Dashboard Cards -->
            <div class="row">
                <!-- Customers Card -->
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3">
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
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <h5><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                        <div class="mt-3" style="height: 300px; background-color: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            [Sales Chart Placeholder]
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="dashboard-card recent-activity">    
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fa-solid fa-circle-exclamation me-2"></i>Low Stocks</h5>
                            <a href="admin-inventory.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-boxes-stacked"></i> Show Inventory
                            </a>
                        </div>
                        <hr class="border-1 border-black opacity-25">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="row">
                                    <?php
                                    if (count($lowInventory) > 0) {
                                        foreach ($lowInventory as $product) {
                                            echo '<div class="container d-flex align-items-center">';
                                            echo '<img src="' . htmlspecialchars($product['ProductImage']) . '" alt="Product Image" style="height:100px; width:100px;" class="img-thumbnail">';
                                                echo '<div class="fw-bold ms-3">';
                                                    echo htmlspecialchars($product['Model']);
                                                    echo "<br> Available Stocks: ".htmlspecialchars($product['Stocks']);
                                                echo '</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p class="text-center">No low stock products.</p>';
                                    } 
                                    ?>
                                </div>
                            </li>
                        </ul>
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
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <small><?php echo date('M j, g:i A', strtotime($activity['Upd_dt'])); ?></small>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const mobileToggle = document.getElementById('mobileMenuToggle');
                const body = document.body;
                
                // Toggle sidebar on mobile
                if (mobileToggle) {
                    mobileToggle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sidebar.classList.toggle('active');
                        body.classList.toggle('sidebar-open');
                    });
                }
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 992 && 
                        !sidebar.contains(e.target) && 
                        (!mobileToggle || e.target !== mobileToggle)) {
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
    </body>
</html>