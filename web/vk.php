<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
use Bot\Config;
use Bot\DBConnect;
use BotMan\Drivers\Vk\VkDriver;


$appConfig = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$db = DBConnect::connect($appConfig['database']);
$config = [
    // Your driver-specific configuration
//    "telegram" => $appConfig['telegram']
    "access_token" => '0e9adee36161f6e653cf8a2a659cde42b42f360da4bf3a291128fa850d2743cdc96cf3af418f857e0f2b3',
    "api_version" => '5.50'
];
DriverManager::loadDriver(VkDriver::class);
$botman = BotManFactory::create($config);

$botman->hears('/start', function (BotMan $bot) {
    $bot->reply('Tested');
});
$botman->listen();
