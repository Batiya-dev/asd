<?php
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$log = $data;
file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);

?>