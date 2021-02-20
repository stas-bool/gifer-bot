<?php


namespace Test;


use Bot\Config;
use Bot\DBConnect;
use PHPUnit\Framework\TestCase;

class DBConnectTest extends TestCase
{
    private $userId = 132763295;
    private $userConfig = [
        'speed' => 5,
        'bg_color' => '#FFFFFF',
        'font_color' => '#000000'
    ];
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        $testConfig = json_decode(file_get_contents(__DIR__ . '/../test-config.json'), true);
        self::$db = DBConnect::connect($testConfig['database']);
        self::assertInstanceOf(DBConnect::class, self::$db);
        self::$db->query('DROP TABLE IF EXISTS tasks');
        self::$db->query('DROP TABLE IF EXISTS configs');
        self::assertTrue(self::$db->createTables());
    }

    public function testConnect()
    {
        $this->assertInstanceOf(DBConnect::class, self::$db);
    }

    public function testNewUserConfig()
    {
        $this->assertTrue(
            self::$db->saveConfig($this->userId, $this->userConfig),
            'Не удалось создать конфигурацию пользователя'
        );
    }

    public function testGetConfigByUserId()
    {
        $userConfigFromDb = self::$db->getConfigByUserId($this->userId);
        $this->assertEquals($this->userConfig, $userConfigFromDb);
        $notExistUserId = 0;
        $this->assertNull(self::$db->getConfigByUserId($notExistUserId));
    }

    public function testNewTask()
    {
        $userConfig = Config::load($this->userId, self::$db);
        $newTaskId = self::$db->newTask($userConfig, 'test text');
        $this->assertIsString($newTaskId);
        return $newTaskId;
    }

    /**
     * @depends testNewTask
     * @param string $newTaskId
     * @return integer
     */
    public function testGetTask($newTaskId)
    {
        $task = self::$db->getTask();
        $this->assertIsArray($task);
        $this->assertEquals((int) $newTaskId, $task['id']);
        return $task['id'];
    }

    /**
     * @depends testGetTask
     * @param string $taskId
     */
    public function testSetTaskDone($taskId)
    {
        self::$db->setTaskDone($taskId);
        $this->assertNull(self::$db->getTask());
    }
}