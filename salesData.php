<?php
include 'setup.php';
include 'adminFunctions.php';
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

// Parameters:
// view = 'week'|'month'|'year' (defaults to week)
// rangeStart, rangeEnd: integers representing selection within the view
// For week: day indices 1-7; month: week indices 1-5; year: month numbers 1-12

$view = $_GET['view'] ?? 'week';
$view = in_array($view, ['week','month','year']) ? $view : 'week';
$rangeStart = isset($_GET['rangeStart']) ? (int)$_GET['rangeStart'] : null;
$rangeEnd = isset($_GET['rangeEnd']) ? (int)$_GET['rangeEnd'] : null;

$now = new DateTime();

// Compute start and end dates based on view and ranges
if ($view === 'week') {
	// base week = current week (Monday to Sunday)
	$baseStart = new DateTime();
	$baseStart->modify('monday this week');
	$baseEnd = clone $baseStart;
	$baseEnd->modify('+6 days');

	if ($rangeStart !== null && $rangeEnd !== null) {
		$startDate = (clone $baseStart)->modify('+' . max(0, $rangeStart - 1) . ' days');
		$endDate = (clone $baseStart)->modify('+' . max(0, $rangeEnd - 1) . ' days');
	} else {
		// default last 7 days
		$endDate = new DateTime();
		$startDate = (clone $endDate)->modify('-6 days');
	}
	$groupBy = 'day';
} elseif ($view === 'month') {
	// current month
	$baseStart = new DateTime($now->format('Y-m-01'));
	$baseEnd = new DateTime($now->format('Y-m-t'));
	$lastDay = (int)$baseEnd->format('d');

	if ($rangeStart !== null && $rangeEnd !== null) {
		$startDay = max(1, ($rangeStart - 1) * 7 + 1);
		$endDay = min($lastDay, $rangeEnd * 7);
		$startDate = new DateTime($now->format('Y-m-') . str_pad($startDay, 2, '0', STR_PAD_LEFT));
		$endDate = new DateTime($now->format('Y-m-') . str_pad($endDay, 2, '0', STR_PAD_LEFT));
	} else {
		$startDate = $baseStart;
		$endDate = $baseEnd;
	}
	$groupBy = 'week-of-month';
} else { // year
	$year = (int)$now->format('Y');
	$baseStart = new DateTime($year . '-01-01');
	$baseEnd = new DateTime($year . '-12-31');

	if ($rangeStart !== null && $rangeEnd !== null) {
		$startMonth = max(1, min(12, $rangeStart));
		$endMonth = max(1, min(12, $rangeEnd));
		$startDate = new DateTime(sprintf('%04d-%02d-01', $year, $startMonth));
		// last day of endMonth
		$endDate = new DateTime(sprintf('%04d-%02d-01', $year, $endMonth));
		$endDate->modify('last day of this month');
	} else {
		$startDate = $baseStart;
		$endDate = $baseEnd;
	}
	$groupBy = 'month';
}

$startStr = $startDate->format('Y-m-d');
$endStr = $endDate->format('Y-m-d');

// Fetch aggregated quantities per date and status
$conn = connect();
$sql = "SELECT DATE(oh.Created_dt) as date, od.Status as status, SUM(od.Quantity) as qty 
		FROM orderDetails od
		JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
		WHERE DATE(oh.Created_dt) BETWEEN ? AND ?
		GROUP BY DATE(oh.Created_dt), od.Status
		ORDER BY date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startStr, $endStr);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
	$rows[] = $r;
}

// Build buckets
$labels = [];
$claimed = [];
$cancelled = [];
$returned = [];

if ($groupBy === 'day') {
	$period = new DatePeriod(new DateTime($startStr), new DateInterval('P1D'), (new DateTime($endStr))->modify('+1 day'));
	foreach ($period as $d) {
		$lbl = $d->format('Y-m-d');
		$labels[] = $lbl;
		$claimed[$lbl] = 0;
		$cancelled[$lbl] = 0;
		$returned[$lbl] = 0;
	}
	foreach ($rows as $r) {
		$date = $r['date'];
		$qty = (int)$r['qty'];
		$status = strtolower($r['status']);
		if (isset($claimed[$date])) {
			if ($status === 'sold') $claimed[$date] += $qty;
			elseif ($status === 'cancelled') $cancelled[$date] += $qty;
			elseif ($status === 'returned') $returned[$date] += $qty;
		}
	}
	$outLabels = array_values(array_map(function($d){ $dt = new DateTime($d); return $dt->format('M j'); }, $labels));
	$outClaimed = array_values($claimed);
	$outCancelled = array_values($cancelled);
	$outReturned = array_values($returned);
} elseif ($groupBy === 'week-of-month') {
	// Determine week buckets within the start/end month
	$startMonth = (int)$startDate->format('m');
	$startYear = (int)$startDate->format('Y');
	$lastDay = (int)$endDate->format('d');
	// compute weeks covering startDate..endDate in that month
	$weeks = [];
	$weekIndexStart = (int)ceil((int)$startDate->format('d') / 7);
	$weekIndexEnd = (int)ceil((int)$endDate->format('d') / 7);
	for ($w = $weekIndexStart; $w <= $weekIndexEnd; $w++) {
		$labels[] = 'Week ' . $w;
		$claimed[$w] = 0;
		$cancelled[$w] = 0;
		$returned[$w] = 0;
	}
	foreach ($rows as $r) {
		$d = new DateTime($r['date']);
		if ($d->format('Y-m') !== $startDate->format('Y-m')) continue;
		$day = (int)$d->format('d');
		$wk = (int)ceil($day / 7);
		$qty = (int)$r['qty'];
		$status = strtolower($r['status']);
		if (!isset($claimed[$wk])) continue;
		if ($status === 'sold') $claimed[$wk] += $qty;
		elseif ($status === 'cancelled') $cancelled[$wk] += $qty;
		elseif ($status === 'returned') $returned[$wk] += $qty;
	}
	$outLabels = array_values($labels);
	$outClaimed = array_values($claimed);
	$outCancelled = array_values($cancelled);
	$outReturned = array_values($returned);
} else { // month grouping
	$startMonth = new DateTime($startDate->format('Y-m-01'));
	$endMonth = new DateTime($endDate->format('Y-m-01'));
	$period = new DatePeriod($startMonth, new DateInterval('P1M'), (clone $endMonth)->modify('+1 month'));
	$months = [];
	foreach ($period as $m) {
		$key = $m->format('Y-m');
		$labels[] = $m->format('M');
		$claimed[$key] = 0;
		$cancelled[$key] = 0;
		$returned[$key] = 0;
		$months[] = $key;
	}
	foreach ($rows as $r) {
		$dateKey = (new DateTime($r['date']))->format('Y-m');
		$qty = (int)$r['qty'];
		$status = strtolower($r['status']);
		if (!isset($claimed[$dateKey])) continue;
		if ($status === 'claimed') $claimed[$dateKey] += $qty;
		elseif ($status === 'cancelled') $cancelled[$dateKey] += $qty;
		elseif ($status === 'returned') $returned[$dateKey] += $qty;
	}
	$outLabels = array_values($labels);
	$outClaimed = array_values($claimed);
	$outCancelled = array_values($cancelled);
	$outReturned = array_values($returned);
}

echo json_encode([
	'success' => true,
	'labels' => $outLabels,
	'claimed' => $outClaimed,
	'cancelled' => $outCancelled,
	'returned' => $outReturned,
	'start' => $startStr,
	'end' => $endStr,
]);

