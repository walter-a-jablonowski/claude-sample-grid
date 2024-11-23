<?php

require_once 'lib/file_reader.php';

header('Content-Type: application/json');

$reader = new FileReader();
$files = $reader->listFiles();

echo json_encode($files);

?>
