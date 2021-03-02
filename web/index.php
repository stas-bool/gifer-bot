<?php

use Bot\model\Task;
use Bot\Registry;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
use Bot\model\Config;

ini_set("xdebug.overload_var_dump", "off");
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../config/');
$dotenv->load();

$pdo = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
$registry = Registry::getInstance();
$registry->pdo = $pdo;

$botmanConfig = [
    // Your driver-specific configuration
     "telegram" => ['token' => $_ENV['TELEGRAM_TOKEN']]
];
DriverManager::loadDriver(TelegramDriver::class);
$botman = BotManFactory::create($botmanConfig);

$botman->hears('/start', function (BotMan $bot) use (&$db) {
    $bot->reply('Я умею конвертировать текст в gif. Text to gif converter');
    $bot->reply('Напиши мне что-нибудь. Text me something.');
    $userId = $bot->getUser()->getId();
    $config = Config::find()->byId($userId);
    if (is_null($config)) {
        $config = new Config($userId);
        $config->insert();
    }
    die();
});
$botman->hears('/set_speed(.*)', function (BotMan $bot, $speed) use (&$db) {
    $speed = trim($speed);
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $config->setSpeed($speed);
    if ($config->hasErrors()) {
        $bot->reply(implode("\n", $config->getErrors()));
    } else {
        $config->update();
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/set_font_color(.*)', function (BotMan $bot, $fontColor) use (&$db) {
    $fontColor = trim($fontColor);
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $config->setFontColor($fontColor);
    if ($config->hasErrors()) {
        $bot->reply(implode("\n", $config->getErrors()));
    } else {
        $config->update();
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/set_bg_color(.*)', function (BotMan $bot, $bgColor) use (&$db) {
    $bgColor = trim($bgColor);
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $config->setBgColor($bgColor);
    if ($config->hasErrors()) {
        $bot->reply(implode("\n", $config->getErrors()));
    } else {
        $config->update();
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/light_theme', function (BotMan $bot) use (&$db) {
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $config->setBgColor('#FFFFFF')->setFontColor('#000000');
    if ($config->hasErrors()) {
        $bot->reply(implode("\n", $config->getErrors()));
    } else {
        $config->update();
        $bot->reply('Установлена светлая тема');
    }
    die();
});
$botman->hears('/dark_theme', function (BotMan $bot) use (&$db) {
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $config->setBgColor('#000000')->setFontColor('#FFFFFF');
    if ($config->hasErrors()) {
        $bot->reply(implode("\n", $config->getErrors()));
    } else {
        $config->update();
        $bot->reply('Установлена темная тема');
    }
    die();
});
$botman->hears('/(.*)', function (BotMan $bot) use (&$db) {
    $bot->reply('Команда не найдена');
    die();
});
$botman->hears('(.*)', function (BotMan $bot, $text) use (&$db) {
    if (iconv_strlen($text) > 300) {
        $bot->reply("Слииииишком длинный текст. Я могу обработать текст не длинее 300 символов😕");
        die();
    }
    $bot->reply('Обрабатываю...');
    $userId = $bot->getUser()->getId();
    $config = Config::findOrCreateDefault($userId);
    $db->newTask($userId, $text);
    $task = new Task(-1, $text, $config->getId());
    $task->insert();
});
$botman->listen();

