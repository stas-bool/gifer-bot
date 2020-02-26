<?php

use Bot\Gifer;
use Bot\DBConnect;

require_once __DIR__ . '/../vendor/autoload.php';

$appConfig = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$db = DBConnect::connect($appConfig['database']);
$task = $db->getTask();
if (!$task) {
    exit(0);
}


$font = __DIR__ . '/../fonts/NotoSans-Regular.ttf';
$gifer = new Gifer($font, $task);
$gifFile = $gifer->process();
Gifer::sendGif($task['user_id'], $gifFile, $appConfig['telegram']);
unlink($gifFile);
$db->setTaskDone($task['id']);
