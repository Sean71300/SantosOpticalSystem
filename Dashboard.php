<?php
include 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'adminFunctions.php';

$isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;

// Get all counts
$customerCount = getCustomerCount();
$employeeCount = getEmployeeCount();
$inventoryCount = getInventoryCount();
$orderCount = getOrderCount();
$claimedOrderCount = getClaimedOrderCount();
$recentActivities = getRecentActivities();
$lowInventory = getLowInventoryProducts();
$salesData = getSalesOverviewData();

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
    <title>Dashboard</title>
    <style>
        body {
            background-color: #f5f7fa;
            padding-top: 60px;
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
        .chart-container {
            height: 300px;
            width: 100%;
        }
        .card-icon.text-success {
            color: #28a745 !important;
        }
        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-success:hover {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php
            $username = $_SESSION["username"];
            echo "<h2 class='mb-4'>Welcome back, $username</h2>";
        ?>
        
        <div class="row">
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
            
            <?php if ($isAdmin): ?>
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
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-warning">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h5>Inventory</h5>
                    <div class="stat-number"><?php echo number_format($inventoryCount); ?></div>
                    <a href="<?php echo ($isAdmin) ? 'admin-inventory.php' : 'Employee-inventory.php'; ?>" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-info">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h5>Total Orders</h5>
                    <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                    <a href="order.php" class="btn btn-sm btn-outline-info mt-2">View All</a>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h5><i class="fas fa-chart-line me-2"></i>Sales Overview (Last 7 Days)</h5>
                    <hr class="border-1 border-black opacity-25">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="dashboard-card recent-activity">    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fa-solid fa-circle-exclamation me-2"></i>Low Stocks</h5>
                        <a href="<?php echo ($isAdmin) ? 'admin-inventory.php' : 'Employee-inventory.php'; ?>" class="btn btn-sm btn-outline-secondary">
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
            <?php if ($isAdmin): ?>                    
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
                                <div><?php echo htmlspecialchars($activity['Description']) ." ". ($activity['TargetType']). " # " . ($activity['TargetID']); ?></div>
                            </div>
                            <span class="badge bg-primary rounded-pill">New</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
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

            const salesData = {
                labels: <?php echo json_encode(array_column($salesData, 'date')); ?>,
                datasets: [{
                    label: 'Products Sold',
                    data: <?php echo json_encode(array_column($salesData, 'total_sold')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            };

            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: salesData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Products Sold'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' products sold';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>