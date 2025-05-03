<?php
include 'setup.php';
include 'adminFunctions.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
$limit = ($period === 'daily') ? 7 : (($period === 'weekly') ? 4 : (($period === 'monthly') ? 6 : 3));

$data = getSalesDataByPeriod($period, $limit);
echo json_encode($data);
?>