<?php


namespace Bot;


use Imagick;
use ImagickDraw;
use ImagickPixel;

class Gifer
{
    use GifSenderTrait;
    public const GIF_WIDTH = 500;
    public const GIF_ROW_HEIGHT = 29;
    public const FONT_SIZE = 20;
    public const TEXT_COORD_X = 5;
    public const TEXT_COORD_Y = 20;

    private $fontFile;
    private $text;
    private $bgColor;
    private $fontColor;
    private $speed;
    private $userId;

    public function __construct(string $fontFile, array $taskParams)
    {
        $this->fontFile = $fontFile;
        if (iconv_strlen($taskParams['text']) > 300) {
            throw new \RuntimeException("Слишком длинный текст");
        }
        $this->text = $taskParams['text'];
        $this->bgColor = $taskParams['bg_color'];
        $this->fontColor = $taskParams['font_color'];
        $this->speed = $taskParams['speed'];
        $this->userId = $taskParams['user_id'];
    }
    public function calcWidth($text)
    {
        $image = new Imagick();
        $draw = new ImagickDraw();
        $draw->setFontSize(self::FONT_SIZE);
        $draw->setFont($this->fontFile);
        $fontMetrics = $image->queryFontMetrics($draw, $text);
        return $fontMetrics['textWidth'];
    }

    public function process(): string
    {
        $newLines = substr_count($this->text, "\n");

        $animation = new Imagick();
        $animation->setFormat("gif");

        $formatedTextArray = $this->splitText($this->text, $this->fontFile);
        $formatedText = implode("", $formatedTextArray);
        $textLength = mb_strlen($formatedText);

        for ($lastSymbol = 1; $lastSymbol <= $textLength; $lastSymbol++) {
            $image = new Imagick();
            $draw = new ImagickDraw();
            $draw->setFillColor(new ImagickPixel($this->fontColor));
            $draw->setFontSize(self::FONT_SIZE);
            $draw->setFont($this->fontFile);
            $textToImage = mb_substr($formatedText, 0, $lastSymbol);
            $image::setResourceLimit(6, 1);

            $image->newImage(self::GIF_WIDTH, self::GIF_ROW_HEIGHT * (count($formatedTextArray) + $newLines), new ImagickPixel($this->bgColor));

            $image->annotateImage($draw, self::TEXT_COORD_X, self::TEXT_COORD_Y, 0, $textToImage);
            $image->setImageFormat('png');
            $animation->addImage($image);
            $animation->nextImage();
            $animation->setImageDelay(100 / $this->speed);
            $image->clear();
        }
        $animation->setImageDelay(300);

        $gifFile = "/tmp/{$this->userId}.gif";
        $animation->writeImages($gifFile, true);
        $animation->clear();
        return $gifFile;
    }

    private function splitText(string $text, string $font): array
    {
        $words = explode(' ', $text);
        $row = '';
        $totalText = [];

        // Пока не кончатся слова в тексте
        while (count($words) !== 0) {
            // Убираем одно слово из начала текста
            $word = array_shift($words);

            $strWidth = $this->calcWidth("{$row}{$word} ");
            if ($strWidth > self::GIF_WIDTH) {
                // Если ширина строки + слово > 690
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
}