<?php

namespace Bot\model;


use Bot\model\Base\DomainObject;
use Bot\model\Base\Mapper;
use Bot\model\Mapper\ConfigMapper;

class Config extends Base\DomainObject
{
    private ?int $speed = 5;
    private ?string $bgColor = '#000000';
    private ?string $fontColor = '#FFFFFF';
    private array $errors = [];

    /**
     * Config constructor.
     * @param integer $id
     * @param null|integer $speed
     * @param null|string $bgColor
     * @param null|string $fontColor
     */
    public function __construct(int $id, $speed = null, $bgColor = null, $fontColor = null)
    {
        parent::__construct($id);
        $this->speed = $speed ?? $this->speed;
        $this->bgColor = $bgColor ?? $this->bgColor;
        $this->fontColor = $fontColor ?? $this->fontColor;
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
     * Возвращает массив ошибок
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    private function colorInHexFormat($hexColor): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $hexColor) === 1;
    }

    protected static function getMapper(): Mapper
    {
        return new ConfigMapper();
    }

    public static function find(): Mapper
    {
        return self::getMapper();
    }

    public static function findOrCreateDefault($id): Config
    {
        $mapper = self::getMapper();
        $config = $mapper->byId($id);
        if (is_null($config)) {
            $config = new self($id);
            $config->insert();
        }
        return $config;
    }

    public function insert(): DomainObject
    {
        if ($this->hasErrors()) {
            throw new \RuntimeException(implode("\n", $this->getErrors()));
        }
        $mapper = self::getMapper();
        $mapper->insert($this);
        return $this;
    }

    public function update(): DomainObject
    {
        if ($this->hasErrors()) {
            throw new \RuntimeException(implode("\n", $this->getErrors()));
        }
        $mapper = self::getMapper();
        $mapper->update($this);
        return $this;
    }
}