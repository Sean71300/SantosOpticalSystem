<?php
require_once 'connect.php';
include 'ActivityTracker.php';
include 'loginChecker.php';

include 'order-functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <title>Admin | Orders</title>
        <style>
            body {
                background-color: #f5f7fa;
                display: flex;
            }
            .sidebar {
                background-color: white;  /* Changed from #2c3e50 to white */
                height: 100vh;
                padding: 20px 0;
                color: #2c3e50;  /* Changed text color for better contrast */
                position: fixed;
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);  /* Added subtle shadow */
            }

            .sidebar-header {
                padding: 0 20px 20px;
                border-bottom: 1px solid rgba(0,0,0,0.1);  /* Changed border color */
            }

            .sidebar-item {
                padding: 12px 20px;
                margin: 5px 0;
                border-radius: 0;
                display: flex;
                align-items: center;
                color: #2c3e50;  /* Changed text color */
                transition: all 0.3s;
                text-decoration: none;  /* Removed underline */
            }

            .sidebar-item:hover {
                background-color: #f8f9fa;  /* Lighter hover state */
                color: #2c3e50;
            }

            .sidebar-item.active {
                background-color: #e9ecef;  /* Lighter active state */
                color: #2c3e50;
                font-weight: 500;
            }   
            .sidebar-item i {
                margin-right: 10px;
                width: 20px;
                text-align: center;
            }
            .main-content {
                margin-left: 250px;
                padding: 20px;
                width: calc(100% - 250px);
            }
            .table-container {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 20px;
            }
        </style>
    </head>

    <body>
        <?php include "employeeSidebar.php"?>
        
        <div class="main-content">
            <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fa-solid fa-pen-to-square me-2"></i>Orders</h1>
                    <a class="btn btn-primary" href="customerCreate.php" role="button">
                        <i class="fas fa-plus me-2"></i>New Order
                    </a>            
                </div>
            </div>
        </div>    
    </body>
</html>