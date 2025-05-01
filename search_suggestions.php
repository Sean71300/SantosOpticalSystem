<?php
require_once 'connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$results = [];

if (!empty($search)) {
    $conn = connect();
    $searchTerm = mysqli_real_escape_string($conn, $search);
    
    $sql = "SELECT Model, CategoryType FROM productMstr 
            WHERE Model LIKE '%$searchTerm%' OR CategoryType LIKE '%$searchTerm%'
            LIMIT 10";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($results);
?>
