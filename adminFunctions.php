<?php
function getCustomerCount() {
    $conn = connect();
    $count = 0;
    $query = "SELECT COUNT(*) as count FROM customer";
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
    $query = "SELECT COUNT(DISTINCT ProductID) as count FROM productMstr";
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
    $query = "SELECT COUNT(*) as count FROM Order_hdr";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    }
    mysqli_close($conn);
    return $count;
}

function getRecentActivities($limit = 5) {
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

function getLowInventoryProducts() {
    $conn = connect();
    $lowInventory = [];
    $query = "SELECT pbm.ProductBranchID, pbm.ProductID, pbm.BranchCode, pbm.Count, pm.*
              FROM productbranchmaster pbm 
              JOIN productMstr pm ON pbm.ProductID = pm.ProductID
              WHERE pbm.Count <= 10 
              ORDER BY pbm.Count ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lowInventory[] = $row;
        }
    }
    mysqli_close($conn);
    return $lowInventory;
}
?>