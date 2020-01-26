<?php


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config.php';

$config = Config::load(132763295);
$config->setSpeed(1)->setBgColor('white')->setFontColor('black')->save();
