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
   
