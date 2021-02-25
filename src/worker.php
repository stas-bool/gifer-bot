<?php

use Bot\Gifer;
use Bot\DB;
use Bot\TelegramClient;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../config/');
$dotenv->load();
$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
while (true) {
    $task = $db->getNewTask();
    if (is_null($task)) {
        sleep(10);
        continue;
    }


    $font = __DIR__ . '/../fonts/NotoSans-Regular.ttf';
    $gifFilePath = Gifer::create($font, $task['text'], $task['bg_color'], $task['font_color'],
        $task['speed'], $task['user_id'])
        ->process();

    $success = TelegramClient::sendGif(
        $task['user_id'],
        $gifFilePath,
        $_ENV['TELEGRAM_TOKEN'],
        $_ENV['TELEGRAM_PROXY'] ?? null
    );

    if ($success) {
        unlink($gifFilePath);
        $db->setTaskDone($task['id']);
    } else {
        sleep(10);
    }
}
