<?php


namespace Bot\model\Base;


abstract class DomainObject
{
    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    abstract protected static function getMapper(): Mapper;
    abstract public static function find(): Mapper;
    abstract public function insert(): DomainObject;
    abstract public function update(): DomainObject;
}