<?php

require_once __DIR__.'/DBConnect.php';
class Config
{
    private $userId;
    private $speed;
    private $bgColor;
    private $fontColor;
    private static $instance;

    private function __construct($userId, array $config)
    {
        $this->userId = $userId;
        $this->speed = $config['speed'];
        $this->bgColor = $config['bg_color'];
        $this->fontColor = $config['font_color'];
    }

    public function getBgColor()
    {
        return $this->bgColor;
    }

    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;
        return $this;
    }

    public function getFontColor()
    {
        return $this->fontColor;
    }

    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;
        return $this;
    }

    public function getSpeed()
    {
        return $this->speed;
    }

    public function setSpeed($speed)
    {
        $this->speed = $speed;
        return $this;
    }

    public static function load($userId)
    {
        $db = new DBConnect();
        $config = $db->getConfigByUserId($userId);
        if (!$config) {
            $config = [
                'speed' => 3,
                'bg_color' => '#FFEBEB',
                'font_color' => 'black'
            ];
            $db->createNew($userId, json_encode($config));
        }
        if (is_null(self::$instance)) {
            self::$instance = new Config($userId, $config);
        }
        return self::$instance;
    }

    public function save()
    {
        $config = [
            'speed' => $this->speed,
            'bg_color' => $this->bgColor,
            'font_color' => $this->fontColor,
        ];
        $db = new DBConnect();
        $result = $db->saveConfig($this->userId, json_encode($config));
    }
}