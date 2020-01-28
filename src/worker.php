<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config.php';

$db = DBConnect::connect();
$task = $db->getTask();
if (!$task) {
    exit(0);
}

function calcWidth($text, $font)
{
    $box = imagettfbbox(20, 0, $font, $text);
    return  abs($box[4] - $box[0]);
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

        if (calcWidth("{$row}{$word} ", $font) > 700) {
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
    $client->request(
        'POST',
        'sendAnimation',
        [
            'proxy' => 'socks5://127.0.0.1:8888',
            'multipart' => toMultiPart([
                'chat_id' => $chatId,
                'animation' => fopen($gifFile, 'r')
            ])
        ]
    );
}

$font = __DIR__.'/../NotoSans-Regular.ttf';
$gifWidth = 500;
$gifRowHeight = 27;
$fontSize = 20;

$textCoordX = 5;
$textCoordY = 20;

$animation = new Imagick();
$animation->setFormat("gif");

$formatedTextArray = splitText($task['text'], $font);
$formatedText = implode("", $formatedTextArray);
$textLength = mb_strlen($formatedText);

for ($lastSymbol = 1; $lastSymbol <= $textLength; $lastSymbol++) {
    $image = new Imagick();
    $image->setResourceLimit(6, 1);

    $image->newImage($gifWidth, $gifRowHeight * count($formatedTextArray), new ImagickPixel($task['bg_color']));
    $draw = new ImagickDraw();
    $draw->setFillColor(new ImagickPixel($task['font_color']));
    $draw->setFontSize($fontSize);
    $draw->setFont($font);

    $textToImage = mb_substr($formatedText, 0, $lastSymbol);
    $image->annotateImage($draw, $textCoordX, $textCoordY, 0, $textToImage);
    $image->setImageFormat('png');
    $animation->addImage($image);
    $animation->nextImage();
    $animation->setImageDelay(100 / $task['speed']);
    $image->clear();
}
$animation->setImageDelay(300);

$gifFile = "/tmp/{$task['user_id']}.gif";
$animation->writeImages($gifFile, true);
$animation->clear();

sendGif($task['user_id'], $gifFile);
unlink($gifFile);
$db->setTaskDone($task['id']);
