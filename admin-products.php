<?php
include_once 'setup.php'; // Include the setup.php file
require_once 'connect.php'; //Connect to the database
include 'ActivityTracker.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
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