<?php
require_once 'connect.php';

$conn = connect();

// Get search term and sort value
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the SQL query
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
    default:
        $sql .= " ORDER BY Model ASC";
}

// Limit results for preview
$sql .= " LIMIT 8";

$result = mysqli_query($conn, $sql);

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
?>
