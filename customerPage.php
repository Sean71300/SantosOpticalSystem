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
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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

