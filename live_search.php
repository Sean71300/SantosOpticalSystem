<?php
// Add error reporting at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required parameters exist
if (!isset($_GET['search']) || !isset($_GET['sort'])) {
    die(json_encode(['error' => 'Missing parameters']));
}

require_once 'connect.php';

$conn = connect();

// Check connection
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get parameters
$search = mysqli_real_escape_string($conn, $_GET['search']);
$sort = mysqli_real_escape_string($conn, $_GET['sort']);

// Validate sort parameter
$allowed_sorts = ['price_asc', 'price_desc', 'name_asc', 'name_desc'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'name_asc';
}

// Build query
$sql = "SELECT * FROM `productMstr` WHERE 
        Model LIKE '%$search%' OR 
        CategoryType LIKE '%$search%' OR 
        Material LIKE '%$search%'";

// Add sorting
switch($sort) {
    case 'price_asc':
        $sql .= " ORDER BY CAST(REPLACE(REPLACE(Price, '₱', ''), ',', '') AS DECIMAL(10,2)) ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY CAST(REPLACE(REPLACE(Price, '₱', ''), ',', '') AS DECIMAL(10,2)) DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY Model ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY Model DESC";
        break;
}

// Limit results
$sql .= " LIMIT 8";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]));
}

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo '<div class="search-result-item d-flex align-items-center" data-id="'.$row['ProductID'].'">';
        echo '<img src="'.$row['ProductImage'].'" alt="'.$row['Model'].'" class="img-thumbnail">';
        echo '<div>';
        echo '<div class="search-result-text fw-bold">'.$row['Model'].'</div>';
        echo '<div class="search-result-text small text-muted">'.$row['CategoryType'].' • '.$row['Material'].'</div>';
        
        // Format price
        $price = $row['Price'];
        $numeric_price = preg_replace('/[^0-9.]/', '', $price);
        $formatted_price = is_numeric($numeric_price) ? '₱' . number_format((float)$numeric_price, 2) : '₱0.00';
        
        echo '<div class="search-result-text text-primary">'.$formatted_price.'</div>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="p-3 text-center">No products found</div>';
}

mysqli_close($conn);
?>
