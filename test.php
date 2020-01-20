<?php

$text = $argv[1];
//$text = "Google объявил, что планирует добавить в библиотеку игрового стримингового сервиса Google Stadia более 120 игр в 2020 году, включая Cyberpunk 2077 и Watch Dogs Legion, среди которых будет 10 временно эксклюзивных (это значит, что вскоре игры появятся и на других платформах).";

$animation = new Imagick();
$animation->setFormat("gif");

function calcWidth($text)
{
    $box = imagettfbbox(30, 0, '/usr/share/fonts/truetype/msttcorefonts/ariali.ttf', $text);
    $width = abs($box[4] - $box[0]);
    return $width;
}

function splitText($text)
{
    $words = explode(' ', $text);
    $row = '';
    $totalText = [];

    // Пока не кончатся слова в тексте
    while (count($words) !== 0) {
        // Убираем одно слово из начала текста
        $word = array_shift($words);

        if (calcWidth("{$row}{$word} ") > 500) {
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

$formatedTextArray = splitText($text);
$formatedText = implode("", $formatedTextArray);
$textLength = mb_strlen($formatedText);

for ($end = 1; $end <= $textLength; $end++) {
    $image = new Imagick();
    $image->setResourceLimit(6, 1);


    $image->newImage(500, 40 * count($formatedTextArray), new ImagickPixel('#FFEBEB'));
    $draw = new ImagickDraw();
    $draw->setFillColor(new ImagickPixel('black'));
    $draw->setFontSize(30);
    $draw->setFont('Arial');

    $textToImage = mb_substr($formatedText, 0, $end);
    print "$end\t$textToImage\n";
    $image->annotateImage($draw, 10, 30, 0, $textToImage);
    $image->setImageFormat('png');
    $image->roundCorners(5,5);
//    $image->writeImage("/tmp/test-image{$end}.png");
    $animation->addImage($image);
    $animation->nextImage();
    $animation->setImageDelay(10);
}
$animation->setImageDelay(300);
$animation->writeImages("/tmp/test.gif", true);
