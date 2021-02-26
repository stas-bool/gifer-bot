<?php

use Gifer\Gifer;
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

    $gifFile = sys_get_temp_dir()."/{$task['user_id']}.gif";
    if (!file_exists($gifFile)) {
        // TODO Если файл существует, то не надо генерировать gif
    }
    $gif = Gifer::createGif($font, $task['text'], $task['bg_color'], $task['font_color'], $task['speed']);

    file_put_contents($gifFile, $gif);

    $success = TelegramClient::sendGif(
        $task['user_id'],
        $gifFile,
        $_ENV['TELEGRAM_TOKEN'],
        $_ENV['TELEGRAM_PROXY'] ?? null
    );

    unlink($gifFile);
    if ($success) {
        $db->setTaskDone($task['id']);
    } else {
        // TODO Если не удалось выполнить, вернуть статус new
        sleep(10);
    }
}
