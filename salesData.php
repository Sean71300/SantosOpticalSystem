<?php
include 'setup.php';
include 'adminFunctions.php';
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
if (!in_array($days, [7,30,365])) $days = 7;
$data = getSalesOverviewData($days);
echo json_encode(['success' => true, 'data' => $data]);
