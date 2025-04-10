<?php
include 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database


function getBranches() { // Function to get branches from the database
    $link = connect();
    $sql = "SELECT BranchName from branchmaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BranchName']."'>".$row['BranchName']."</option>";
    }
}

function getInventory($branchName) { // Function to get inventory based on the selected branch
    $link = connect();
    $branchName = $_POST['chooseBranch'];
        $sql = "SELECT bm.BranchCode, pbm.*, pm.* 
                FROM branchmaster bm
                JOIN productbranchmaster pbm ON bm.BranchCode = pbm.BranchCode
                JOIN productmstr pm ON pbm.productID = pm.productID 
                WHERE bm.BranchName = ?"; //bm is branchmaster, pbm is productbranchmaster, pm is productmstr
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $branchName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // display table
        echo "<table class='table table-bordered table-fixed mt-3' border='1'>
                <tr class = 'text-center'>
                    <th>Product ID</th>
                    <th>Category Type</th>
                    <th>Shape ID</th>
                    <th>Brand ID</th>
                    <th>Model</th>
                    <th>Remarks</th>
                    <th>Product Image</th>
                    <th>Count</th>
                    <th>Updated by</th>
                    <th>Updated Date</th>
                </tr>
                
                <tbody class='table-group-divider'>";

        // fetch and display results
        while ($row = mysqli_fetch_assoc($result)) {
            echo 
                "<tr class='text-center'>
                    <td>" . htmlspecialchars($row['ProductID']) . "</td>
                    <td>" . htmlspecialchars($row['CategoryType']) . "</td>
                    <td>" . htmlspecialchars($row['ShapeID']) . "</td>
                    <td>" . htmlspecialchars($row['BrandID']) . "</td>
                    <td>" . htmlspecialchars($row['Model']) . "</td>
                    <td>" . htmlspecialchars($row['Remarks']) . "</td>
                    <td><img src='" . htmlspecialchars($row['ProductImage']) . "' alt='Product Image' style='width:100px; height:auto;'/></td>
                    <td>" . htmlspecialchars($row['Count']) . "</td>
                    <td>" . htmlspecialchars($row['Upd_by']) . "</td>
                    <td>" . htmlspecialchars($row['Upd_dt']) . "</td>
                </tr>";
        }

        echo " </tbody>
            </table>";

        // Close the statement
        mysqli_stmt_close($stmt);
}

function addProduct(){
    $link = connect();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        echo "<div class='alert alert-success'>Product added successfully!</div>";
    }
}

?>