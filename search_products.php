<?php
require_once 'connect.php';

$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';
$searchTerm = mysqli_real_escape_string($link, $searchTerm);

$sql = "SELECT Model FROM productMstr WHERE Model LIKE '%$searchTerm%' LIMIT 5";
$result = mysqli_query($link, $sql);

$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row['Model'];
}

echo json_encode($results);
?>
