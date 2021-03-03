<?php


namespace Test;


use Bot\model\Task;
use Bot\Registry;
use PDO;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private static $userId = 132763295;

    public static function setUpBeforeClass(): void
    {
        $registry = Registry::getInstance();
        $registry->pdo = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $registry->pdo->exec("DELETE FROM task");
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
        $task->setStatus(Task::STATUS_DONE);
        $task->update();

        $task = Task::find()->byId($taskId);
        self::assertEquals(Task::STATUS_DONE, $task->getStatus());
    }

    /**
     * @depends testUpdateTaskStatus
     */
    public function testFindAll(): void
    {
        $tasks = Task::find()->where(['status' => Task::STATUS_DONE])->all();
        self::assertInstanceOf(Task::class, $tasks->current());
        foreach ($tasks as $task) {
            self::assertInstanceOf(Task::class, $task);
        }
    }

    public function testFindByStatus(): void
    {
        $task = new Task(-1, 'test find by status', self::$userId);
        $task->insert();
        $taskFromDb = Task::find()->where(['status' => Task::STATUS_NEW])->one();
        self::assertInstanceOf(Task::class, $taskFromDb);
    }
}