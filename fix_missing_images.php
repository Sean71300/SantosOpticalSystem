<?php
// Safe utility: scan productMstr.ProductImage values and detect missing files on disk.
// Usage (dry-run): open this file in a browser. To actually apply updates add ?confirm=1
// It reads DB credentials from connect.php (same as the app). It will create a backup SQL
// in the logs/ folder before performing updates.

header('Content-Type: text/html; charset=utf-8');
echo "<h3>Missing product images scanner</h3>";
echo "<p>This script will show products whose image file cannot be found on the server. To actually replace missing ProductImage values with a placeholder, re-open this page with <code>?confirm=1</code>.</p>";

require_once __DIR__ . '/connect.php'; // defines $link (mysqli)

if (!isset($link) || !$link) {
    echo "<p><strong>DB connection not available.</strong></p>";
    exit;
}

// placeholder to use when applying fixes
$placeholder = 'Images/logo.png';

function candidate_paths($val) {
    $val = trim($val);
    $candidates = [];
    // raw relative path
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . $val;
    // with no leading slash
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . ltrim($val, '/\\');
    // common folders
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($val);
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . basename($val);
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . basename($val);
    return array_unique($candidates);
}

$sql = "SELECT ProductID, ProductImage FROM productMstr";
$res = mysqli_query($link, $sql);
if (!$res) {
    echo "<p>DB query failed: " . htmlspecialchars(mysqli_error($link)) . "</p>";
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

$missing = [];
foreach ($rows as $r) {
    $pid = $r['ProductID'];
    $img = $r['ProductImage'];
    if (empty($img)) {
        $missing[] = ['ProductID'=>$pid,'ProductImage'=>$img,'found'=>false,'checked'=>[]];
        continue;
    }
    $checked = candidate_paths($img);
    $found = false; $foundPath = null;
    foreach ($checked as $p) {
        if (is_readable($p)) { $found = true; $foundPath = $p; break; }
    }
    if (!$found) {
        $missing[] = ['ProductID'=>$pid,'ProductImage'=>$img,'found'=>false,'checked'=>$checked];
    }
}

echo "<p>Total products scanned: " . count($rows) . ", missing/unresolved images: " . count($missing) . "</p>";

if (count($missing) === 0) {
    echo "<p>No action required.</p>";
    exit;
}

echo "<table border='1' cellpadding='4' cellspacing='0'>";
echo "<tr><th>ProductID</th><th>ProductImage</th><th>Checked paths</th></tr>";
foreach ($missing as $m) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($m['ProductID']) . "</td>";
    echo "<td>" . htmlspecialchars($m['ProductImage']) . "</td>";
    echo "<td><small>";
    foreach ($m['checked'] as $c) {
        echo htmlspecialchars($c) . ($c === end($m['checked']) ? '' : '<br>');
    }
    echo "</small></td>";
    echo "</tr>";
}
echo "</table>";

// apply updates only if confirm=1 is present
if (isset($_GET['confirm']) && $_GET['confirm'] == '1') {
    // ensure logs dir exists
    $logsDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logsDir)) @mkdir($logsDir, 0755, true);
    $backupFile = $logsDir . DIRECTORY_SEPARATOR . 'missing_images_backup_' . date('Ymd_His') . '.sql';
    $fh = fopen($backupFile, 'w');
    if ($fh === false) {
        echo "<p>Failed to create backup file at: " . htmlspecialchars($backupFile) . "</p>";
        exit;
    }
    fwrite($fh, "-- backup of ProductImage updates on " . date('c') . "\n");

    $updateSql = "UPDATE productMstr SET ProductImage = ? WHERE ProductID = ?";
    $stmt = mysqli_prepare($link, $updateSql);
    if (!$stmt) {
        echo "<p>Prepare failed: " . htmlspecialchars(mysqli_error($link)) . "</p>";
        exit;
    }

    $applied = 0;
    foreach ($missing as $m) {
        $pid = $m['ProductID'];
        // write SQL backup line
        $line = "UPDATE productMstr SET ProductImage = '" . mysqli_real_escape_string($link, $placeholder) . "' WHERE ProductID = '" . mysqli_real_escape_string($link, $pid) . "';\n";
        fwrite($fh, $line);
        mysqli_stmt_bind_param($stmt, 'ss', $placeholder, $pid);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) >= 0) $applied++;
    }
    fclose($fh);
    echo "<p>Applied updates: " . $applied . ". Backup SQL saved to: " . htmlspecialchars($backupFile) . "</p>";
    echo "<p><a href=\"fix_missing_images.php\">Return</a></p>";
    exit;
} else {
    // show link to apply
    $applyUrl = basename(__FILE__) . '?confirm=1';
    echo "<p>If you want to replace missing ProductImage values with <code>" . htmlspecialchars($placeholder) . "</code>, click: <a href=\"" . htmlspecialchars($applyUrl) . "\">Apply fixes (confirm)</a></p>";
    echo "<p>Or upload the missing files into your 'uploads/' or 'Images/' directory instead and reload this page.</p>";
}

?>
