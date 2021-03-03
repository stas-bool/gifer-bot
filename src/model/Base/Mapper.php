<?php


namespace Bot\model\Base;


use Bot\Registry;
use Generator;
use PDO;
use PDOStatement;

abstract class Mapper
{
    protected PDO $pdo;
    protected string $selectSql;
    protected PDOStatement $updateStmt;
    protected PDOStatement $insertStmt;
    protected PDOStatement $selectStmt;

    public function __construct()
    {
        $registry = Registry::getInstance();
        if (!is_null($registry)) {
            $this->pdo = $registry->pdo;
        }
    }

    public function where($params): Mapper
    {
        $this->selectSql .= " WHERE ";
        array_walk($params, static function (&$value, $column) {
            if (is_int($value)) {
                $value = "$column = $value";
            } else {
                $value = "$column = '$value'";
            }
        });
        $this->selectSql .= implode(" AND ", $params);
        $this->selectStmt = $this->pdo->prepare($this->selectSql);
        return $this;
    }

    public function byId($id): ?DomainObject
    {
        $this->selectStmt = $this->pdo->prepare($this->selectSql . " WHERE id = :id");
        $this->selectStmt()->execute([':id' => $id]);
        $row = $this->selectStmt()->fetch(PDO::FETCH_ASSOC);
        $this->selectStmt()->closeCursor();

        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    public function one(): ?DomainObject
    {
        $row = $this->selectStmt()->fetch(PDO::FETCH_ASSOC);
        $this->selectStmt()->closeCursor();
        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    public function all(): Generator
    {
        $this->selectStmt()->execute();
        while ($row = $this->selectStmt()->fetch(PDO::FETCH_ASSOC)) {
            yield $this->createObject($row);
        }
    }

    public function createObject($row): DomainObject
    {
        $obj = $this->doCreateObject($row);
        return $obj;
    }

    public function insert(DomainObject $object): void
    {
        $this->doInsert($object);
    }

    protected function selectStmt(): PDOStatement
    {
        return $this->selectStmt;
    }

    abstract public function update(DomainObject $object): bool;
    abstract protected function doCreateObject(array $raw): DomainObject;
    abstract protected function doInsert(DomainObject $object): void;
    abstract protected function targetClass(): string;
}