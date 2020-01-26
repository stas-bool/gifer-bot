<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use BotMan\Drivers\Telegram\TelegramDriver;
use GuzzleHttp\Client;

ini_set("xdebug.overload_var_dump", "off");
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config.php';
$config = [
    // Your driver-specific configuration
     "telegram" => [
        "token" => "887931185:AAEu_F46a_nR87kKeBRN_tUIvRohO4XklSw"
     ]
];
DriverManager::loadDriver(TelegramDriver::class);
$botman = BotManFactory::create($config);

function calcWidth($text, $font)
{
    $box = imagettfbbox(20, 0, $font, $text);
    $width = abs($box[4] - $box[0]);
    return $width;
}
function splitText($text, $font)
{
    $words = explode(' ', $text);
    $row = '';
    $totalText = [];

    // Пока не кончатся слова в тексте
    while (count($words) !== 0) {
        // Убираем одно слово из начала текста
        $word = array_shift($words);

        if (calcWidth("{$row}{$word} ", $font) > 650) {
            // Если ширина строки + слово > 500
            // То слово возвращаем обратно
            array_unshift($words, $word);
            // и добавляем конец строки в массив
            $totalText[] = $row.PHP_EOL;
            $row = '';
        } else {
            $row .= $word . " ";
        }
    }
    $totalText[] = $row;
    return $totalText;
}
function toMultiPart(array $arr) {
    $result = [];
    array_walk($arr, function($value, $key) use(&$result) {
        $result[] = ['name' => $key, 'contents' => $value];
    });
    return $result;
}
function sendGif($chatId, $gifFile)
{
    $client = new Client([
        'base_uri' => 'https://api.telegram.org/bot887931185:AAEu_F46a_nR87kKeBRN_tUIvRohO4XklSw/'
    ]);
    $result = $client->request(
        'POST',
        'sendAnimation',
        [
//            'proxy' => 'socks5://127.0.0.1:8888',
            'multipart' => toMultiPart([
                'chat_id' => $chatId,
                'animation' => fopen($gifFile, 'r')
            ])
        ]
    );
}

$textToGif = function (BotMan $bot, $text)
{
    if (strlen($text) > 300) {
        $bot->reply("Слииииишком длинный текст. Я могу обработать текст не длинее 300 символов😕");
        die();
    }
    $bot->reply('Обрабатываю');
    $font = __DIR__.'/NotoSans-Regular.ttf';
    $animation = new Imagick();
    $animation->setFormat("gif");

    $formatedTextArray = splitText($text, $font);
    $formatedText = implode("", $formatedTextArray);
    $textLength = mb_strlen($formatedText);

    $appConfig = Config::load($bot->getUser()->getId());
    for ($end = 1; $end <= $textLength; $end++) {
        $image = new Imagick();
        $image->setResourceLimit(6, 1);

        $image->newImage(500, 30 * count($formatedTextArray), new ImagickPixel($appConfig->getBgColor()));
        $draw = new ImagickDraw();
        $draw->setFillColor(new ImagickPixel($appConfig->getFontColor()));
        $draw->setFontSize(20);
        $draw->setFont($font);

        $textToImage = mb_substr($formatedText, 0, $end);
        $image->annotateImage($draw, 5, 20, 0, $textToImage);
        $image->setImageFormat('png');
//        $image->roundCorners(5,5);
        $animation->addImage($image);
        $animation->nextImage();
        $animation->setImageDelay(100 / $appConfig->getSpeed());
        $image->clear();
    }
    $animation->setImageDelay(300);

    $gifFile = "/tmp/{$bot->getUser()->getId()}.gif";
    $animation->writeImages($gifFile, true);
    $animation->clear();

    sendGif($bot->getUser()->getId(), $gifFile);
    unlink($gifFile);
    die();
};
$botman->hears('/start', function (BotMan $bot) {
    $bot->reply('Я умею конвертировать текст в гифку. Emoji пока не поддерживаются, но в ближайшее время я что-нибудь с этим сделаю');
    $bot->reply('Давай, напиши мне что-нибудь');
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
});
$botman->hears('/dark_theme', function (BotMan $bot) {
    $appConfig = Config::load($bot->getUser()->getId());
    $appConfig->setBgColor('#000000')->setFontColor('#FFFFFF')->save();
    $bot->reply('Установлена темная тема');
});
$botman->hears('(.*)', $textToGif);
$botman->listen();
