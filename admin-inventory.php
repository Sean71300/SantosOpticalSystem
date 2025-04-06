<?php
include 'setup.php'; // Include the setup.php file
require 'connect.php'; //Connect to the database
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ //Check if the user is logged in
    // If not logged in, redirect to login page
    echo '<html>';
    echo '<head>';
    echo '<title>INVALID ACCESS</title>';
    echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
    echo '</head>';
    echo '<body>';
    echo '<div class="h-100 container d-flex flex-column justify-content-center align-items-center">';
    echo '<div class="mt-4 alert alert-danger"> Please login to continue. </div>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    header("Refresh: 2; url=login.php");
    exit;
}
function getBranches($link) { // Function to get branches from the database
    $sql = "SELECT BranchName from branchmaster";
    $result = mysqli_query($link, $sql);
    while($row = mysqli_fetch_array($result)){
        echo "<option class='form-select-sm' value='".$row['BranchName']."'>".$row['BranchName']."</option>";
    }
}

function getInventory($link, $branchName) { // Function to get inventory based on the selected branch
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
?>

<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="customCodes/custom.css">
        <title>Admin | Inventories</title>
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>

        <style>
            .table-fixed {
                width: 100%;
                vertical-align: middle;
            }
        </style>
    </head>

    <header class="mb-5">
        <?php
            include "Navigation.php";
        ?>
    </header>

    <body>
        <div class="container">
            <div class="container mb-5">
                <?php
                    $username = $_SESSION["username"];
                    echo "<h1 class='mb-5' style='text-align: center;'>Welcome $username</h1>";
                ?>
                <h2 style='text-align: center;'>Admin Dashboard</h2>
                <hr>
                <div class="d-flex justify-content-evenly">
                    <a class="col-2 mt-2 btn btn-primary" href="customerRecords.php" role="button">Customer Information</a>                
                    <a class="col-2 mt-2 btn btn-primary" href="EmployeeRecords.php" role="button">Manage Employees</a>
                    <a class="col-2 mt-2 btn btn-primary" href="admin-inventory.php" role="button">Manage Inventories</a>
                </div>
            </div>
            
            <div class="container" style="margin-bottom: 3.5rem;">
                <form class="d-flex justify-content-evenly" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-floating w-100 me-3">
                        <select name="chooseBranch" id="chooseBranch" class="form-select form-select-lg">
                            <option value="" disabled selected> </option>
                            <?php
                                getBranches($link); // Call the function to get branches
                            ?>
                        </select>   
                        <label for="chooseBranch">Choose Branch:</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:20%">Search</button>
                    <button type="button" class="btn bg-success" style="width:20%; margin-left: 1rem"><a href='#' class="text-light text-decoration-none">Add</a></button>
                    
                </form>
            </div>          

            <?php                            
                if ($_SERVER["REQUEST_METHOD"] == "POST") { // Submit then display inventory
                    if (empty($_POST['chooseBranch'])) { //error handler
                        echo "<div class='alert alert-danger mt-3'>Please select a branch.</div>";
                        exit;
                    } 
                    else {
                        getInventory($link, $_POST['chooseBranch']); // Call the function to get inventory
                    }
                }
            ?>
        </div>
    </body>
</html>