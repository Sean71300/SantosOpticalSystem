<?php
include_once 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
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
?>

<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="customCodes/custom.css">
        <title>Admin | Inventories</title>
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
    </head>

    <header class="mb-5">
        <?php
            include "Navigation.php";
        ?>
    </header>

    <body>
        <div class="container">
            <div class="container mb-4">
                <a class="col-2 mt-2 btn btn-primary" href="customerRecords.php" role="button">Customer Information</a>                
                <a class="col-2 mt-2 btn btn-primary" href="EmployeeRecords.php" role="button">Manage Employees</a>
                <a class="col-2 mt-2 btn btn-primary" href="admin-inventory.php" role="button">Manage Inventories</a>
            </div>
            
            <div class="container" style="margin-bottom: 3.5rem;">
                <form class="d-flex justify-content-evenly" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                    <div class="form-floating w-100 me-3">
                        <select name="chooseBranch" id="chooseBranch" class="form-select form-select-lg">
                            <option value="" disabled selected> </option>
                            <?php
                                $sql = "SELECT BranchName from branchmaster";
                                $result = mysqli_query($link, $sql);
                                while($row = mysqli_fetch_array($result)){
                                    echo "<option class='form-select-sm' value='".$row['BranchName']."'>".$row['BranchName']."</option>";
                                }
                            ?>
                        </select>   
                        <label for="chooseBranch">Choose Branch:</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-25">Submit</button>               
                </form>
            </div>          

            <?php                            
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (empty($_POST['chooseBranch'])) { //error handler
                        echo "<div class='alert alert-danger mt-3'>Please select a branch.</div>";
                        exit;
                    } 
                    else {
                        $branchName = $_POST['chooseBranch'];
                        $sql = "SELECT bm.BranchCode, pbm.productID, pm.* 
                                FROM branchmaster bm
                                JOIN productbranchmaster pbm ON bm.BranchCode = pbm.BranchCode
                                JOIN productmstr pm ON pbm.productID = pm.productID 
                                WHERE bm.BranchName = ?"; //bm is branchmaster, pbm is productbranchmaster, pm is productmstr
                        
                        $stmt = mysqli_prepare($link, $sql);
                        mysqli_stmt_bind_param($stmt, "s", $branchName);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        // display table
                        echo "<table class='table mt-3' border='1'>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Category Type</th>
                                    <th>Shape ID</th>
                                    <th>Brand ID</th>
                                    <th>Model</th>
                                    <th>Remarks</th>
                                    <th>Product Image</th>
                                    <th>Availability</th>
                                    <th>Updated by</th>
                                    <th>Updated Date</th>
                                </tr>";

                        // fetch and display results
                        while ($row = mysqli_fetch_assoc($result)) {
                            $imageData = base64_encode($row['ProductImage']);
                            $src = 'data:image/jpeg;base64,' . $imageData;

                            echo "<tr>
                                    <td>" . htmlspecialchars($row['ProductID']) . "</td>
                                    <td>" . htmlspecialchars($row['CategoryType']) . "</td>
                                    <td>" . htmlspecialchars($row['ShapeID']) . "</td>
                                    <td>" . htmlspecialchars($row['BrandID']) . "</td>
                                    <td>" . htmlspecialchars($row['Model']) . "</td>
                                    <td>" . htmlspecialchars($row['Remarks']) . "</td>
                                    <td><img src='" . $src . "' alt='Product Image' style='width:100px; height:auto;'/></td>
                                    <td>" . htmlspecialchars($row['Avail_FL']) . "</td>
                                    <td>" . htmlspecialchars($row['Upd_by']) . "</td>
                                    <td>" . htmlspecialchars($row['Upd_dt']) . "</td>
                                </tr>";
                        }

                        echo "</table>";

                        // Close the statement
                        mysqli_stmt_close($stmt);
                    }
                }
            ?>
        </div>
    </body>
</html>