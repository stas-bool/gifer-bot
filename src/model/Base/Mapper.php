<?php


namespace Bot\model\Base;


use PDO;
use PDOStatement;

abstract class Mapper
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id)
    {
        $this->selectStmt()->execute([':id' => $id]);
        $row = $this->selectStmt()->fetch(PDO::FETCH_ASSOC);
        $this->selectStmt()->closeCursor();

        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    public function createObject($row): DomainObject
    {
        $obj = $this->doCreateObject($row);
        return $obj;
    }

    public function insert(DomainObject $object)
    {
        $this->doInsert($object);
    }

    abstract public function update(DomainObject $object);
    abstract protected function doCreateObject(array $raw): DomainObject;
    abstract protected function doInsert(DomainObject $object);
    abstract protected function selectStmt(): PDOStatement;
    abstract protected function targetClass(): string;
}