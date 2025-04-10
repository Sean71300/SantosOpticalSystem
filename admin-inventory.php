<?php
session_start();
include 'admin-inventory-funcs.php'; // Include the functions file

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
                <form class="d-flex justify-content-evenly" action="" method="post">
                    <div class="form-floating w-100 me-3">
                        <select name="chooseBranch" id="chooseBranch" class="form-select form-select-lg">
                            <option value="" disabled selected> </option>
                            <?php
                                getBranches(); // Call the function to get branches
                            ?>
                        </select>   
                        <label for="chooseBranch">Choose Branch:</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:20%">Search</button>
                </form>
                <div class="d-flex w-100 align-items-center mt-4">
                <button onclick="document.location='admin-inventory-add.php'" class="btn bg-success text-light">Add</button>
                </div>
            </div>          

            <?php                            
                if ($_SERVER["REQUEST_METHOD"] == "POST") { // Submit then display inventory
                    if (empty($_POST['chooseBranch'])) { //error handler
                        echo "<div class='alert alert-danger mt-3'>Please select a branch.</div>";
                        exit;
                    } 
                    else {
                        getInventory($_POST['chooseBranch']); // Call the function to get inventory
                    }
                }
            ?>
        </div>
    </body>
</html>