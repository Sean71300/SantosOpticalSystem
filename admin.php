<?php
include_once 'setup.php'; // Include the setup.php file
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
    </style>

    <body>
        <div class="container">
            
            <?php
            $username = $_SESSION["username"];
            echo "<h1 style='text-align: center;'>Welcome $username</h1>";
            ?>

            <?php
            require_once 'connect.php';
            $sql = "SELECT bm.* FROM branchmaster bm JOIN employee e ON bm.BranchCode = e.BranchCode WHERE e.BranchCode = ?";

            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_branchcode);
                $param_branchcode = $_SESSION["branchcode"];

                if(mysqli_stmt_execute($stmt)){
                    $result = mysqli_stmt_get_result($stmt);
                    if(mysqli_num_rows($result) > 0){
                        while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                            echo "<table class='table table-bordered'>";
                            echo "<thead>";
                                echo "<tr>";
                                    echo "<th>Branch Code</th>";
                                    echo "<th>Branch Name</th>";
                                    echo "<th>Branch Location</th>";
                                    echo "<th>Contact No</th>";
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                                echo "<tr>";
                                    echo "<td>" . $row["BranchCode"] . "</td>";
                                    echo "<td>" . $row["BranchName"] . "</td>";
                                    echo "<td>" . $row["BranchLocation"] . "</td>";
                                    echo "<td>" . $row["ContactNo"] . "</td>";
                                echo "</tr>";
                            echo "</tbody>";
                            echo "</table>";
                        }
                        mysqli_free_result($result);
                    } else{
                        echo "<p class='lead'><em>No records were found.</em></p>";
                    }
                } else{
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                }
            }
            ?>
        </div>
    </body>
</html>