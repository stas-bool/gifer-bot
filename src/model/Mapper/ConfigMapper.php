<?php


namespace Bot\model\Mapper;


use Bot\model\Base\DomainObject;
use Bot\model\Base\Mapper;
use Bot\model\Config;

class ConfigMapper extends Mapper
{
    public function __construct()
    {
        parent::__construct();
        $this->updateStmt = $this->pdo->prepare(
            "UPDATE config SET speed=:speed, bg_color=:bgColor, font_color=:fontColor WHERE id=:id"
        );
        $this->insertStmt = $this->pdo->prepare(
            "INSERT INTO config (id, speed, bg_color, font_color) 
VALUES (:id, :speed, :bgColor, :fontColor)"
        );
        $this->selectSql = "SELECT * FROM config";
        $this->selectStmt = $this->pdo->prepare("SELECT FROM config");
    }

    public function update(DomainObject $object): bool
    {
        $values = [
            ":speed" => $object->getSpeed(),
            ":bgColor" => $object->getBgColor(),
            ":fontColor" => $object->getFontColor(),
            ":id" => $object->getId(),
        ];
        return $this->updateStmt->execute($values);
    }

    protected function doCreateObject(array $raw): DomainObject
    {
        return new Config($raw['id'], $raw['speed'], $raw['bg_color'], $raw['font_color']);
    }

    protected function doInsert(DomainObject $object): void
    {
        $values = [
            ":id" => $object->getId(),
            ":speed" => $object->getSpeed(),
            ":bgColor" => $object->getBgColor(),
            ":fontColor" => $object->getFontColor(),
        ];
        $this->insertStmt->execute($values);
    }

    protected function targetClass(): string
    {
        return Config::class;
    }
}