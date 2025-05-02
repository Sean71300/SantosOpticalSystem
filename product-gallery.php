<?php
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_GET['product'])) {
    echo json_encode(['success' => false, 'message' => 'Product name not provided']);
    exit;
}

$productName = mysqli_real_escape_string($link, $_GET['product']);

// Get product details
$productQuery = "SELECT p.*, s.Description as ShapeDescription 
                 FROM productMstr p
                 LEFT JOIN shapeMaster s ON p.ShapeID = s.ShapeID
                 WHERE p.Model = '$productName'";
$productResult = mysqli_query($link, $productQuery);

if (!$productResult || mysqli_num_rows($productResult) === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = mysqli_fetch_assoc($productResult);

// Get stock information
$stockQuery = "SELECT SUM(Stocks) as TotalStock FROM ProductBranchMaster 
               WHERE ProductID = {$product['ProductID']}";
$stockResult = mysqli_query($link, $stockQuery);
$stockData = mysqli_fetch_assoc($stockResult);

// Prepare response
$response = [
    'success' => true,
    'product' => $product,
    'stock' => $stockData['TotalStock'] ?? 0,
    'shape' => $product['ShapeDescription'] ?? 'N/A'
];

echo json_encode($response);
?>
