<?php
// Temporary debug helper - remove after use
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug: including employeeRecords.php</h2>\n";

// Buffer output so we can see raw errors first
ob_start();
include __DIR__ . '/employeeRecords.php';
$out = ob_get_clean();

// Show captured output (or nothing if a fatal occurred and wasn't captured)
echo "<pre>Captured output:\n" . htmlspecialchars(substr($out, 0, 10000)) . "</pre>\n";

echo "<p>If you still see a blank page or HTTP 500, check your server's PHP/Apache error log (common paths: /var/log/apache2/error.log or c:/xampp/apache/logs/error.log).</p>\n";

// End
?>
