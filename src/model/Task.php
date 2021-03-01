<?php


namespace Bot\model;


use Bot\model\Base\Mapper;
use Bot\model\Mapper\TaskMapper;

class Task extends Base\DomainObject
{
    public const STATUS_NEW = 0;
    public const STATUS_DONE = 1;
    private string $text;
    private int $status;
    private int $config;

    public function __construct($id, $text, $config, $status = self::STATUS_NEW)
    {
        parent::__construct($id);
        $this->text = $text;
        $this->config = $config;
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): Task
    {
        $this->status = $status;
        return $this;
    }

    public function setText($text): Task
    {
        $this->text = $text;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getConfig(): int
    {
        return $this->config;
    }

    protected static function getMapper(): Mapper
    {
        return new TaskMapper();
    }

    public static function find(): Mapper
    {
        return self::getMapper();
    }

    public function insert(): Task
    {
        $mapper = self::getMapper();
        $mapper->insert($this);
        return $this;
    }

    public function update(): Task
    {
        $mapper = self::getMapper();
        $mapper->update($this);
        return $this;
    }
}