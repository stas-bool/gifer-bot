<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
use Bot\Config;
use Bot\DB;

ini_set("xdebug.overload_var_dump", "off");
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
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
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $db->saveConfig($appConfig);
    die();
});
$botman->hears('/set_speed(.*)', function (BotMan $bot, $speed) use (&$db) {
    $speed = trim($speed);
    $userId = $bot->getUser()->getId();
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $appConfig->setSpeed($speed);
    $saveResult = $db->saveConfig($appConfig);
    if (!$saveResult || $appConfig->hasErrors()) {
        $bot->reply(implode("\n", $appConfig->getErrors()));
    } else {
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/set_font_color(.*)', function (BotMan $bot, $fontColor) use (&$db) {
    $fontColor = trim($fontColor);
    $userId = $bot->getUser()->getId();
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $appConfig->setFontColor($fontColor);
    $saveResult = $db->saveConfig($appConfig);
    if (!$saveResult || $appConfig->hasErrors()) {
        $bot->reply(implode("\n", $appConfig->getErrors()));
    } else {
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/set_bg_color(.*)', function (BotMan $bot, $bgColor) use (&$db) {
    $bgColor = trim($bgColor);
    $userId = $bot->getUser()->getId();
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $appConfig->setBgColor($bgColor);
    $saveResult = $db->saveConfig($appConfig);
    if (!$saveResult || $appConfig->hasErrors()) {
        $bot->reply(implode("\n", $appConfig->getErrors()));
    } else {
        $bot->reply('Записал');
    }
    die();
});
$botman->hears('/light_theme', function (BotMan $bot) use (&$db) {
    $userId = $bot->getUser()->getId();
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $appConfig->setBgColor('#FFFFFF')->setFontColor('#000000');
    $saveResult = $db->saveConfig($appConfig);
    if (!$saveResult || $appConfig->hasErrors()) {
        $bot->reply(implode("\n", $appConfig->getErrors()));
    } else {
        $bot->reply('Установлена светлая тема');
    }
    die();
});
$botman->hears('/dark_theme', function (BotMan $bot) use (&$db) {
    $userId = $bot->getUser()->getId();
    $appConfig = Config::get($userId, $db->getConfigByUserId($userId));
    $appConfig->setBgColor('#000000')->setFontColor('#FFFFFF');
    $saveResult = $db->saveConfig($appConfig);
    if (!$saveResult || $appConfig->hasErrors()) {
        $bot->reply(implode("\n", $appConfig->getErrors()));
    } else {
        $bot->reply('Установлена темная тема');
    }
    die();
});
$botman->hears('(.*)', function (BotMan $bot, $text) use (&$db) {
    if (iconv_strlen($text) > 300) {
        $bot->reply(iconv_strlen($text));
        $bot->reply("Слииииишком длинный текст. Я могу обработать текст не длинее 300 символов😕");
        die();
    }
    $bot->reply('Обрабатываю...');
    $userId = $bot->getUser()->getId();
    $config = Config::get($userId, $db->getConfigByUserId($userId));
    $db->saveConfig($config);
    $db->newTask($userId, $text);
});
$botman->listen();

