<?php
include 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'adminFunctions.php';

$isAdmin = isset($_SESSION['roleid']) && $_SESSION['roleid'] === 1;

// Get all counts (removed claimed order count)
$customerCount = getCustomerCount();
$employeeCount = getEmployeeCount();
$inventoryCount = getInventoryCount();
$orderCount = getOrderCount();
$recentActivities = getRecentActivities();
$lowInventory = getLowInventoryProducts();

// Modified function to only show claimed orders in sales data
function getSalesOverviewData($days = 7) {
    global $conn;
    
    $query = "SELECT 
                DATE(oh.Created_dt) as date, 
                SUM(od.Quantity) as total_sold 
              FROM orderDetails od
              JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
              WHERE oh.Created_dt >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND od.Status = 'Complete'  // Only count completed/claimed orders
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
        <!-- [Head content remains exactly the same] -->
    </head>

    <body>
        <?php include "sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php
                $username = $_SESSION["username"];
                echo "<h2 class='mb-4'>Welcome back, $username</h2>";
            ?>
            
            <!-- Dashboard Cards (removed Claimed Orders card) -->
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
                
                <!-- Orders Card -->
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <div class="card-icon text-info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h5>Orders</h5>
                        <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                        <a href="order.php" class="btn btn-sm btn-outline-info mt-2">View All</a>
                    </div>
                </div>
            </div>
            
            <!-- Sales Overview Section (now showing only claimed orders) -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Claimed Orders (Last 7 Days)</h5>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- [Rest of your content remains the same] -->
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // [Your existing sidebar toggle code remains the same]

                // Sales Chart - Now showing only claimed orders
                const salesData = {
                    labels: <?php echo json_encode(array_column($salesData, 'date')); ?>,
                    datasets: [{
                        label: 'Claimed Orders',
                        data: <?php echo json_encode(array_column($salesData, 'total_sold')); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgba(40, 167, 69, 1)',
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
                                    text: 'Claimed Orders'
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
                                        return context.parsed.y + ' claimed orders';
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