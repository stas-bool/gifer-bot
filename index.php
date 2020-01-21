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
        $animation->setImageDelay(15);
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
DriverManager::loadDriver(TelegramDriver::class);
$botman = BotManFactory::create($config);
$botman->hears('/start', function (BotMan $bot) {
    $bot->reply('Я умею конвертировать текст в гифку. Emoji пока не поддерживаются, но в ближайшее время я что-нибудь с этим сделаю');
    $bot->reply('Давай, напиши мне что-нибудь');
    die();
});
$botman->hears('/settings', function (BotMan $bot) {
    $question = Question::create('Настройки')
        ->addButtons([
            Button::create('Скорость')->value('/set_speed'),
            Button::create('Цвет фона')->value('/set_background_color'),
            Button::create('Цвет текста')->value('/set_font_color'),

        ]);
    $bot->ask($question, function (Answer $answer) {});
    die();
});
$botman->hears('/set_speed', function (BotMan $bot) {
    $bot->startConversation(new SetSpeedConversation());
});
$botman->hears('(.*)', $textToGif);
$botman->listen();
