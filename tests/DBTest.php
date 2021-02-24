<?php


namespace Test;


use Bot\Config;
use Bot\DB;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    private static $userId = 132763295;
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/../', '.env.test');
        $dotenv->load();
    }

    public function testConnect(): void
    {
        self::$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        self::assertInstanceOf(DB::class, self::$db);
    }

    public function testNewUserConfig(): void
    {
        $dbConfig = self::$db->getConfigByUserId(self::$userId);
        $config = Config::get(self::$userId, $dbConfig);
        self::assertTrue(self::$db->saveConfig($config), 'Не удалось создать конфигурацию пользователя');
    }

    public function testNewTask(): int
    {
        $configFromDb = self::$db->getConfigByUserId(self::$userId);
        $userConfig = Config::get(self::$userId, $configFromDb);
        $newTaskId = self::$db->newTask($userConfig->getUserId(), 'test text');
        self::assertIsInt($newTaskId);
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
        self::assertIsArray($task);
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
        self::assertNull(self::$db->getNewTask());
    }

    public function testSaveWrongData()
    {
        $config = Config::get(self::$userId, self::$db->getConfigByUserId(self::$userId));
        $config->setBgColor('WRONG_FORMAT');
        $config->setFontColor('WRONG_FORMAT');
        $config->setSpeed('WRONG_FORMAT');
        self::assertFalse(self::$db->saveConfig($config));
    }

    public function testTooLongText()
    {
        $text = $this->generateRandomString();
        $configFromDb = self::$db->getConfigByUserId(self::$userId);
        $userConfig = Config::get(self::$userId, $configFromDb);

        $this->expectException(\RuntimeException::class);
        self::$db->newTask($userConfig->getUserId(), $text);
    }

    private function generateRandomString($length = 301)
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