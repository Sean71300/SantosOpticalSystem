<?php
  session_start();
  include_once 'customerFunctions.php'; 
  $customerData = customerData();
?>
    
<html>
        <title>
            Customer Page
        </title>
        <head>
            <script src="js/bootstrap.bundle.min.js"></script>
            <script src="https://kit.fontawesome.com/af468059ce.js" crossorigin="anonymous"></script>
            <link rel="stylesheet" href="css/bootstrap.min.css">
            <link rel="stylesheet" href="customCodes/custom.css">
        </head>
        <body class="bg-body-tertiary">
            <?php include "Navigation.php"?> 
            <div class="container category-container">
                <div class="row">
                <h1>Customer Records</h1>
                <a class="col-2 mt-2 btn btn-primary" href="customerCreate.php" role="button">New Customer</a>                
                <table class="mt-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>                      
                        <?php echo $customerData; ?>                      
                    </tbody>
                </table>
                </div>
            </div>
            
        </body>
</html>

