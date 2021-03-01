<?php


namespace Bot\model;


use Bot\model\Base\Mapper;
use Bot\model\Mapper\TaskMapper;

class Task extends Base\DomainObject
{
    private $text;
    private $status;
    private $config;

    public function __construct($id, $text, $config, $status = 'new')
    {
        parent::__construct($id);
        $this->text = $text;
        $this->config = $config;
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Task
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

    public static function getMapper(): Mapper
    {
        return new TaskMapper();
    }

    public function getTableName(): string
    {
        return "task";
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