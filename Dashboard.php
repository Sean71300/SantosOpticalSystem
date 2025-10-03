<?php
include 'setup.php';
include 'ActivityTracker.php';
include 'loginChecker.php';
include 'adminFunctions.php';

$isAdmin = false;
// Consider roleid 1 (Admin) and roleid 4 (Super Admin) as admin-level for dashboard access
if (isset($_SESSION['roleid'])) {
    $rid = (int)$_SESSION['roleid'];
    if ($rid === 1 || $rid === 4) {
        $isAdmin = true;
    }
}

// Get all counts
$customerCount = getCustomerCount();
$employeeCount = getEmployeeCount();
$inventoryCount = getInventoryCount();
$orderCount = getOrderCount();
$claimedOrderCount = getClaimedOrderCount();
$recentActivities = getRecentActivities();
$lowInventory = getLowInventoryProducts();
$salesData = getSalesOverviewData();
// Defensive guards
if (!is_array($lowInventory)) { $lowInventory = []; }
if (!is_numeric($customerCount)) { $customerCount = 0; }
if (!is_numeric($employeeCount)) { $employeeCount = 0; }
if (!is_numeric($inventoryCount)) { $inventoryCount = 0; }
if (!is_numeric($orderCount)) { $orderCount = 0; }
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
        /* Equal-height helpers for admin view */
        .equal-height-row { align-items: stretch; }
    .equal-height { height: 100%; display: flex; flex-direction: column; width: 100%; flex: 1 1 auto; }
        .scroll-section { flex: 1 1 auto; overflow: auto; }
    .chart-container { height: 300px; width: 100%; }
    /* Low Stocks half-height (admin view) */
    .low-stocks-half.equal-height { height: 50% !important; flex: 0 0 auto; }
    .low-stocks-half .scroll-section { flex: 1 1 auto; overflow: auto; }
        .card-icon.text-success { color: #28a745 !important; }
        .btn-outline-success { color: #28a745; border-color: #28a745; }
        .btn-outline-success:hover { color: #fff; background-color: #28a745; border-color: #28a745; }
    /* Horizontal Low Stocks strip */
    .low-stocks-strip { display: flex; flex-direction: row; flex-wrap: nowrap; gap: 12px; overflow-x: auto; overflow-y: hidden; padding-bottom: 6px; }
    .low-stocks-item { min-width: 280px; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #fff; display: flex; align-items: center; }
    .low-stocks-item img { height: 80px; width: 80px; object-fit: cover; }
    .low-stocks-item .meta { font-weight: 600; margin-left: 10px; }
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
        <?php 
            // Determine employee-only early so we can switch layouts
            $ridLocal = isset($_SESSION['roleid']) ? (int)$_SESSION['roleid'] : 0; 
            $isEmployeeOnly = ($ridLocal === 2) && !$isAdmin; 
        ?>

        <?php if (!$isEmployeeOnly): ?>
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

            <!-- Inventory card (visible to all roles) -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-warning"><i class="fas fa-boxes-stacked"></i></div>
                    <h5>Inventory</h5>
                    <div class="stat-number"><?php echo number_format($inventoryCount); ?></div>
                    <a href="<?php echo ($isAdmin) ? 'admin-inventory.php' : 'Employee-inventory.php'; ?>" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                </div>
            </div>

            <!-- Orders card (visible to all roles) -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="card-icon text-info"><i class="fas fa-receipt"></i></div>
                    <h5>Total Orders</h5>
                    <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                    <a href="order.php" class="btn btn-sm btn-outline-info mt-2">View All</a>
                </div>
            </div>
            <?php // $isEmployeeOnly already computed above ?>
            <?php if ($isAdmin): ?>
                <div class="row mt-4 equal-height-row">
                    <div class="col-md-9 col-lg-9 d-flex">
                        <div class="dashboard-card equal-height">
                            <h5 id="sales-overview-title"><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                            <hr class="border-1 border-black opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div></div>
                                <div class="d-flex align-items-center">
                                    <select id="sales-month-select" class="form-select form-select-sm d-inline-block" style="width:150px;"></select>
                                    <select id="sales-week-select" class="form-select form-select-sm d-inline-block ms-2" style="width:140px;">
                                        <option value="week1">Week 1</option>
                                        <option value="week2">Week 2</option>
                                        <option value="week3">Week 3</option>
                                        <option value="week4">Week 4</option>
                                        <option value="month">Whole Month</option>
                                    </select>
                                    <select id="sales-year-select" class="form-select form-select-sm d-inline-block ms-2" style="width:110px;"></select>
                                </div>
                            </div>
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                            <div class="mt-3">
                                <div id="net-summary" class="mb-2"></div>
                                <h6 class="mb-2">Top Products</h6>
                                <ul id="top-products" class="list-group list-group-flush"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-3 d-flex">
                        <div class="dashboard-card equal-height low-stocks-half">    
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fa-solid fa-circle-exclamation me-2"></i>Low Stocks</h5>
                                <a href="<?php echo ($isAdmin) ? 'admin-inventory.php' : 'Employee-inventory.php'; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-boxes-stacked"></i> Show Inventory
                                </a>
                            </div>
                            <hr class="border-1 border-black opacity-25">
                            <div class="scroll-section">
                                    <div class="low-stocks-strip">
                                        <?php
                                        if (count($lowInventory) > 0) {
                                            foreach ($lowInventory as $product) {
                                                $img = !empty($product['ProductImage']) ? $product['ProductImage'] : 'Images/logo.png';
                                                echo '<div class="low-stocks-item">';
                                                echo '<img src="' . htmlspecialchars($img) . '" alt="Product Image" class="img-thumbnail me-2">';
                                                echo '<div class="meta">';
                                                echo htmlspecialchars($product['Model']);
                                                echo "<br><span class=\"text-muted\">Available Stocks: " . htmlspecialchars($product['Stocks']) . "</span>";
                                                echo '</div>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<div class="text-center text-muted w-100">No low stock products.</div>';
                                        }
                                        ?>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($isEmployeeOnly): ?>
                <!-- Employee: first row with 3 stat cards, second row full-width Low Stocks -->
                <div class="row g-4 mt-1">
                    <!-- Row 1: Customers, Inventory, Total Orders -->
                    <div class="col-12 col-md-4 d-flex">
                        <div class="dashboard-card equal-height w-100">
                            <div class="card-icon text-primary"><i class="fas fa-users"></i></div>
                            <h5>Customers</h5>
                            <div class="stat-number"><?php echo number_format($customerCount); ?></div>
                            <div class="mt-auto">
                                <a href="customerRecords.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 d-flex">
                        <div class="dashboard-card equal-height w-100">
                            <div class="card-icon text-warning"><i class="fas fa-boxes-stacked"></i></div>
                            <h5>Inventory</h5>
                            <div class="stat-number"><?php echo number_format($inventoryCount); ?></div>
                            <div class="mt-auto">
                                <a href="Employee-inventory.php" class="btn btn-sm btn-outline-warning">View All</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 d-flex">
                        <div class="dashboard-card equal-height w-100">
                            <div class="card-icon text-info"><i class="fas fa-receipt"></i></div>
                            <h5>Total Orders</h5>
                            <div class="stat-number"><?php echo number_format($orderCount); ?></div>
                            <div class="mt-auto">
                                <a href="order.php" class="btn btn-sm btn-outline-info">View All</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Low Stocks full width -->
                <div class="row g-4 mt-1">
                    <div class="col-12 d-flex">
                        <div class="dashboard-card equal-height w-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><i class="fa-solid fa-circle-exclamation me-2"></i>Low Stocks</h5>
                                <a href="Employee-inventory.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-boxes-stacked"></i> Show Inventory
                                </a>
                            </div>
                            <hr class="border-1 border-black opacity-25">
                            <div class="scroll-section">
                                    <div class="low-stocks-strip">
                                        <?php
                                        if (count($lowInventory) > 0) {
                                            foreach ($lowInventory as $product) {
                                                $img = !empty($product['ProductImage']) ? $product['ProductImage'] : 'Images/logo.png';
                                                echo '<div class="low-stocks-item">';
                                                echo '<img src="' . htmlspecialchars($img) . '" alt="Product Image" class="img-thumbnail me-2">';
                                                echo '<div class="meta">';
                                                echo htmlspecialchars($product['Model']);
                                                echo "<br><span class=\"text-muted\">Available Stocks: " . htmlspecialchars($product['Stocks']) . "</span>";
                                                echo '</div>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<div class="text-center text-muted w-100">No low stock products.</div>';
                                        }
                                        ?>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                    <div class="dashboard-card mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                            <a href="logs.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list me-1"></i> Show All Logs
                            </a>
                        </div>
                        <hr class="border-1 border-black opacity-25">
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
                    </div>
            <?php endif; ?>
            </div>
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
                data: { labels: [], datasets: [
                    { label: 'Sold', data: [], backgroundColor: 'rgba(40,167,69,0.15)', borderColor: 'rgba(40,167,69,1)', tension: 0.1, fill: true },
                    { label: 'Cancelled', data: [], backgroundColor: 'rgba(220,53,69,0.12)', borderColor: 'rgba(220,53,69,1)', tension: 0.1, fill: true },
                    { label: 'Returned', data: [], backgroundColor: 'rgba(255,193,7,0.12)', borderColor: 'rgba(255,193,7,1)', tension: 0.1, fill: true }
                ] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Quantity' } }, x: { title: { display: true, text: 'Date' } } },
                    plugins: { legend: { position: 'top' } }
                }
            }) : null;

            // Ensure the chart uses the full width of its container (fixes right-side whitespace)
            if (salesChart) {
                const canvas = document.getElementById('salesChart');
                const container = canvas ? canvas.parentElement : null;
                // Resize once after layout settles
                setTimeout(() => { try { salesChart.resize(); } catch (_) {} }, 60);
                // Resize on window resize
                window.addEventListener('resize', () => { try { salesChart.resize(); } catch (_) {} });
                // Resize when the container size changes
                if (container && 'ResizeObserver' in window) {
                    const ro = new ResizeObserver(() => { try { salesChart.resize(); } catch (_) {} });
                    ro.observe(container);
                }
            }

            // New: populate month/year selectors and compute start/end from 3 selectors (month, week segment, year)
            function formattedLabels(labels) {
                const n = labels.length;
                return labels.map(d => {
                    const dt = new Date(d + 'T00:00:00');
                    if (n <= 14) return dt.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric' }).replace(',', '');
                    if (n <= 62) return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                    return dt.toLocaleDateString(undefined, { month: 'short' });
                });
            }

            function populateMonthYearSelectors() {
                const monthSel = document.getElementById('sales-month-select');
                const yearSel = document.getElementById('sales-year-select');
                if (!monthSel || !yearSel) return;

                const monthNames = [];
                for (let m = 0; m < 12; m++) {
                    const dt = new Date(2000, m, 1);
                    monthNames.push(dt.toLocaleString(undefined, { month: 'long' }));
                }
                monthSel.innerHTML = '';
                // add Whole Year option first
                const wholeYearOpt = document.createElement('option');
                wholeYearOpt.value = 'whole-year';
                wholeYearOpt.textContent = 'Whole Year';
                monthSel.appendChild(wholeYearOpt);
                monthNames.forEach((name, idx) => {
                    const opt = document.createElement('option');
                    opt.value = (idx + 1).toString();
                    opt.textContent = name;
                    monthSel.appendChild(opt);
                });

                const now = new Date();
                const thisYear = now.getFullYear();
                yearSel.innerHTML = '';
                // add All Time option first
                const allOpt = document.createElement('option');
                allOpt.value = 'all';
                allOpt.textContent = 'All Time';
                yearSel.appendChild(allOpt);
                for (let y = thisYear; y >= thisYear - 4; y--) {
                    const opt = document.createElement('option');
                    opt.value = y.toString();
                    opt.textContent = y.toString();
                    yearSel.appendChild(opt);
                }

                monthSel.value = (now.getMonth() + 1).toString();
                yearSel.value = now.getFullYear().toString();
            }

            function computeStartEndFromSelectors() {
                const monthSel = document.getElementById('sales-month-select');
                const weekSel = document.getElementById('sales-week-select');
                const yearSel = document.getElementById('sales-year-select');
                const monthVal = monthSel ? monthSel.value : null;
                const yearVal = yearSel ? yearSel.value : null;
                const week = weekSel ? weekSel.value : 'month';

                const now = new Date();

                // All time
                if (yearVal === 'all') {
                    const start = new Date(2015, 0, 1);
                    const end = now;
                    return { start: start.toISOString().slice(0,10), end: end.toISOString().slice(0,10) };
                }

                // parse numeric month/year
                const month = monthVal && monthVal !== 'whole-year' ? parseInt(monthVal, 10) : null;
                const year = yearVal ? parseInt(yearVal, 10) : now.getFullYear();

                // If whole-year selected
                if (monthVal === 'whole-year') {
                    const startY = new Date(year, 0, 1);
                    const endY = new Date(year, 11, 31);
                    return { start: startY.toISOString().slice(0,10), end: endY.toISOString().slice(0,10) };
                }

                // Fallback if month/year not numeric
                const firstDay = new Date(year, (month || (now.getMonth()+1)) - 1, 1);
                const lastDay = new Date(year, (month || (now.getMonth()+1)), 0);

                let start = new Date(firstDay);
                let end = new Date(lastDay);

                if (week === 'week1') {
                    start = new Date(year, month - 1, 1);
                    end = new Date(year, month - 1, Math.min(7, lastDay.getDate()));
                } else if (week === 'week2') {
                    start = new Date(year, month - 1, 8);
                    end = new Date(year, month - 1, Math.min(14, lastDay.getDate()));
                } else if (week === 'week3') {
                    start = new Date(year, month - 1, 15);
                    end = new Date(year, month - 1, Math.min(21, lastDay.getDate()));
                } else if (week === 'week4') {
                    start = new Date(year, month - 1, 22);
                    end = new Date(year, month - 1, lastDay.getDate());
                } else if (week === 'month') {
                    start = new Date(firstDay);
                    end = new Date(lastDay);
                }

                return { start: start.toISOString().slice(0,10), end: end.toISOString().slice(0,10) };
            }

            async function loadSalesRange() {
                try {
                    const se = computeStartEndFromSelectors();
                    const params = new URLSearchParams({ start: se.start, end: se.end });
                    const url = 'salesOverview.php?' + params.toString();
                    console.debug('[Sales] request=', url);
                    const resp = await fetch(url, { cache: 'no-store' });
                    const json = await resp.json();
                    console.debug('[Sales] response=', json);
                    if (!json.success) throw new Error(json.error || 'Failed to load sales');

                    if (salesChart) {
                        salesChart.data.labels = formattedLabels(json.labels);
                        salesChart.data.datasets[0].data = json.sold.map(v => parseInt(v||0,10));
                        salesChart.data.datasets[1].data = json.cancelled.map(v => parseInt(v||0,10));
                        salesChart.data.datasets[2].data = json.returned.map(v => parseInt(v||0,10));
                        salesChart.update();
                    }

                    // Top products: show three lists (sold, cancelled, returned)
                    const topContainer = document.getElementById('top-products');
                    if (topContainer) {
                        topContainer.innerHTML = '';
                        const sections = [ ['Sold', 'sold', 'primary'], ['Cancelled', 'cancelled', 'danger'], ['Returned', 'returned', 'warning'] ];
                        sections.forEach(([label, key, badgeClass]) => {
                            const header = document.createElement('li');
                            header.className = 'list-group-item bg-light';
                            header.innerHTML = `<strong>${label}</strong>`;
                            topContainer.appendChild(header);
                            const listItems = (json.topProducts && json.topProducts[key]) ? json.topProducts[key] : [];
                            if (listItems.length === 0) {
                                const empty = document.createElement('li');
                                empty.className = 'list-group-item text-muted';
                                empty.textContent = 'No items';
                                topContainer.appendChild(empty);
                            } else {
                                listItems.forEach(p => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                                    li.textContent = p.Model || ('Product ' + p.ProductID);
                                    const span = document.createElement('span');
                                    span.className = 'badge bg-' + badgeClass + ' rounded-pill';
                                    span.textContent = p.qty;
                                    li.appendChild(span);
                                    topContainer.appendChild(li);
                                });
                            }
                        });
                    }

                    const title = document.getElementById('sales-overview-title');
                    if (title) {
                        const startF = new Date(json.start).toLocaleDateString();
                        const endF = new Date(json.end).toLocaleDateString();
                        title.textContent = `Sales Overview — ${startF} to ${endF}`;
                    }

                    // Net gain / loss display
                    const netEl = document.getElementById('net-summary');
                    if (netEl) {
                        // salesOverview.php now returns net_gained (only sold revenue). Always show Net Gained.
                        const net = parseFloat(json.net_gained || json.net_total || 0);
                        const fmt = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 });
                        netEl.innerHTML = `<span class="badge bg-success">Net Gained: ${fmt.format(net)}</span>`;
                    }
                } catch (err) {
                    console.error('Error loading sales range:', err);
                }
            }

            // populate selectors and wire change events with state management
            populateMonthYearSelectors();

            function updateSelectorStates() {
                const monthSel = document.getElementById('sales-month-select');
                const weekSel = document.getElementById('sales-week-select');
                const yearSel = document.getElementById('sales-year-select');
                if (!monthSel || !weekSel || !yearSel) return;

                if (yearSel.value === 'all') {
                    // All Time: disable month and week
                    monthSel.disabled = true;
                    weekSel.disabled = true;
                } else {
                    monthSel.disabled = false;
                    // if Whole Year selected, week segments don't apply
                    if (monthSel.value === 'whole-year') {
                        weekSel.disabled = true;
                    } else {
                        weekSel.disabled = false;
                    }
                }
            }

            // initial state
            updateSelectorStates();

            const monthSel = document.getElementById('sales-month-select');
            const weekSel = document.getElementById('sales-week-select');
            const yearSel = document.getElementById('sales-year-select');
            if (monthSel) monthSel.addEventListener('change', function() { updateSelectorStates(); loadSalesRange(); });
            if (weekSel) weekSel.addEventListener('change', loadSalesRange);
            if (yearSel) yearSel.addEventListener('change', function() { updateSelectorStates(); loadSalesRange(); });

            // initial load
            loadSalesRange();
        });
    </script>
</body>
</html>