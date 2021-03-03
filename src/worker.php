<?php

use Bot\DB;
use Bot\model\Config;
use Bot\model\Task;
use Bot\Registry;
use Gifer\Gifer;
use Bot\TelegramClient;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../config/');
$dotenv->load();
$registry = Registry::getInstance();
$registry->pdo = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
DB::createTables($registry->pdo);
while (true) {
    $transaction = $registry->pdo->beginTransaction();
    $task = Task::find()->where(['status' => Task::STATUS_NEW])->forUpdate()->one();
    if (is_null($task)) {
        $registry->pdo->rollBack();
        sleep(10);
        continue;
    }

    $font = __DIR__ . '/../fonts/NotoSans-Regular.ttf';

    $gifFile = sys_get_temp_dir()."/{$task->getId()}.gif";
    $config = Config::find()->byId($task->getConfig());
    if (!file_exists($gifFile)) {
        $gif = Gifer::createGif($font, $task->getText(), $config->getBgColor(), $config->getFontColor(), $config->getSpeed());
        file_put_contents($gifFile, $gif);
    }

    $success = TelegramClient::sendGif(
        $config->getId(),
        $gifFile,
        $_ENV['TELEGRAM_TOKEN'],
        $_ENV['TELEGRAM_PROXY'] ?? null
    );

    if ($success) {
        unlink($gifFile);
        $task->setStatus(Task::STATUS_DONE)->update();
        $registry->pdo->commit();
    } else {
        $registry->pdo->rollBack();
        sleep(10);
    }
}
