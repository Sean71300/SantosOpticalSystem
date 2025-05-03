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
$claimedOrderCount = getClaimedOrderCount(); // New claimed orders count
$recentActivities = getRecentActivities();
$lowInventory = getLowInventoryProducts();

function getClaimedOrderCount() {
    global $conn;
    
    $query = "SELECT COUNT(DISTINCT od.OrderHdr_id) as claimed_count 
              FROM orderDetails od
              WHERE od.Status = 'Complete'";
              
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['claimed_count'];
    }
    
    return 0;
}

function getSalesOverviewData($days = 7) {
    global $conn;
    
    $query = "SELECT 
                DATE(oh.Created_dt) as date, 
                SUM(od.Quantity) as total_sold 
              FROM orderDetails od
              JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
              WHERE oh.Created_dt >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY DATE(oh.Created_dt)
              ORDER BY date ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $salesData = [];
    while ($row = $result->fetch_assoc()) {
        $salesData[] = $row;
    }
    
    // Fill in missing days with 0 values
    $filledData = [];
    $currentDate = new DateTime("-" . ($days - 1) . " days");
    $endDate = new DateTime();
    
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $found = false;
        
        foreach ($salesData as $sale) {
            if ($sale['date'] == $dateStr) {
                $filledData[] = $sale;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $filledData[] = ['date' => $dateStr, 'total_sold' => 0];
        }
        
        $currentDate->modify('+1 day');
    }
    
    return $filledData;
}

$salesData = getSalesOverviewData();
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
            
            /* Chart container */
            .chart-container {
                height: 300px;
                width: 100%;
            }
            
            /* New styles for claimed orders */
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
            
            /* Mobile styles */
            @media (max-width: 992px) {
                .main-content {
                    margin-left: 0;
                    width: 100%;
                }
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
        <!-- Add Chart.js for the sales chart -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                
                <!-- Inventory Card -->
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
                
                <!-- Total Orders Card -->
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
                
                <!-- Claimed Orders Card -->
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <div class="card-icon text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h5>Claimed Orders</h5>
                        <div class="stat-number"><?php echo number_format($claimedOrderCount); ?></div>
                        <a href="order.php?status=Complete" class="btn btn-sm btn-outline-success mt-2">View Claimed</a>
                    </div>
                </div>
            </div>
            
            <!-- Sales Overview Section -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Sales Overview (Last 7 Days)</h5>
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

                // Sales Chart
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