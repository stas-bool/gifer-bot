<?php


namespace Test;


use Bot\DB;
use Bot\model\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DBTest extends TestCase
{
    private static $userId = 132763295;
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        self::$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        self::$db->query("DELETE FROM task");
        self::$db->query("DELETE FROM user_config");
    }

    public function testConnect(): void
    {
        self::assertInstanceOf(DB::class, self::$db);
    }

    /**
     * @depends testConnect
     */
    public function testNewUserConfig(): void
    {
        $dbConfig = self::$db->getConfigByUserId(self::$userId);
        self::assertFalse($dbConfig);

        $config = Config::getInstance(self::$userId, $dbConfig);
        self::assertTrue(self::$db->saveConfig($config), 'Не удалось создать конфигурацию пользователя');
    }

    /**
     * @depends testConnect
     * @depends testNewUserConfig
     * @return int
     */
    public function testNewTask(): int
    {
        $configFromDb = self::$db->getConfigByUserId(self::$userId);
        $userConfig = Config::getInstance(self::$userId, $configFromDb);
        $newTaskId = self::$db->newTask($userConfig->getId(), 'test text');
        self::assertIsInt($newTaskId, "Не удалось создать задание");
        return $newTaskId;
    }

    /**
     * @depends testNewTask
     * @param int $taskId
     * @return integer
     */
    public function testGetTask(int $taskId): int
    {
        $task = self::$db->getNewTask();
        self::assertIsArray($task, "Не удалось получить новое задание");
        self::assertEquals($taskId, $task['id']);
        return $task['id'];
    }

    /**
     * @depends testGetTask
     * @param int $taskId
     */
    public function testSetTaskDone(int $taskId): void
    {
        self::$db->setTaskDone($taskId);
        self::assertNull(self::$db->getNewTask(), "Не удалось отметить задание выполненным");
    }

    /**
     * @depends testConnect
     * @depends testNewUserConfig
     */
    public function testSaveWrongData(): void
    {
        $config = Config::getInstance(self::$userId, self::$db->getConfigByUserId(self::$userId));
        $config->setBgColor('WRONG_FORMAT');
        $config->setFontColor('WRONG_FORMAT');
        $config->setSpeed('WRONG_FORMAT');
        self::assertFalse(self::$db->saveConfig($config));
    }

    /**
     * @depends testConnect
     * @depends testNewUserConfig
     */
    public function testTooLongText(): void
    {
        $text = $this->generateRandomString();
        $configFromDb = self::$db->getConfigByUserId(self::$userId);
        $userConfig = Config::getInstance(self::$userId, $configFromDb);

        $this->expectException(RuntimeException::class);
        self::$db->newTask($userConfig->getId(), $text);
    }

    private function generateRandomString($length = 301): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}