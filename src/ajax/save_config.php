<?php

require_once 'lib/config.php';

header('Content-Type: application/json');

if (!isset($_POST['config'])) {
  http_response_code(400);
  echo json_encode(['error' => 'No configuration data provided']);
  exit;
}

$configData = json_decode($_POST['config'], true);
if ($configData === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON data']);
  exit;
}

$config = new DashboardConfig();
$result = $config->save($configData);

echo json_encode(['success' => $result]);

?>
