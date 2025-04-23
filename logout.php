<?php
include 'ActivityTracker.php';
$_SESSION = array();
session_destroy();

header("location: index.php");
exit;
?>