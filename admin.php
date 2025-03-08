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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <title>Admin | Dashboard</title>

    </head>

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            margin-bottom: 2.5rem;
        }

        /* CONTAINERS */
        .container {
            padding: 10rem;
        }
    </style>

    <body>
        <div class="container">
            <?php
            $username = $_SESSION["username"];
            echo "<h1 style='text-align: center;'>Welcome $username</h1>";
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <label for = "chooseBranch">Choose Branch:</label>
                <select name="chooseBranch" id="chooseBranch">
                    <?php
                        $sql = "SELECT BranchName from branchmaster";
                        $result = mysqli_query($link, $sql);
                        while($row = mysqli_fetch_array($result)){
                            echo "<option value = '".$row['BranchName']."'>".$row['BranchName']."</option>";
                        }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                    echo "<table border='1'>
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
            ?>
        </div>
    </body>
</html>