<?php
require_once 'connect.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (strlen($search) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $conn = connect();
    $searchTerm = mysqli_real_escape_string($conn, $search);
    
    $sql = "SELECT Model, CategoryType 
            FROM productMstr 
            WHERE Model LIKE '%$searchTerm%' OR CategoryType LIKE '%$searchTerm%'
            LIMIT 8";
    
    $result = mysqli_query($conn, $sql);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    echo json_encode($products);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
