<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u809407821_santosopticals');
define('DB_PASSWORD', '8Bt?Q0]=w');
define('DB_NAME', 'u809407821_santosopticals');
 
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>