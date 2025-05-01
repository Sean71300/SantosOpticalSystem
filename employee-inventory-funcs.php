<?php
include 'setup.php';

function getInventory($sort = 'ProductID', $order = 'ASC') {
    $link = connect();
    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get the employee's branch code from session
    $employeeID = $_SESSION['id'] ?? '';
    $branchCode = '';
    
    // First, get the employee's branch code
    $sql = "SELECT BranchCode FROM employee WHERE EmployeeID = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $branchCode = $row['BranchCode'];
    } else {
        die("Employee branch not found");
    }
    mysqli_stmt_close($stmt);

    // Define valid sort columns to prevent SQL injection
    $validSortColumns = ['ProductID', 'CategoryType', 'ShapeDescription', 'BrandName', 'Model', 'Material', 'Price', 'Stocks'];
    $sort = in_array($sort, $validSortColumns) ? $sort : 'ProductID';
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

    // Query to get inventory for the employee's branch
    $sql = "SELECT 
            pm.ProductID, 
            pm.CategoryType, 
            sm.Description AS ShapeDescription,
            bm.BrandName,
            pm.Model, 
            pm.Material,
            pm.Price, 
            pm.ProductImage,
            pbm.Stocks
        FROM productMstr pm
        JOIN shapeMaster sm ON pm.ShapeID = sm.ShapeID
        JOIN brandMaster bm ON pm.BrandID = bm.BrandID
        JOIN ProductBranchMaster pbm ON pm.ProductID = pbm.ProductID
        WHERE pbm.BranchCode = ?";
    
    // Add sorting
    switch($sort) {
        case 'ProductID': $sql .= " ORDER BY pm.ProductID"; break;
        case 'CategoryType': $sql .= " ORDER BY pm.CategoryType"; break;
        case 'ShapeDescription': $sql .= " ORDER BY sm.Description"; break;
        case 'BrandName': $sql .= " ORDER BY bm.BrandName"; break;
        case 'Model': $sql .= " ORDER BY pm.Model"; break;
        case 'Material': $sql .= " ORDER BY pm.Material"; break;
        case 'Price': $sql .= " ORDER BY pm.Price"; break;
        case 'Stocks': $sql .= " ORDER BY pbm.Stocks"; break;
        default: $sql .= " ORDER BY pm.ProductID";
    }
    
    $sql .= " $order";
    
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $branchCode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($link));
    }
    
    // Display the inventory table rows
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td class='align-middle'>".htmlspecialchars($row['ProductID'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['CategoryType'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['ShapeDescription'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['BrandName'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['Model'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['Material'])."</td>
                <td class='align-middle'>".htmlspecialchars($row['Price'])."</td>
                <td class='align-middle'><img src='".htmlspecialchars($row['ProductImage'])."' class='product-img'></td>
                <td class='align-middle'>".htmlspecialchars($row['Stocks'])."</td>
            </tr>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
}
?>