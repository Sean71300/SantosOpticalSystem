<?php
require_once 'connect.php';

if (isset($_GET['product_id'])) {
    $productID = (int)$_GET['product_id'];
    
    $sql = "SELECT b.BranchName, pb.Stocks 
            FROM ProductBranchMaster pb
            JOIN BranchMaster b ON pb.BranchCode = b.BranchCode
            WHERE pb.ProductID = ? AND pb.Avail_FL = 'Available' AND pb.Stocks > 0
            ORDER BY b.BranchName";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($branches);
    
    $stmt->close();
    $conn->close();
}
?>
