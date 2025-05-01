<?php
include_once 'setup.php';
header('Content-Type: application/json');

// Check authentication
session_start();
if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// Get period from request (default to 30 days)
$period = isset($_GET['period']) ? (int)$_GET['period'] : 30;
$period = max(7, min(365, $period)); // Limit between 7 and 365 days

$conn = connect();

$data = [
    'labels' => [],
    'values' => []
];

// Get sales data for the selected period from Order_hdr and orderDetails
$query = "SELECT 
            DATE(o.Created_dt) as day, 
            SUM(od.Quantity * (REPLACE(REPLACE(p.Price, '₱', ''), ',', '')) as total 
          FROM Order_hdr o
          JOIN orderDetails od ON o.Orderhdr_id = od.OrderHdr_id
          JOIN ProductBranchMaster pbm ON od.ProductBranchID = pbm.ProductBranchID
          JOIN productMstr p ON pbm.ProductID = p.ProductID
          WHERE o.Created_dt >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          AND od.ActivityCode = 2  -- Only include orders with ActivityCode = 1 (Ordered)
          GROUP BY DATE(o.Created_dt) 
          ORDER BY day ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $period);
$stmt->execute();
$result = $stmt->get_result();

// Initialize array with all dates in period
$salesByDay = [];
while ($row = $result->fetch_assoc()) {
    $salesByDay[$row['day']] = (float)$row['total'];
}

// Create DatePeriod for the selected range
$startDate = new DateTime("-$period days");
$endDate = new DateTime('tomorrow');
$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($startDate, $interval, $endDate);

// Fill in all dates (including those with no sales)
foreach ($dateRange as $date) {
    $dateStr = $date->format('Y-m-d');
    $data['labels'][] = $date->format('M j');
    $data['values'][] = $salesByDay[$dateStr] ?? 0;
}

$conn->close();

echo json_encode($data);
?>