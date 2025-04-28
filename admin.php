<?php
include_once 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database
include 'ActivityTracker.php';
include 'loginChecker.php';

?>

<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <title>Admin | Dashboard</title>
    </head>

    <header class="mb-5">
        <?php
            include "employeeSidebar.php";
        ?>
    </header>

    <body>
        <div class="container"> 
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
    </body>
</html>