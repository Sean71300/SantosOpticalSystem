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

            /* Spinner for input number */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;            
            }

            input[type=number] {
                -moz-appearance: textfield;
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

            <div class="container">
                <form method="post" enctype="multipart/form-data">
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productBranch" class="col-form-label">Branch:</label>
                        </div>
                        <div class="col-3">
                            <select name="productBranch" id="productBranch" class="form-select">
                                <?php getBranch(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem"> 
                            <label for="productName" class="col-form-label">Name:</label>
                        </div>
                        <div class="col-3">
                            <input type="text" name="productName" id="productName" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem"> 
                            <label for="productBrand" class="col-form-label">Brand:</label>
                        </div>
                        <div class="col-3">
                            <select name="productBrand" id="productBrand" class="form-select">
                                <?php getBrands(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productQty" class="col-form-label">Quantity:</label>
                        </div>
                        <div class="col-3">
                            <input type="number" name="productQty" id="productQty" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productCategory" class="col-form-label">Category:</label>
                        </div>
                        <div class="col-3">
                            <select name="productCategory" id="productCategory" class="form-select">
                                <?php getCategory(); ?>
                            </select>
                        </div>                        
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productShape" class="col-form-label">Shape:</label>
                        </div>
                        <div class="col-3">
                            <select name="productShape" id="productShape" class="form-select">
                                <?php getShapes(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productImg" class="col-form-label">Remarks:</label>
                        </div>
                        <div class="col-3">
                            <input class="form-control" type="text" id="productRemarks" name="productRemarks" required>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto" style="width: 5rem">
                            <label for="productImg" class="col-form-label">Image:</label>
                        </div>
                        <div class="col-3">
                            <input class="form-control" type="file" id="productImg" accept="image/png, image/jpeg" name="productImg" required>
                        </div>                        
                    </div>
                    <button type="submit" class="btn btn-success" style="width: 10%" name="addProduct" value="addProduct">Add</button>
                    <button type="reset" class="btn btn-danger" style="width: 10%">Reset</button> 
                    <?php 
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        addProduct(); 
                    }
                    ?>
                </form>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

        <script>
            var myModal = new bootstrap.Modal(document.getElementById("successModal"));
            myModal.show();
        </script>
    </body>
</html>