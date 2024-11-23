<?php

require_once 'lib/config.php';

header('Content-Type: application/json');

$config = new DashboardConfig();
$data = $config->load();

echo json_encode($data);

?>
