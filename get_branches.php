<?php
require_once 'connect.php';

header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
    $productID = (int)$_GET['product_id'];
    
    $sql = "SELECT b.BranchName, pb.Stocks 
            FROM ProductBranchMaster pb
            JOIN BranchMaster b ON pb.BranchCode = b.BranchCode
            WHERE pb.ProductID = ? AND pb.Avail_FL = 'Available' AND pb.Stocks > 0
            ORDER BY b.BranchName";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $branches = [];
        while ($row = $result->fetch_assoc()) {
            $branches[] = $row;
        }
        
        echo json_encode($branches);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode(['error' => 'No product ID provided']);
}

$conn->close();
?>
