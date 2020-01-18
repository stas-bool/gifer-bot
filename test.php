<?php

$text = $argv[1];

$animation = new Imagick();
$animation->setFormat("gif");
for ($end = 1; $end <= strlen($text); $end++) {
    $image = new Imagick();
    $image->setResourceLimit(6, 1);
    $image->newImage(500, 30, new ImagickPixel('yellow'));
    $draw = new ImagickDraw();
    $draw->setFillColor(new ImagickPixel('black'));
    $draw->setFontSize(30);
//$draw->setFont('Consolas NF');

    $image->annotateImage($draw, 10, 30, 0, substr($text, 0, $end));
    $image->setImageFormat('jpg');
    $image->writeImage("/tmp/test-image{$end}.png");
    $animation->addImage($image);
    $animation->setImageDelay(20);
    $animation->nextImage();
}
$animation->writeImages("/tmp/test.gif", true);
