<?php 
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