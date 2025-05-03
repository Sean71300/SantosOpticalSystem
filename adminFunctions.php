<?php
function getCustomerCount() {
    $conn = connect();
    $count = 0;
    $query = "SELECT COUNT(*) as count FROM customer WHERE Status = 'Active'";        
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    }
    mysqli_close($conn);
    return $count;
}

function getEmployeeCount() {
    $conn = connect();
    $count = 0;
    $query = "SELECT COUNT(*) as count FROM employee WHERE Status = 'Active'";     
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    }
    mysqli_close($conn);
    return $count;
}

function getInventoryCount() {
    $conn = connect();
    $count = 0;
    $query = "SELECT COUNT(*) as count FROM productMstr WHERE Avail_FL = 'Available'";   
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    }
    mysqli_close($conn);
    return $count;
}

function getOrderCount() {
    $conn = connect();
    $count = 0;
    $query = "SELECT COUNT(*) as count 
          FROM Order_hdr o
          WHERE NOT EXISTS (
              SELECT 1 
              FROM archives a 
              WHERE a.TargetID = o.Orderhdr_id AND a.TargetType = 'order'
          )";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    }
    mysqli_close($conn);
    return $count;
}

function getRecentActivities($limit = 20) {
    $conn = connect();
    $activities = [];
    $query = "SELECT * FROM Logs 
              JOIN activityMaster ON Logs.ActivityCode = activityMaster.ActivityCode
              ORDER BY Upd_dt DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
    }
    mysqli_close($conn);
    return $activities;
}

function getClaimedOrderCount() {
    global $conn;
    $query = "SELECT SUM(od.Quantity) as claimed_count 
              FROM orderDetails od
              JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
              WHERE od.Status = 'Claimed'";
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['claimed_count'] : 0;
}

function getLowInventoryProducts() {
    global $conn;
    $query = "SELECT p.ProductID, p.Model, p.ProductImage, pb.Stocks
              FROM productMstr p
              JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID
              WHERE pb.Stocks < 10 AND p.Avail_FL = 'Available'
              ORDER BY pb.Stocks ASC LIMIT 5";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getSalesOverviewData($days = 7) {
    global $conn;
    
    // Query to get claimed orders (where Status = 'Complete')
    $query = "SELECT 
                DATE(oh.Created_dt) as date, 
                SUM(od.Quantity) as total_sold 
              FROM orderDetails od
              JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
              WHERE oh.Created_dt >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND od.Status = 'Claimed'
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
?>