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

// Get sales data for the selected period
$query = "SELECT 
            DATE(OrderDate) as day, 
            SUM(TotalAmount) as total 
          FROM orders 
          WHERE OrderDate >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          GROUP BY DATE(OrderDate) 
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