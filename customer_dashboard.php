<?php
include_once 'connect.php';
include_once 'setup.php';
include 'ActivityTracker.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Refresh: 2; url=login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Dashboard</title>
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>       
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
    </head>

    <header>
        <?php include 'Navigation.php'; ?>
    </header>

    <body>
        <div class="container mt-5 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h1 class="text-center">Customer Dashboard</h1>
                    <p class="text-center">Welcome to your dashboard! Here you can manage your orders and account settings.</p>
                </div>
            </div>
        </div>

        <footer class="py-5 border-top mt-5 pt-4" style="background-color: #ffffff; margin-top: 50px; border-color: #ffffff;">
            <div class="container">
                <div class="row text-center text-md-start">
                    <div class="col-md-3 mb-3 mb-md-0 text-center">
                        <img src="Images/logo.png" alt="Logo" width="200">
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <h6 class="fw-bold">PRODUCTS</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                            <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <h6 class="fw-bold">About</h6>
                        <ul class="list-unstyled">
                            <li><a href="aboutus.php" class="text-dark text-decoration-none">About Us</a></li>
                            <li><a href="ourservices.php" class="text-dark text-decoration-none">Services</a></li>
                        </ul>
                    </div>

                    <div class="col-md-3">
                        <h6 class="fw-bold">CONTACT US!</h6>
                        <p class="mb-1">Address: #6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                        <p class="mb-1">Phone: 027-508-4792</p>
                        <p class="mb-1">Cell: 0932-844-7068</p>
                        <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                    </div>
                </div>
                <div class="container-fluid text-center py-3" style="background-color: white">
                    <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>