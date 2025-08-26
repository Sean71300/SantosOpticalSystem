<?php
require_once 'setup.php';
$conn = connect();
header('Content-Type: application/json');
$rows = [];
$sql = "SELECT ProductID, Model, ProductImage FROM productMstr LIMIT 200";
if ($res = $conn->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $img = $r['ProductImage'];
        $exists = false;
        if ($img) {
            $path = __DIR__ . DIRECTORY_SEPARATOR . $img;
            $exists = file_exists($path);
        }
        $r['image_exists'] = $exists;
        $rows[] = $r;
    }
}
echo json_encode(['count'=>count($rows),'rows'=>$rows]);
?>
