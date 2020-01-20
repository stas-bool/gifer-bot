<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;

require_once __DIR__ . '/vendor/autoload.php';
$config = [
    // Your driver-specific configuration
     "telegram" => [
        "token" => "887931185:AAEu_F46a_nR87kKeBRN_tUIvRohO4XklSw"
     ]
];

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

$textToGif = function (BotMan $bot, $text)
{
    $font = '/var/www/html/bot/NotoSans-Regular.ttf';
    $animation = new Imagick();
    $animation->setFormat("gif");

    $formatedTextArray = splitText($text, $font);
    $formatedText = implode("", $formatedTextArray);
    $textLength = mb_strlen($formatedText);

    for ($end = 1; $end <= $textLength; $end++) {
        $image = new Imagick();
        $image->setResourceLimit(6, 1);

        $image->newImage(500, 30 * count($formatedTextArray), new ImagickPixel('#FFEBEB'));
        $draw = new ImagickDraw();
        $draw->setFillColor(new ImagickPixel('black'));
        $draw->setFontSize(20);
        $draw->setFont($font);

        $textToImage = mb_substr($formatedText, 0, $end);
        $image->annotateImage($draw, 5, 20, 0, $textToImage);
        $image->setImageFormat('png');
        $image->roundCorners(5,5);
        $animation->addImage($image);
        $animation->nextImage();
        $animation->setImageDelay(10);
        $image->clear();
    }
    $animation->setImageDelay(300);

    $gifFile = "/tmp/{$bot->getUser()->getId()}.gif";
    $animation->writeImages($gifFile, true);
    $animation->clear();

    $attachment = new Image($gifFile);
    $message = OutgoingMessage::create()->withAttachment($attachment);
    $bot->reply($message);
};
DriverManager::loadDriver(TelegramDriver::class);

$botman = BotManFactory::create($config);


$botman->hears('(.*)', $textToGif);

$botman->listen();
