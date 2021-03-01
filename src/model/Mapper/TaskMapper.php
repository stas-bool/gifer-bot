<?php


namespace Bot\model\Mapper;


use Bot\model\Base\DomainObject;
use Bot\model\Base\Mapper;
use Bot\model\Task;
use Bot\Registry;

class TaskMapper extends Mapper
{
    private $updateStmt;
    private $insertStmt;

    public function __construct()
    {
        parent::__construct(self::getTableName());
        $this->pdo = Registry::getInstance()->pdo;
        $this->updateStmt = $this->pdo->prepare(
            "UPDATE task set text=:text, status=:status WHERE id=:id"
        );
        $this->insertStmt = $this->pdo->prepare(
            "INSERT INTO task (text, status, config) VALUES (:text, :status, :config)"
        );
    }

    protected function targetClass(): string
    {
        return Task::class;
    }

    protected function doCreateObject(array $raw): DomainObject
    {
        return new Task($raw['id'], $raw['text'], $raw['config'], $raw['status']);
    }

    protected function doInsert(DomainObject $object)
    {
        $values = [
            ':text' => $object->getText(),
            ':status' => $object->getStatus(),
            ':config' => $object->getConfig()
        ];
        $this->insertStmt->execute($values);
        $object->setId($this->pdo->lastInsertId());
    }

    public function update(DomainObject $object): bool
    {
        $values = [
            ':text' => $object->getText(),
            ':status' => $object->getStatus(),
            ':id' => $object->getId()
        ];
        return $this->updateStmt->execute($values);
    }

    protected static function getTableName(): string
    {
        return "task";
    }
}