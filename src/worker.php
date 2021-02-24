<?php

use Bot\Gifer;
use Bot\DB;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
$task = $db->getNewTask();
if (is_null($task)) {
    sleep(10);
    exit(0);
}


$font = __DIR__ . '/../fonts/NotoSans-Regular.ttf';
$gifFilePath = (new Gifer($font, $task))->process();
Gifer::sendGif($task['user_id'], $gifFilePath, $_ENV['TELEGRAM_TOKEN'], $_ENV['TELEGRAM_PROXY'] ?? null);
unlink($gifFilePath);
$db->setTaskDone($task['id']);
