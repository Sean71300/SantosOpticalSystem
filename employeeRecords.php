<?php
    include 'ActivityTracker.php';
    include_once 'employeeFunctions.php';   
    include 'loginChecker.php';

?>
    
<html>
        <title>
            Employee Records
        </title>
        <head>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
            <link rel="stylesheet" href="customCodes/custom.css">
            
        </head>
        <body class="bg-body-tertiary">
            <?php include "Navigation.php"?> 
            <div class="container category-container">
                <div class="row">
                <h1>Employee Records</h1>
                <a class="col-2 mt-2 btn btn-primary" href="employeeCreate.php" role="button">New Employee</a>                
                <table class="mt-2 table text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Role</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>                      
                        <?php employeeData(); ?>                      
                    </tbody>
                </table>
                </div>
            </div>
            
        </body>
</html>

