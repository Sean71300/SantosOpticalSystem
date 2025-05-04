<?php
require_once 'connect.php';

$term = isset($_GET['term']) ? $_GET['term'] : '';

$conn = connect();
$sql = "SELECT DISTINCT Model 
        FROM productMstr 
        WHERE Model LIKE ? 
        AND (Avail_FL = 'Available' OR Avail_FL IS NULL)
        LIMIT 5";
$stmt = $conn->prepare($sql);
$searchTerm = $term . '%'; // Your "starts with" behavior
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row['Model'];
}

header('Content-Type: application/json');
echo json_encode($products);
?>
