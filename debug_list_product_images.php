<?php
// Debug script: list product images and whether the file exists on disk.
// This script reads DB credentials from connect.php (without executing it)
// to avoid running any setup code that may crash on include.
header('Content-Type: application/json');

$connectFile = __DIR__ . DIRECTORY_SEPARATOR . 'connect.php';
$db = [
    'DB_SERVER' => 'localhost',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_NAME' => ''
];

if (file_exists($connectFile)) {
    $contents = file_get_contents($connectFile);
    if (preg_match("/define\('\s*DB_SERVER\s*',\s*'([^']+)'\)/i", $contents, $m)) $db['DB_SERVER'] = $m[1];
    if (preg_match("/define\('\s*DB_USERNAME\s*',\s*'([^']+)'\)/i", $contents, $m)) $db['DB_USERNAME'] = $m[1];
    if (preg_match("/define\('\s*DB_PASSWORD\s*',\s*'([^']*)'\)/i", $contents, $m)) $db['DB_PASSWORD'] = $m[1];
    if (preg_match("/define\('\s*DB_NAME\s*',\s*'([^']+)'\)/i", $contents, $m)) $db['DB_NAME'] = $m[1];
}

// Attempt mysqli connection safely
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db['DB_SERVER'], $db['DB_USERNAME'], $db['DB_PASSWORD'], $db['DB_NAME']);
if ($conn->connect_errno) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed', 'errno' => $conn->connect_errno, 'error_msg' => $conn->connect_error]);
    exit;
}

$rows = [];
$sql = "SELECT ProductID, Model, ProductImage FROM productMstr LIMIT 1000";
if ($res = $conn->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $img = $r['ProductImage'];
        $exists = false;
        if ($img) {
            // Normalize and check several probable locations
            $candidates = [];
            $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . $img;
            $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . ltrim($img, '/\\');
            $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . basename($img);
            foreach ($candidates as $p) {
                if (file_exists($p)) { $exists = true; break; }
            }
        }
        $r['image_exists'] = $exists;
        $rows[] = $r;
    }
}

echo json_encode(['success' => true, 'count'=>count($rows),'rows'=>$rows]);
?>
