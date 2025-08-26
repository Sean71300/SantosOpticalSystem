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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">View</h6>
                                <div>
                                    <div class="btn-group" role="group" aria-label="Sales view">
                                        <button type="button" class="btn btn-sm btn-outline-secondary sales-view-btn active" data-view="week">Week</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary sales-view-btn" data-view="month">Month</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary sales-view-btn" data-view="year">Year</button>
                                    </div>
                                    <div id="sales-range-controls" class="d-inline-block ms-2">
                                        <!-- Range controls injected by JS -->
                                    </div>
                                </div>
                            </div>
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

            // initial placeholder data (will be overwritten by API)
            const salesData = {
                labels: [],
                datasets: [
                    { label: 'Sold', data: [], backgroundColor: 'rgba(40,167,69,0.15)', borderColor: 'rgba(40,167,69,1)', tension: 0.1, fill: true },
                    { label: 'Cancelled', data: [], backgroundColor: 'rgba(220,53,69,0.15)', borderColor: 'rgba(220,53,69,1)', tension: 0.1, fill: true },
                    { label: 'Returned', data: [], backgroundColor: 'rgba(255,193,7,0.15)', borderColor: 'rgba(255,193,7,1)', tension: 0.1, fill: true }
                ]
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
                                    const label = context.dataset && context.dataset.label ? context.dataset.label : '';
                                    const raw = context.parsed && (context.parsed.y !== undefined ? context.parsed.y : context.parsed);
                                    const value = (raw === undefined || raw === null) ? 0 : raw;
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Sales view and range controls
            let currentView = 'week';
            let currentRangeStart = null;
            let currentRangeEnd = null;

            function setActiveViewButton(btn) {
                document.querySelectorAll('.sales-view-btn').forEach(b => b.classList.remove('active'));
                if (btn) btn.classList.add('active');
            }

            function renderRangeControls(view) {
                const container = document.getElementById('sales-range-controls');
                container.innerHTML = '';
                if (view === 'week') {
                    // day buttons 1..7
                    for (let d = 1; d <= 7; d++) {
                        const b = document.createElement('button');
                        b.className = 'btn btn-sm btn-outline-secondary me-1 sales-range-btn-day';
                        b.textContent = d;
                        b.dataset.day = d;
                        container.appendChild(b);
                    }
                    container.insertAdjacentHTML('beforeend', '<small class="ms-2">Select day range: click start then end</small>');
                } else if (view === 'month') {
                    // weeks 1..5
                    for (let w = 1; w <= 5; w++) {
                        const b = document.createElement('button');
                        b.className = 'btn btn-sm btn-outline-secondary me-1 sales-range-btn-week';
                        b.textContent = 'W' + w;
                        b.dataset.week = w;
                        container.appendChild(b);
                    }
                    container.insertAdjacentHTML('beforeend', '<small class="ms-2">Select week range: click start then end</small>');
                } else {
                    // year months 1..12
                    for (let m = 1; m <= 12; m++) {
                        const b = document.createElement('button');
                        b.className = 'btn btn-sm btn-outline-secondary me-1 sales-range-btn-month';
                        b.textContent = m;
                        b.dataset.month = m;
                        container.appendChild(b);
                    }
                    container.insertAdjacentHTML('beforeend', '<small class="ms-2">Select month range: click start then end</small>');
                }
                attachRangeSelectionHandlers(view);
            }

            function attachRangeSelectionHandlers(view) {
                let start = null;
                let end = null;
                function clearSelection() {
                    start = null; end = null; currentRangeStart = null; currentRangeEnd = null;
                    document.querySelectorAll('#sales-range-controls .active').forEach(n=>n.classList.remove('active'));
                }

                if (view === 'week') {
                    document.querySelectorAll('.sales-range-btn-day').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const v = parseInt(this.dataset.day, 10);
                            if (start === null) { start = v; this.classList.add('active'); }
                            else if (end === null) { end = v; this.classList.add('active'); }
                            else { clearSelection(); start = v; this.classList.add('active'); }
                            if (start !== null && end !== null) {
                                if (end < start) [start, end] = [end, start];
                                currentRangeStart = start; currentRangeEnd = end;
                                loadSalesRange();
                            }
                        });
                    });
                } else if (view === 'month') {
                    document.querySelectorAll('.sales-range-btn-week').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const v = parseInt(this.dataset.week, 10);
                            if (start === null) { start = v; this.classList.add('active'); }
                            else if (end === null) { end = v; this.classList.add('active'); }
                            else { clearSelection(); start = v; this.classList.add('active'); }
                            if (start !== null && end !== null) {
                                if (end < start) [start, end] = [end, start];
                                currentRangeStart = start; currentRangeEnd = end;
                                loadSalesRange();
                            }
                        });
                    });
                } else {
                    document.querySelectorAll('.sales-range-btn-month').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const v = parseInt(this.dataset.month, 10);
                            if (start === null) { start = v; this.classList.add('active'); }
                            else if (end === null) { end = v; this.classList.add('active'); }
                            else { clearSelection(); start = v; this.classList.add('active'); }
                            if (start !== null && end !== null) {
                                if (end < start) [start, end] = [end, start];
                                currentRangeStart = start; currentRangeEnd = end;
                                loadSalesRange();
                            }
                        });
                    });
                }

                // double-click or outside click clears selection
                document.addEventListener('dblclick', clearSelection);
            }

            async function loadSalesRange() {
                try {
                    const params = new URLSearchParams();
                    params.set('view', currentView);
                    if (currentRangeStart !== null) params.set('rangeStart', currentRangeStart);
                    if (currentRangeEnd !== null) params.set('rangeEnd', currentRangeEnd);

                    const resp = await fetch('salesData.php?' + params.toString());
                    const json = await resp.json();
                    if (!json.success) throw new Error('Failed to load sales data');

                    salesChart.data.labels = json.labels;
                    salesChart.data.datasets[0].data = json.claimed.map(v=>parseInt(v||0,10));
                    salesChart.data.datasets[1].data = json.cancelled.map(v=>parseInt(v||0,10));
                    salesChart.data.datasets[2].data = json.returned.map(v=>parseInt(v||0,10));
                    salesChart.update();
                } catch (err) {
                    console.error('Error loading sales range:', err);
                    alert('Could not load sales data. Check console for details.');
                }
            }

            // View button handlers
            document.querySelectorAll('.sales-view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentView = this.dataset.view;
                    setActiveViewButton(this);
                    renderRangeControls(currentView);
                    // reset selection
                    currentRangeStart = null; currentRangeEnd = null;
                    loadSalesRange();
                });
            });

            // initialize
            renderRangeControls(currentView);
            loadSalesRange();
        });
    </script>
</body>
</html>