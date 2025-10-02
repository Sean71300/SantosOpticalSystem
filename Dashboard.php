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
        body { background-color: #f5f7fa; padding-top: 60px; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); transition: margin 0.3s ease; }
        .dashboard-card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px; background-color: white; transition: transform 0.3s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .card-icon { font-size: 2rem; margin-bottom: 15px; }
        .stat-number { font-size: 2rem; font-weight: bold; }
        .recent-activity { max-height: 400px; overflow-y: auto; }
        .chart-container { height: 300px; width: 100%; }
        .card-icon.text-success { color: #28a745 !important; }
        .btn-outline-success { color: #28a745; border-color: #28a745; }
        .btn-outline-success:hover { color: #fff; background-color: #28a745; border-color: #28a745; }
        @media (max-width: 992px) { .main-content { margin-left: 0; width: 100%; } }
        @media (max-width: 768px) { .col-md-3 { flex: 0 0 50%; max-width: 50%; } .stat-number { font-size: 1.5rem; } }
        @media (max-width: 576px) { .col-md-3 { flex: 0 0 100%; max-width: 100%; } .main-content { padding: 15px; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php
            $username = $_SESSION["username"] ?? 'User';
            echo "<h2 class='mb-4'>Welcome back, " . htmlspecialchars($username) . "</h2>";
        ?>
        
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-primary"><i class="fas fa-users"></i></div>
                    <h5>Customers</h5>
                    <div class="stat-number"><?php echo number_format($customerCount); ?></div>
                    <a href="customerRecords.php" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-success"><i class="fas fa-user-tie"></i></div>
                    <h5>Employees</h5>
                    <div class="stat-number"><?php echo number_format($employeeCount); ?></div>
                    <a href="employeeRecords.php" class="btn btn-sm btn-outline-success mt-2">View All</a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-warning"><i class="fas fa-boxes"></i></div>
                    <h5>Inventory</h5>
                    <div class="stat-number"><?php echo number_format($inventoryCount); ?></div>
                    <a href="<?php echo ($isAdmin) ? 'admin-inventory.php' : 'Employee-inventory.php'; ?>" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-info"><i class="fas fa-shopping-cart"></i></div>
                    <h5>Total Orders</h5>
                    <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                    <a href="order.php" class="btn btn-sm btn-outline-info mt-2">View All</a>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h5 id="sales-overview-title"><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                    <hr class="border-1 border-black opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <!-- compact header left area (empty) -->
                        </div>
                        <div>
                            <!-- Add a compact dropdown to choose view: week / month / year -->
                            <select id="sales-mode-select" class="form-select form-select-sm" style="width:120px;">
                                <option value="week">Week</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <h6 class="mb-2">Top Products</h6>
                        <ul id="top-products" class="list-group list-group-flush"></ul>
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

    <!-- Script consolidated below (removed duplicate) -->
</body>
</html>
                            <select id="sales-year-select" class="form-select form-select-sm d-inline-block ms-2" style="width:auto;"></select>
                            <select id="sales-month-select" class="form-select form-select-sm d-inline-block ms-2" style="width:auto; display:none;"></select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <h6 class="mb-2">Top Products</h6>
                        <ul id="top-products" class="list-group list-group-flush"></ul>
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
                if (window.innerWidth <= 992 && !sidebar.contains(e.target) && (!mobileToggle || e.target !== mobileToggle)) {
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

            // Chart setup
            const salesCtx = document.getElementById('salesChart') ? document.getElementById('salesChart').getContext('2d') : null;
            const salesChart = salesCtx ? new Chart(salesCtx, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Sold', data: [], backgroundColor: 'rgba(40,167,69,0.15)', borderColor: 'rgba(40,167,69,1)', tension: 0.1, fill: true }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Products Sold' } }, x: { title: { display: true, text: 'Date' } } },
                    plugins: { legend: { position: 'top' } }
                }
            }) : null;

            let currentView = 'week';

            function computeStartEndForView(view) {
                const now = new Date();
                let start, end;
                if (view === 'year') {
                    start = new Date(now.getFullYear(), 0, 1);
                    end = new Date(now.getFullYear(), 11, 31);
                } else if (view === 'month') {
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                } else { // week
                    end = now;
                    start = new Date();
                    start.setDate(end.getDate() - 6);
                }
                return { start: start.toISOString().slice(0,10), end: end.toISOString().slice(0,10) };
            }

            function formattedLabels(labels) {
                const n = labels.length;
                return labels.map(d => {
                    const dt = new Date(d + 'T00:00:00');
                    if (n <= 14) return dt.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric' }).replace(',', '');
                    if (n <= 62) return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                    return dt.toLocaleDateString(undefined, { month: 'short' });
                });
            }

            async function loadSalesRange() {
                try {
                    const se = computeStartEndForView(currentView);
                    const params = new URLSearchParams({ start: se.start, end: se.end });
                    const url = 'salesOverview.php?' + params.toString();
                    // debug info: what view / url we are requesting
                    console.debug('[Sales] currentView=', currentView, 'request=', url);
                    const resp = await fetch(url, { cache: 'no-store' });
                    const json = await resp.json();
                    console.debug('[Sales] response=', json);
                    if (!json.success) throw new Error(json.error || 'Failed to load sales');

                    if (salesChart) {
                        salesChart.data.labels = formattedLabels(json.labels);
                        salesChart.data.datasets[0].data = json.sold.map(v => parseInt(v||0,10));
                        salesChart.update();
                    }

                    const topList = document.getElementById('top-products');
                    if (topList) {
                        topList.innerHTML = '';
                        if (json.topProducts && json.topProducts.length) {
                            json.topProducts.forEach(p => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                                li.textContent = p.Model || ('Product ' + p.ProductID);
                                const span = document.createElement('span');
                                span.className = 'badge bg-primary rounded-pill';
                                span.textContent = p.qty;
                                li.appendChild(span);
                                topList.appendChild(li);
                            });
                        } else {
                            topList.innerHTML = '<li class="list-group-item text-muted">No sales in range.</li>';
                        }
                    }

                    const title = document.getElementById('sales-overview-title');
                    if (title) {
                        const startF = new Date(json.start).toLocaleDateString();
                        const endF = new Date(json.end).toLocaleDateString();
                        title.textContent = `Sales Overview — ${startF} to ${endF}`;
                    }
                } catch (err) {
                    console.error('Error loading sales range:', err);
                }
            }

            // Wire dropdown
            const salesModeSelect = document.getElementById('sales-mode-select');
            if (salesModeSelect) {
                salesModeSelect.value = currentView;
                salesModeSelect.addEventListener('change', function() {
                    const v = this.value;
                    if (['week','month','year'].includes(v)) {
                        currentView = v;
                        loadSalesRange();
                    }
                });
            }

            // initial load
            loadSalesRange();
        });
    </script>
</body>
</html>