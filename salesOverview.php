<?php
// salesOverview.php
// Returns JSON aggregated sales (only 'Claimed' orders are treated as sold) between start and end dates.
include 'setup.php';
header('Content-Type: application/json');

try {
    $start = isset($_GET['start']) ? $_GET['start'] : null;
    $end = isset($_GET['end']) ? $_GET['end'] : null;

    // Default to last 7 days if not provided
    if (!$end) $end = date('Y-m-d');
    if (!$start) $start = date('Y-m-d', strtotime('-6 days', strtotime($end)));

    // Basic validation: YYYY-MM-DD
    $sd = DateTime::createFromFormat('Y-m-d', $start);
    $ed = DateTime::createFromFormat('Y-m-d', $end);
    if (!$sd || !$ed) throw new Exception('Invalid date format. Use YYYY-MM-DD.');

    $conn = connect();

    // Aggregate sold / cancelled / returned quantities and revenue per day
    // Join product info to compute revenue = Quantity * Price (Price stored as varchar like '₱3,500')
    $sql = "SELECT DATE(oH.Created_dt) AS saleDate,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'CLAIMED' THEN oD.Quantity ELSE 0 END),0) AS sold,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'CANCELLED' THEN oD.Quantity ELSE 0 END),0) AS cancelled,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'RETURNED' THEN oD.Quantity ELSE 0 END),0) AS returned,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'CLAIMED' THEN oD.Quantity *
                 COALESCE(CAST(REPLACE(REPLACE(p.Price, '₱', ''), ',', '') AS DECIMAL(12,2)),0) ELSE 0 END),0) AS sold_rev,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'CANCELLED' THEN oD.Quantity *
                 COALESCE(CAST(REPLACE(REPLACE(p.Price, '₱', ''), ',', '') AS DECIMAL(12,2)),0) ELSE 0 END),0) AS cancelled_rev,
             COALESCE(SUM(CASE WHEN UPPER(oD.Status) = 'RETURNED' THEN oD.Quantity *
                 COALESCE(CAST(REPLACE(REPLACE(p.Price, '₱', ''), ',', '') AS DECIMAL(12,2)),0) ELSE 0 END),0) AS returned_rev
        FROM Order_hdr oH
        JOIN orderDetails oD ON oH.Orderhdr_id = oD.OrderHdr_id
        LEFT JOIN ProductBranchMaster pb ON oD.ProductBranchID = pb.ProductBranchID
        LEFT JOIN productMstr p ON pb.ProductID = p.ProductID
        WHERE DATE(oH.Created_dt) BETWEEN ? AND ?
        GROUP BY saleDate
        ORDER BY saleDate ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('ss', $start, $end);
    $stmt->execute();
    $res = $stmt->get_result();

    $map = [];
    while ($r = $res->fetch_assoc()) {
        $map[$r['saleDate']] = [
            'sold' => (int)$r['sold'],
            'cancelled' => (int)$r['cancelled'],
            'returned' => (int)$r['returned'],
            'sold_rev' => isset($r['sold_rev']) ? (float)$r['sold_rev'] : 0.0,
            'cancelled_rev' => isset($r['cancelled_rev']) ? (float)$r['cancelled_rev'] : 0.0,
            'returned_rev' => isset($r['returned_rev']) ? (float)$r['returned_rev'] : 0.0
        ];
    }

    // Build full date range and fill zeros
    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($end))->modify('+1 day'));
    $labels = [];
    $sold = [];
    $cancelled = [];
    $returned = [];
    $sold_rev = [];
    $cancelled_rev = [];
    $returned_rev = [];
    foreach ($period as $dt) {
        $d = $dt->format('Y-m-d');
        $labels[] = $d;
        $sold[] = isset($map[$d]) ? $map[$d]['sold'] : 0;
        $cancelled[] = isset($map[$d]) ? $map[$d]['cancelled'] : 0;
        $returned[] = isset($map[$d]) ? $map[$d]['returned'] : 0;
        $sold_rev[] = isset($map[$d]) ? (float)$map[$d]['sold_rev'] : 0.0;
        $cancelled_rev[] = isset($map[$d]) ? (float)$map[$d]['cancelled_rev'] : 0.0;
        $returned_rev[] = isset($map[$d]) ? (float)$map[$d]['returned_rev'] : 0.0;
    }

    // totals
    $total_sold_rev = array_sum($sold_rev);
    $total_cancelled_rev = array_sum($cancelled_rev);
    $total_returned_rev = array_sum($returned_rev);
    // Per user request: do not treat cancelled/returned as net loss here — only show net gained from sold items.
    $net_total = $total_sold_rev;

        // Top products (by quantity) in range for sold/cancelled/returned
        $topProducts = [ 'sold' => [], 'cancelled' => [], 'returned' => [] ];

        $queries = [
                'sold' => "SELECT p.ProductID, p.Model, COALESCE(SUM(oD.Quantity),0) AS qty
                                     FROM orderDetails oD
                                     JOIN ProductBranchMaster pb ON oD.ProductBranchID = pb.ProductBranchID
                                     JOIN productMstr p ON pb.ProductID = p.ProductID
                                     JOIN Order_hdr oH ON oH.Orderhdr_id = oD.OrderHdr_id
                                     WHERE (oD.Status = 'Claimed' OR oD.ActivityCode = 1)
                                         AND DATE(oH.Created_dt) BETWEEN ? AND ?
                                     GROUP BY p.ProductID
                                     ORDER BY qty DESC
                                     LIMIT 5",
                'cancelled' => "SELECT p.ProductID, p.Model, COALESCE(SUM(oD.Quantity),0) AS qty
                                                FROM orderDetails oD
                                                JOIN ProductBranchMaster pb ON oD.ProductBranchID = pb.ProductBranchID
                                                JOIN productMstr p ON pb.ProductID = p.ProductID
                                                JOIN Order_hdr oH ON oH.Orderhdr_id = oD.OrderHdr_id
                                                WHERE UPPER(oD.Status) = 'CANCELLED'
                                                    AND DATE(oH.Created_dt) BETWEEN ? AND ?
                                                GROUP BY p.ProductID
                                                ORDER BY qty DESC
                                                LIMIT 5",
                'returned' => "SELECT p.ProductID, p.Model, COALESCE(SUM(oD.Quantity),0) AS qty
                                                FROM orderDetails oD
                                                JOIN ProductBranchMaster pb ON oD.ProductBranchID = pb.ProductBranchID
                                                JOIN productMstr p ON pb.ProductID = p.ProductID
                                                JOIN Order_hdr oH ON oH.Orderhdr_id = oD.OrderHdr_id
                                                WHERE UPPER(oD.Status) = 'RETURNED'
                                                    AND DATE(oH.Created_dt) BETWEEN ? AND ?
                                                GROUP BY p.ProductID
                                                ORDER BY qty DESC
                                                LIMIT 5"
        ];

        foreach ($queries as $k => $q) {
                $st = $conn->prepare($q);
                if ($st) {
                        $st->bind_param('ss', $start, $end);
                        $st->execute();
                        $r = $st->get_result();
                        while ($row = $r->fetch_assoc()) {
                                $topProducts[$k][] = ['ProductID' => $row['ProductID'], 'Model' => $row['Model'], 'qty' => (int)$row['qty']];
                        }
                        $st->close();
                }
        }

        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'sold' => $sold,
            'cancelled' => $cancelled,
            'returned' => $returned,
            'sold_rev' => $sold_rev,
            'cancelled_rev' => $cancelled_rev,
            'returned_rev' => $returned_rev,
            'total_sold_rev' => $total_sold_rev,
            'total_cancelled_rev' => $total_cancelled_rev,
            'total_returned_rev' => $total_returned_rev,
            // net_gained is provided for clarity — equals total sold revenue only (no subtraction of cancelled/returned)
            'net_gained' => $total_sold_rev,
            'net_total' => $net_total,
            'start' => $start,
            'end' => $end,
            'topProducts' => $topProducts
        ]);
    $stmt->close();
    $conn->close();

} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $ex->getMessage()]);
}

?>
