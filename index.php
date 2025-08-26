<?php
// Simple redirect to the main page. ActivityTracker is not included here
// to avoid any output before sending HTTP headers.
header('Location: pdmain.php');
exit();
?>