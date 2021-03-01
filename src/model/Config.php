<?php

namespace Bot\model;


use Bot\model\Base\DomainObject;
use Bot\model\Base\Mapper;

class Config extends Base\DomainObject
{
    private $speed = 5;
    private $bgColor = '#000000';
    private $fontColor = '#FFFFFF';
    private $errors = [];
    private static $instance;

    /**
     * Config constructor.
     * @param integer $id
     * @param null|false|array $rawConfig
     */
    private function __construct(int $id, $rawConfig = null)
    {
        parent::__construct($id);
        if ($rawConfig !== false && !is_null($rawConfig)) {
            $this->speed = $rawConfig['speed'];
            $this->bgColor = $rawConfig['bg_color'];
            $this->fontColor = $rawConfig['font_color'];
        }
    }

    /**
     * @param int $userId
     * @param null|false|array $rawConfig
     * @return Config
     */
    public static function getInstance(int $userId, $rawConfig = null): Config
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($userId, $rawConfig);
        }
        return self::$instance;
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
     * Возвращает массив ошибок и очищает их
     * @return array
     */
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

    protected function getTableName(): string
    {
        return 'config';
    }

    public static function getMapper(): Mapper
    {
        // TODO: Implement getMapper() method.
    }

    public static function find(): Mapper
    {
        // TODO: Implement find() method.
    }

    public function insert(): DomainObject
    {
        // TODO: Implement insert() method.
    }

    public function update(): DomainObject
    {
        // TODO: Implement update() method.
    }
}