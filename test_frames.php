<?php
// Create this as a separate test file: test_frames.php
// This will help us diagnose the issue

require_once 'connect.php';

echo "<h2>Testing Frame Recommendation System</h2>";
echo "<hr>";

// Test 1: Check database connection
echo "<h3>Test 1: Database Connection</h3>";
$conn = connect();
if ($conn) {
    echo "✅ Database connected successfully<br>";
} else {
    echo "❌ Database connection failed<br>";
    die();
}

// Test 2: Check if tables exist
echo "<h3>Test 2: Check Tables</h3>";
$tables = ['productMstr', 'ProductBranchMaster', 'BranchMaster', 'archives', 'shapeMaster'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' NOT found<br>";
    }
}

// Test 3: Check shapeMaster data
echo "<h3>Test 3: Shape Master Data</h3>";
$result = $conn->query("SELECT * FROM shapeMaster");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ShapeID</th><th>Description</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['ShapeID']}</td><td>{$row['Description']}</td></tr>";
}
echo "</table>";

// Test 4: Check products by shape
echo "<h3>Test 4: Products by Shape</h3>";
$shapes = [1, 2, 3, 4, 5, 6, 7];
foreach ($shapes as $shapeID) {
    $sql = "SELECT COUNT(*) as count FROM productMstr WHERE ShapeID = $shapeID";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "ShapeID $shapeID: {$row['count']} products<br>";
}

// Test 5: Run the actual query for ShapeID 5 (Square)
echo "<h3>Test 5: Full Query Test (ShapeID 5 - Square)</h3>";
$shapeID = 5;
$limit = 3;

$sql = "SELECT DISTINCT p.*, 
        (SELECT GROUP_CONCAT(DISTINCT b.BranchName SEPARATOR ', ') 
         FROM ProductBranchMaster pb 
         JOIN BranchMaster b ON pb.BranchCode = b.BranchCode 
         WHERE pb.ProductID = p.ProductID 
         AND (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)
         AND pb.Stocks > 0) as AvailableBranches,
        (SELECT SUM(pb.Stocks) 
         FROM ProductBranchMaster pb 
         WHERE pb.ProductID = p.ProductID 
         AND (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)) as TotalStocks,
        br.BrandName
        FROM productMstr p
        LEFT JOIN brandMaster br ON p.BrandID = br.BrandID
        LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product')
        WHERE p.ShapeID = $shapeID
        AND (p.Avail_FL = 'Available' OR p.Avail_FL IS NULL)
        AND a.ArchiveID IS NULL
        AND p.CategoryType IN ('Frame', 'Sunglasses')
        HAVING TotalStocks > 0
        ORDER BY TotalStocks DESC, p.Model ASC
        LIMIT $limit";

echo "<strong>SQL Query:</strong><br>";
echo "<pre>$sql</pre>";

$result = $conn->query($sql);

if ($result) {
    echo "<br><strong>Query executed successfully!</strong><br>";
    echo "Number of rows returned: " . $result->num_rows . "<br><br>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ProductID</th><th>Model</th><th>Category</th><th>Material</th><th>Price</th><th>Image</th><th>Branches</th><th>Stock</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['ProductID']}</td>";
            echo "<td>{$row['Model']}</td>";
            echo "<td>{$row['CategoryType']}</td>";
            echo "<td>{$row['Material']}</td>";
            echo "<td>{$row['Price']}</td>";
            echo "<td><img src='{$row['ProductImage']}' width='50' alt='Product'></td>";
            echo "<td>{$row['AvailableBranches']}</td>";
            echo "<td>{$row['TotalStocks']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ Query returned 0 results<br>";
        
        // Let's check why
        echo "<h4>Debugging why no results:</h4>";
        
        // Check products with ShapeID 5
        $debug1 = $conn->query("SELECT COUNT(*) as c FROM productMstr WHERE ShapeID = 5");
        $r1 = $debug1->fetch_assoc();
        echo "Products with ShapeID 5: {$r1['c']}<br>";
        
        // Check available products
        $debug2 = $conn->query("SELECT COUNT(*) as c FROM productMstr WHERE ShapeID = 5 AND (Avail_FL = 'Available' OR Avail_FL IS NULL)");
        $r2 = $debug2->fetch_assoc();
        echo "Available products with ShapeID 5: {$r2['c']}<br>";
        
        // Check archived products
        $debug3 = $conn->query("SELECT COUNT(*) as c FROM productMstr p LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product') WHERE p.ShapeID = 5 AND a.ArchiveID IS NULL");
        $r3 = $debug3->fetch_assoc();
        echo "Non-archived products with ShapeID 5: {$r3['c']}<br>";
        
        // Check category
        $debug4 = $conn->query("SELECT COUNT(*) as c FROM productMstr WHERE ShapeID = 5 AND CategoryType IN ('Frame', 'Sunglasses')");
        $r4 = $debug4->fetch_assoc();
        echo "Frame/Sunglasses products with ShapeID 5: {$r4['c']}<br>";
    }
} else {
    echo "❌ Query failed: " . $conn->error . "<br>";
}

// Test 6: Test each shape
echo "<h3>Test 6: Quick Test All Shapes</h3>";
$shapes = [
    1 => 'OBLONG (Oval)',
    2 => 'V-TRIANGLE', 
    3 => 'DIAMOND',
    4 => 'ROUND',
    5 => 'SQUARE',
    6 => 'A-TRIANGLE',
    7 => 'RECTANGLE'
];

foreach ($shapes as $sid => $name) {
    $testSql = "SELECT COUNT(*) as c FROM productMstr p
                LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product')
                WHERE p.ShapeID = $sid
                AND (p.Avail_FL = 'Available' OR p.Avail_FL IS NULL)
                AND a.ArchiveID IS NULL
                AND p.CategoryType IN ('Frame', 'Sunglasses')";
    
    $testResult = $conn->query($testSql);
    $testRow = $testResult->fetch_assoc();
    
    echo "<strong>$name (ID: $sid):</strong> {$testRow['c']} available products<br>";
}

$conn->close();
?>