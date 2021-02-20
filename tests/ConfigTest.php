<?php


namespace Test;


use Bot\Config;
use Bot\DBConnect;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $userId = 132763295;
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        $testConfig = json_decode(file_get_contents(__DIR__ . '/../test-config.json'), true);
        self::$db = DBConnect::connect($testConfig['database']);
        self::$db->query('DROP TABLE IF EXISTS tasks');
        self::$db->query('DROP TABLE IF EXISTS configs');
        self::$db->createTables();
    }

    public function testLoad()
    {
        $userConfig = Config::load($this->userId, self::$db);
        $this->assertInstanceOf(Config::class, $userConfig);
        return $userConfig;
    }

    /**
     * @depends testLoad
     * @param Config $userConfig
     */
    public function testSetAndGet($userConfig)
    {
        $userConfig->setFontColor("#C39F40");
        $this->assertEquals("#C39F40", $userConfig->getFontColor());

        $userConfig->setBgColor("#FFD23D");
        $this->assertEquals("#FFD23D", $userConfig->getBgColor());

        $userConfig->setSpeed(10);
        $this->assertEquals(10, $userConfig->getSpeed());

        $this->assertEquals(132763295, $userConfig->getUserId());
        $userConfig->save();
    }
}