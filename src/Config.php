<?php

namespace Bot;

class Config
{
    private $userId;
    private $speed;
    private $bgColor;
    private $fontColor;
    private $dbConfig;
    private static $instance;

    private function __construct($userId, array $config, $dbConfig)
    {
        $this->userId = $userId;
        $this->speed = $config['speed'];
        $this->bgColor = $config['bg_color'];
        $this->fontColor = $config['font_color'];
        $this->dbConfig = $dbConfig;
    }

    public function getUserId()
    {
        return $this->userId;
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

    public static function load(int $userId, array $dbConfig)
    {
        $db = DBConnect::connect($dbConfig);
        $config = $db->getConfigByUserId($userId);
        if (!$config) {
            $config = [
                'speed' => 5,
                'bg_color' => '#FFEBEB',
                'font_color' => 'black'
            ];
            $db->newUserConfig($userId, json_encode($config));
        }
        if (is_null(self::$instance)) {
            self::$instance = new Config($userId, $config, $dbConfig);
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
        DBConnect::connect($this->dbConfig)->saveConfig($this->userId, json_encode($config));
        return $this;
    }
}