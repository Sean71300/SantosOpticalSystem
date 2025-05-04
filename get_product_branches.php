<?php
require_once 'connect.php';

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$conn = connect();
$sql = "SELECT b.BranchName 
        FROM ProductBranchMaster pb
        JOIN BranchMaster b ON pb.BranchCode = b.BranchCode
        WHERE pb.ProductID = ? AND pb.Avail_FL = 'Available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row['BranchName'];
}

header('Content-Type: application/json');
echo json_encode($branches);
?>
