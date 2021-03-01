<?php


namespace Test;


use Bot\model\Task;
use Bot\Registry;
use PHPUnit\Framework\TestCase;

class TestTask extends TestCase
{
    private static $userId = 132763295;

    public static function setUpBeforeClass(): void
    {
        $pdo = new \PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $registry = Registry::getInstance();
        $registry->pdo = $pdo;
        $pdo->query("DELETE FROM task");
    }

    public function testCreateNewTask(): int
    {
        $task = new Task(-1, "test create new task", self::$userId);
        $task->insert();
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
        $task = Task::find()->byId($taskId);
        self::assertInstanceOf(Task::class, $task);
        self::assertEquals($taskId, $task->getId());
    }

    /**
     * @depends testCreateNewTask
     * @param $taskId
     */
    public function testUpdateTaskStatus($taskId): void
    {

        $task = Task::find()->byId($taskId);
        $task->setStatus("done");
        $task->update();

        $task = Task::find()->byId($taskId);
        self::assertEquals("done", $task->getStatus());
    }

    /**
     * @depends testUpdateTaskStatus
     */
    public function testFindAll()
    {
        $tasks = Task::find()->where(['status' => 'done'])->all();
        self::assertInstanceOf(Task::class, $tasks->current());
        foreach ($tasks as $task) {
            self::assertInstanceOf(Task::class, $task);
        }
    }
}