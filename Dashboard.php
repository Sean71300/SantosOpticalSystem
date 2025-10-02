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
</head>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                        <a href="logs.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> Show All Logs
                        </a>
                    </div>
                        <div class="list-group list-group-flush recent-activity-list" style="max-height:360px; overflow:auto; padding-right:6px;">
                            <?php
                            if (empty($recentActivities)) {
                                echo '<div class="text-center text-muted py-3">No recent activity.</div>';
                            } else {
                                $seen = [];
                                $shown = 0;
                                $maxShow = 20;
                                foreach ($recentActivities as $activity) {
                                    if ($shown >= $maxShow) break;
                                    $ts = strtotime($activity['Upd_dt']);
                                    $timeLabel = $ts ? date('M j, g:i A', $ts) : htmlspecialchars($activity['Upd_dt']);
                                    $desc = trim($activity['Description']);
                                    $targetType = trim($activity['TargetType'] ?? '');
                                    $targetId = trim($activity['TargetID'] ?? '');
                                    $message = trim($desc . ' ' . $targetType . ($targetId !== '' ? ' # ' . $targetId : ''));
                                    // dedupe by message + timestamp
                                    $key = md5($timeLabel . '|' . $message);
                                    if (isset($seen[$key])) continue;
                                    $seen[$key] = true;
                                    $isNew = ($ts !== false) && (time() - $ts) <= 86400;
                                    $displayMessage = htmlspecialchars($message);
                                    $displayTime = htmlspecialchars($timeLabel);
                                    echo '<a href="logs.php" class="list-group-item list-group-item-action py-3 border-bottom d-flex justify-content-between align-items-center">';
                                    echo '<div class="flex-grow-1 pe-2">';
                                    echo '<div class="small text-muted mb-1">' . $displayTime . '</div>';
                                    echo '<div class="text-truncate" style="max-width:240px">' . $displayMessage . '</div>';
                                    echo '</div>';
                                    if ($isNew) echo '<span class="badge bg-primary rounded-pill ms-2">New</span>';
                                    echo '</a>';
                                    $shown++;
                                }
                            }
                            ?>
                        </div>
                        
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

            // Simplified: only view and year/month selectors remain. Chart will request explicit start/end dates.
            let currentView = 'week';

            function setActiveViewButton(btn) {
                document.querySelectorAll('.sales-view-btn').forEach(b => b.classList.remove('active'));
                if (btn) btn.classList.add('active');
            }

            function computeStartEndForView(view) {
                const now = new Date();
                const yearSel = document.getElementById('sales-year-select');
                const monthSel = document.getElementById('sales-month-select');
                const year = yearSel ? parseInt(yearSel.value, 10) : now.getFullYear();
                const month = monthSel && monthSel.value ? parseInt(monthSel.value, 10) : null;
                let start = null; let end = null;
                if (view === 'year') {
                    start = new Date(year, 0, 1);
                    end = new Date(year, 11, 31);
                } else if (view === 'month') {
                    const m = month !== null ? month - 1 : now.getMonth();
                    start = new Date(year, m, 1);
                    end = new Date(year, m + 1, 0);
                } else { // week
                    const m = month !== null ? month - 1 : now.getMonth();
                    // default to current week within the chosen month: use first day of month to find week
                    const first = new Date(year, m, 1);
                    const day = first.getDay();
                    // start at first day
                    start = first;
                    end = new Date(start);
                    end.setDate(start.getDate() + 6);
                }
                return { start: start.toISOString().slice(0,10), end: end.toISOString().slice(0,10) };
            }

            async function loadSalesRange() {
                try {
                    const params = new URLSearchParams();
                    const yearSel = document.getElementById('sales-year-select');
                    params.set('year', yearSel ? yearSel.value : new Date().getFullYear());
                    const se = computeStartEndForView(currentView);
                    params.set('start', se.start);
                    params.set('end', se.end);

                    // no extra mode param (week-based mode removed)
                    const resp = await fetch('salesData.php?' + params.toString());
                    const text = await resp.text();
                    let json;
                    try { json = JSON.parse(text); } catch (e) { throw new Error('Invalid JSON: ' + text); }
                    if (!json.success) throw new Error(json.error || 'Failed to load sales data');

                    salesChart.data.labels = json.labels;
                    salesChart.data.datasets[0].data = json.claimed.map(v=>parseInt(v||0,10));
                    salesChart.data.datasets[1].data = json.cancelled.map(v=>parseInt(v||0,10));
                    salesChart.data.datasets[2].data = json.returned.map(v=>parseInt(v||0,10));
                    salesChart.update();
                    // update header with selected date range
                    const title = document.getElementById('sales-overview-title');
                    if (title) {
                        // format dates like 'Mar 3, 2025' or range 'Mar 3 - Sep 7, 2025'
                        function fmt(d) { const dt = new Date(d); return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }); }
                        const startF = fmt(json.start);
                        const endF = fmt(json.end);
                        title.textContent = `Sales Overview — ${startF} to ${endF}`;
                    }
                } catch (err) {
                    console.error('Error loading sales range:', err);
                }
            }

            // View button handlers
            document.querySelectorAll('.sales-view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentView = this.dataset.view;
                    // clear mode dropdown when switching view
                    const modeSelect = document.getElementById('sales-mode-select');
                    if (modeSelect) modeSelect.value = '';
                    setActiveViewButton(this);
                    loadSalesRange();
                });
            });

            // week-mode removed; no mode dropdown handler required

            // initialize year/month selects and load
            const yearSelectInit = document.getElementById('sales-year-select');
            if (yearSelectInit && !yearSelectInit.dataset.initialized) {
                const thisYear = new Date().getFullYear();
                for (let y = thisYear; y >= thisYear - 7; y--) {
                    const opt = document.createElement('option'); opt.value = y; opt.textContent = y;
                    yearSelectInit.appendChild(opt);
                }
                yearSelectInit.value = new Date().getFullYear();
                yearSelectInit.dataset.initialized = '1';
                yearSelectInit.addEventListener('change', loadSalesRange);
            }
            const monthInit = document.getElementById('sales-month-select');
            if (monthInit && monthInit.options.length <= 1) {
                const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                for (let m = 1; m <= 12; m++) { const o = document.createElement('option'); o.value = m; o.textContent = monthNames[m-1]; monthInit.appendChild(o); }
                monthInit.addEventListener('change', loadSalesRange);
                monthInit.style.display = '';
            }
            loadSalesRange();
        });
    </script>
</body>
</html>