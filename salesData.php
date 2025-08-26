<?php
// Ensure we only return JSON to the client
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', '0');
@error_reporting(0);
// buffer any unexpected output from includes or warnings
ob_start();
// Minimal, robust sales data endpoint.
// Accepts GET params: start=YYYY-MM-DD, end=YYYY-MM-DD
// Returns JSON: { success:true, labels:[], claimed:[], cancelled:[], returned:[], start:'', end:'' }

try {
	// setup.php defines connect() and other helpers used across the app
	require_once __DIR__ . '/setup.php';
	$extraOutput = trim(ob_get_clean());
	if (!empty($extraOutput)) {
		@file_put_contents(__DIR__ . '/logs/salesData_errors.log', date('c') . " - Unexpected output during include: \n" . $extraOutput . "\n\n", FILE_APPEND);
	}

	// input validation
	$start = isset($_GET['start']) ? $_GET['start'] : null;
	$end = isset($_GET['end']) ? $_GET['end'] : null;

	if (!$start || !$end) {
		throw new Exception('Missing required start or end parameters. Use ?start=YYYY-MM-DD&end=YYYY-MM-DD');
	}

	$startDate = DateTime::createFromFormat('Y-m-d', $start);
	$endDate = DateTime::createFromFormat('Y-m-d', $end);
	if (!$startDate || !$endDate) throw new Exception('Invalid date format. Use YYYY-MM-DD');
	if ($startDate > $endDate) {
		// swap
		$tmp = $startDate; $startDate = $endDate; $endDate = $tmp;
	}

	// sanitize range size
	$diffDays = (int)$startDate->diff($endDate)->days + 1;

	// choose aggregation
	if (isset($_GET['mode']) && $_GET['mode'] === 'month_weeks') {
		$agg = 'month_weeks';
	} else {
		if ($diffDays > 365) $agg = 'month';
		elseif ($diffDays > 90) $agg = 'week';
		else $agg = 'day';
	}

	$startStr = $startDate->format('Y-m-d');
	$endStr = $endDate->format('Y-m-d');

	// query DB: grouped by date and status
	$conn = connect();
	$sql = "SELECT DATE(oh.Created_dt) AS date, od.Status AS status, SUM(od.Quantity) AS qty
			FROM orderDetails od
			JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
			WHERE DATE(oh.Created_dt) BETWEEN ? AND ?
			GROUP BY DATE(oh.Created_dt), od.Status
			ORDER BY date ASC";
	$stmt = $conn->prepare($sql);
	if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
	$stmt->bind_param('ss', $startStr, $endStr);
	$stmt->execute();
	$res = $stmt->get_result();

	$rows = [];
	while ($r = $res->fetch_assoc()) $rows[] = $r;

	// build buckets
	$labels = [];
	$claimed = [];
	$cancelled = [];
	$returned = [];

	if ($agg === 'day') {
		$period = new DatePeriod(new DateTime($startStr), new DateInterval('P1D'), (new DateTime($endStr))->modify('+1 day'));
		foreach ($period as $d) {
			$key = $d->format('Y-m-d');
			$labels[] = $d->format('M j');
			$claimed[$key] = 0; $cancelled[$key] = 0; $returned[$key] = 0;
		}
		foreach ($rows as $r) {
			$date = $r['date']; $qty = (int)$r['qty']; $status = strtolower($r['status']);
			if (!isset($claimed[$date])) continue;
			if (in_array($status, ['sold','claimed','completed'])) $claimed[$date] += $qty;
			elseif ($status === 'cancelled') $cancelled[$date] += $qty;
			elseif ($status === 'returned') $returned[$date] += $qty;
		}
		$outLabels = array_values($labels);
		$outClaimed = array_values($claimed);
		$outCancelled = array_values($cancelled);
		$outReturned = array_values($returned);

	} elseif ($agg === 'week') {
		// week buckets starting Monday
		$sd = clone $startDate; $sd->modify('monday this week');
		$period = new DatePeriod($sd, new DateInterval('P7D'), (new DateTime($endStr))->modify('+1 day'));
		$weekKeys = [];
		foreach ($period as $w) {
			$key = $w->format('Y-m-d'); $weekKeys[] = $key; $labels[] = $w->format('M j');
			$claimed[$key]=0; $cancelled[$key]=0; $returned[$key]=0;
		}
		foreach ($rows as $r) {
			$d = new DateTime($r['date']); $wk = clone $d; $wk->modify('monday this week'); $key = $wk->format('Y-m-d');
			if (!isset($claimed[$key])) continue;
			$qty = (int)$r['qty']; $status = strtolower($r['status']);
			if (in_array($status, ['sold','claimed','completed'])) $claimed[$key] += $qty;
			elseif ($status === 'cancelled') $cancelled[$key] += $qty;
			elseif ($status === 'returned') $returned[$key] += $qty;
		}
		$outLabels = array_values($labels);
		$outClaimed = array_values($claimed);
		$outCancelled = array_values($cancelled);
		$outReturned = array_values($returned);
    
	} elseif ($agg === 'month_weeks') {
		// split the month into Week 1..4 and Whole Month
		// use startDate's month
		$monthStart = new DateTime($startDate->format('Y-m-01'));
		$monthEnd = new DateTime($startDate->format('Y-m-t'));
		$daysInMonth = (int)$monthEnd->format('d');

		// define week ranges (1-based): week1 = days 1-7, week2 = 8-14, week3 = 15-21, week4 = 22-end
		$buckets = [
			['label' => 'Week 1', 'start' => (clone $monthStart)->format('Y-m-d'), 'end' => (clone $monthStart)->modify('+6 days')->format('Y-m-d')],
			['label' => 'Week 2', 'start' => (clone $monthStart)->modify('+7 days')->format('Y-m-d'), 'end' => (clone $monthStart)->modify('+13 days')->format('Y-m-d')],
			['label' => 'Week 3', 'start' => (clone $monthStart)->modify('+14 days')->format('Y-m-d'), 'end' => (clone $monthStart)->modify('+20 days')->format('Y-m-d')],
			['label' => 'Week 4', 'start' => (clone $monthStart)->modify('+21 days')->format('Y-m-d'), 'end' => $monthEnd->format('Y-m-d')],
			['label' => 'Whole Month', 'start' => $monthStart->format('Y-m-d'), 'end' => $monthEnd->format('Y-m-d')]
		];

		// init counters
		foreach ($buckets as $b) {
			$labels[] = $b['label'];
			$claimed[$b['label']] = 0;
			$cancelled[$b['label']] = 0;
			$returned[$b['label']] = 0;
		}

		foreach ($rows as $r) {
			$d = $r['date'];
			$qty = (int)$r['qty'];
			$status = strtolower($r['status']);
			foreach ($buckets as $b) {
				if ($d >= $b['start'] && $d <= $b['end']) {
					if (in_array($status, ['sold','claimed','completed'])) $claimed[$b['label']] += $qty;
					elseif ($status === 'cancelled') $cancelled[$b['label']] += $qty;
					elseif ($status === 'returned') $returned[$b['label']] += $qty;
				}
			}
			// also add to Whole Month bucket
			// (already covered by loop)
		}

		$outLabels = array_values($labels);
		$outClaimed = array_values($claimed);
		$outCancelled = array_values($cancelled);
		$outReturned = array_values($returned);

	} else { // month
		$mStart = new DateTime($startDate->format('Y-m-01'));
		$mEnd = new DateTime($endDate->format('Y-m-01'));
		$period = new DatePeriod($mStart, new DateInterval('P1M'), (clone $mEnd)->modify('+1 month'));
		foreach ($period as $m) {
			$key = $m->format('Y-m'); $labels[] = $m->format('M Y');
			$claimed[$key]=0; $cancelled[$key]=0; $returned[$key]=0;
		}
		foreach ($rows as $r) {
			$dKey = (new DateTime($r['date']))->format('Y-m');
			if (!isset($claimed[$dKey])) continue;
			$qty = (int)$r['qty']; $status = strtolower($r['status']);
			if (in_array($status, ['sold','claimed','completed'])) $claimed[$dKey] += $qty;
			elseif ($status === 'cancelled') $cancelled[$dKey] += $qty;
			elseif ($status === 'returned') $returned[$dKey] += $qty;
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
		'aggregation' => $agg
	]);

} catch (Exception $ex) {
	// write to server log for diagnosis
	@file_put_contents(__DIR__ . '/logs/salesData_errors.log', date('c') . " - " . $ex->getMessage() . "\n" . $ex->getTraceAsString() . "\n\n", FILE_APPEND);
	http_response_code(500);
	echo json_encode(['success'=>false,'error'=>$ex->getMessage()]);
}


