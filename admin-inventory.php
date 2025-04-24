<?php
include 'ActivityTracker.php';
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        <div class="container-fluid">
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
                <form class="d-flex justify-content-evenly" method="post" enctype="multipart/form-data">
                    <div class="form-floating w-100 me-3">
                        <select name="chooseBranch" id="chooseBranch" class="form-select form-select-lg">
                            <option value="" disabled selected> </option>
                            <?php
                                getBranches(); // Call the function to get branches
                            ?>
                        </select>   
                        <label for="chooseBranch">Choose Branch:</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:5%" name="searchProduct" value="searchProduct" data-bs-toggle="modal" data-bs-target="#errorSearchModal"><i class="fa-solid fa-arrow-right"></i></button>
                </form>

                <div class="d-flex w-100 align-items-center mt-4">
                    <a href="admin-inventory-add.php" class="btn bg-success text-light">Add</a>
                </div>               
            </div>               

            <div class="container">
                <?php                          
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_POST['searchProduct'])) {                                                         
                            getInventory();
                        }
                        elseif (isset($_POST['editProductBtn'])) {
                            getInventory();
                            editProduct();
                        }
                        elseif (isset($_POST['saveProductBtn'])) {
                            confirmEditProduct();
                            getInventory();
                        }
                        elseif (isset($_POST['deleteProductBtn'])) {                     
                            confirmDeleteProduct();
                            getInventory();
                        }
                        elseif (isset($_POST['confirmDeleteBtn'])) {
                            deleteProduct();
                            getInventory();
                        }                 
                    }
                ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"></script>

        <script>
            var editProductModalElement = document.getElementById("editProductModal");
            if (editProductModalElement) {
                var editProductModal = new bootstrap.Modal(editProductModalElement);
                editProductModal.show();
            }

            var errorSearchModalElement = document.getElementById("errorSearchModal");
            if (errorSearchModalElement) {
                var errorSearchModal = new bootstrap.Modal(errorSearchModalElement);
                errorSearchModal.show();
            }
            
            var deleteProductModalElement = document.getElementById("deleteProductModal");
            if (deleteProductModalElement) {
                var deleteProductModal = new bootstrap.Modal(deleteProductModalElement);
                deleteProductModal.show();
            }

            var confirmDeleteProductModal = document.getElementById("confirmDeleteProductModal");
            if (confirmDeleteProductModal) {
                var confirmDeleteProductModal = new bootstrap.Modal(confirmDeleteProductModal);
                confirmDeleteProductModal.show();
            }

            var confirmEditModal = document.getElementById("confirmEditModal");
            if (confirmEditModal) {
                var confirmEditModal = new bootstrap.Modal(confirmEditModal);
                confirmEditModal.show();
            }
        </script>
    </body>
</html>