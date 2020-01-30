<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;

ini_set("xdebug.overload_var_dump", "off");
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config.php';
$config = [
    // Your driver-specific configuration
     "telegram" => [
        "token" => "887931185:AAEu_F46a_nR87kKeBRN_tUIvRohO4XklSw"
     ]
];
DriverManager::loadDriver(TelegramDriver::class);
$botman = BotManFactory::create($config);

$botman->hears('/start', function (BotMan $bot) {
    $bot->reply('Я умею конвертировать текст в гифку. Emoji пока не поддерживаются, но в ближайшее время я что-нибудь с этим сделаю');
    $bot->reply('Давай, напиши мне что-нибудь');
    $appConfig = Config::load($bot->getUser()->getId());
    $appConfig->save();
    die();
});
$botman->hears('/set_speed(.*)', function (BotMan $bot, $speed) {
    $speed = trim($speed);
    $appConfig = Config::load($bot->getUser()->getId());
    if (preg_match('/\d/', $speed) === 1 AND $speed >= 1 AND $speed <= 10) {
        $appConfig->setSpeed($speed)->save();
        $bot->reply('Записал');
    } elseif (is_int(strpos($speed, 'default'))) {
        $appConfig->setSpeed(5)->save();
        $bot->reply('Установил дефолтную скорость - 5');
    } else {
        $bot->reply('Чтобы изменить скорость отправь команду /set_speed [1-10]
Например /set_speed 5');
    }
    die();
});
$botman->hears('/set_font_color(.*)', function (BotMan $bot, $fontColor) {
    $fontColor = trim($fontColor);
    $appConfig = Config::load($bot->getUser()->getId());
    if (preg_match('/^\#[0-9A-F]{6}$/', $fontColor)) {
        $appConfig->setFontColor($fontColor)->save();
        $bot->reply("\"Цвет шрифта {$fontColor}\" - записал");
    } elseif (is_int(strpos($fontColor, 'default'))) {
        $appConfig->setFontColor('#000000')->save();
        $bot->reply('"Цвет шрифта #000000" - записал');
    } else {
        $bot->reply('Чтобы установить цвет шрифта отправь команду /set_font_color [цвет]
Цвет должен быть в таком формате #000000');
    }
    die();
});
$botman->hears('/set_bg_color(.*)', function (BotMan $bot, $bgColor) {
    $bgColor = trim($bgColor);
    $appConfig = Config::load($bot->getUser()->getId());
    if (preg_match('/^\#[0-9A-F]{6}$/', $bgColor)) {
        $appConfig->setBgColor($bgColor)->save();
        $bot->reply("\"Цвет фона {$bgColor}\" - записал");
        $bot->reply('Записал');
    } elseif (is_int(strpos($bgColor, 'default'))) {
        $appConfig->setBgColor('#FFFFFF')->save();
        $bot->reply('"Цвет фона #FFFFFF" - записал');
    } else {
        $bot->reply('Чтобы установить цвет шрифта отправь команду /set_bg_color [цвет]
Цвет должен быть в таком формате #FFFFFF');
    }
    die();
});
$botman->hears('/light_theme', function (BotMan $bot) {
    $appConfig = Config::load($bot->getUser()->getId());
    $appConfig->setBgColor('#FFFFFF')->setFontColor('#000000')->save();
    $bot->reply('Установлена светлая тема');
    die();
});
$botman->hears('/dark_theme', function (BotMan $bot) {
    $appConfig = Config::load($bot->getUser()->getId());
    $appConfig->setBgColor('#000000')->setFontColor('#FFFFFF')->save();
    $bot->reply('Установлена темная тема');
    die();
});
$botman->hears('(.*)', function (BotMan $bot, $text) {
    if (iconv_strlen($text) > 300) {
        $bot->reply(iconv_strlen($text));
        $bot->reply("Слииииишком длинный текст. Я могу обработать текст не длинее 300 символов😕");
        die();
    }
    $bot->reply('Обрабатываю...');
    $userId = $bot->getUser()->getId();
    DBConnect::connect()->newTask(Config::load($userId), $text);
});
$botman->listen();

