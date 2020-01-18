<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

$config = [
    // Your driver-specific configuration
     "telegram" => [
        "token" => "729686719:AAFlSz_aR6LurW4rtX1S747EDcbKa18u1aU"
     ]
];

DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

$botman = BotManFactory::create($config);

$message = $botman->getMessage();

//$botman->reply();
