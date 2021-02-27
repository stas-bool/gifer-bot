<?php


namespace Test;


use Bot\model\Mapper\TaskMapper;
use Bot\model\Task;
use PHPUnit\Framework\TestCase;

class TestTask extends TestCase
{
    private static $userId = 132763295;
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        self::$pdo->query("DELETE FROM task");
    }

    public function testCreateNewTask(): int
    {
        $mapper = new TaskMapper(self::$pdo);
        $task = new Task(-1, "test mapper", self::$userId);
        $mapper->insert($task);
        self::assertIsInt($task->getId());
        self::assertNotEquals(-1, $task->getId());
        return $task->getId();
    }

    /**
     * @depends testCreateNewTask
     * @param $taskId
     */
    public function testGetTask($taskId): void
    {
        $mapper = new TaskMapper(self::$pdo);
        $task = $mapper->find($taskId);
        self::assertInstanceOf(Task::class, $task);
        self::assertEquals($taskId, $task->getId());
    }

    /**
     * @depends testCreateNewTask
     * @param $taskId
     */
    public function testUpdateTaskStatus($taskId): void
    {

        $mapper = new TaskMapper(self::$pdo);
        $task = $mapper->find($taskId);
        $task->setStatus("done");
        $mapper->update($task);

        $task = $mapper->find($taskId);
        self::assertEquals("done", $task->getStatus());
    }
}