<?php

namespace Bot;

class Config
{
    private $userId;
    private $speed;
    private $bgColor;
    private $fontColor;
    private $db;
    private static $instance;

    private function __construct($userId, array $config, DBConnect $db)
    {
        $this->userId = $userId;
        $this->speed = $config['speed'];
        $this->bgColor = $config['bg_color'];
        $this->fontColor = $config['font_color'];
        $this->db = $db;
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

    public static function load(int $userId, DBConnect $db)
    {
        $config = $db->getConfigByUserId($userId);
        if (is_null($config)) {
            $config = [
                'speed' => 5,
                'bg_color' => 'white',
                'font_color' => 'black'
            ];
            $db->saveConfig($userId, $config);
        }
        if (is_null(self::$instance)) {
            self::$instance = new Config($userId, $config, $db);
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
        $this->db->saveConfig($this->userId, $config);
        return $this;
    }
}