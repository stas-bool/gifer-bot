<?php

namespace Bot;


class Config
{
    private $userId;
    private $speed = 5;
    private $bgColor = '#000000';
    private $fontColor = '#FFFFFF';
    private $errors = [];
    private static $instance;

    /**
     * Config constructor.
     * @param integer $userId
     * @param null|false|array $config
     */
    private function __construct(int $userId, $config = null)
    {
        $this->userId = $userId;
        if ($config !== false && !is_null($config)) {
            $this->speed = $config['speed'];
            $this->bgColor = $config['bg_color'];
            $this->fontColor = $config['font_color'];
        }
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBgColor(): string
    {
        return $this->bgColor;
    }

    public function setBgColor($bgColor): Config
    {
        if ($this->colorInHexFormat($bgColor)) {
            $this->bgColor = $bgColor;
        } else {
            $this->errors[] = 'Неверный формат цвета. Должен быть в hex формате.
Пример: /set_bg_color #000000';
        }
        return $this;
    }

    public function getFontColor(): string
    {
        return $this->fontColor;
    }

    public function setFontColor($fontColor): Config
    {
        if ($this->colorInHexFormat($fontColor)) {
            $this->fontColor = $fontColor;
        } else {
            $this->errors[] = 'Неверный формат цвета. Должен быть в hex формате.
Пример: /set_font_color #FFFFFF';
        }
        return $this;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function setSpeed($speed): Config
    {
        if (is_int($speed) && $speed >= 1 && $speed <= 10) {
            $this->speed = $speed;
        } elseif ($speed === 'default') {
            $this->speed = 5;
        } else {
            $this->errors[] = 'Неверный формат скорости. Скорость может быть от 1 до 10 или "default".
Пример: /set_speed 5';
        }
        return $this;
    }

    /**
     * @param int $userId
     * @param null|false|array $config
     * @return Config
     */
    public static function get(int $userId, $config = null): Config
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config($userId, $config);
        }
        return self::$instance;
    }

    public function getErrors(): array
    {
        $errors = $this->errors;
        $this->errors = [];
        return $errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    private function colorInHexFormat($hexColor): bool
    {
        return preg_match('/^#[0-9A-F]{6}$/', $hexColor) === 1;
    }

    public static function deleteInstance(): bool
    {
        self::$instance = null;
        return true;
    }
}