<?php


namespace Test;


use Bot\Config;
use Bot\DB;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private static $userId = 132763295;
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        self::$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    }

    public function testGetConfig(): Config
    {
        $userConfig = Config::get(self::$userId, self::$db->getConfigByUserId(self::$userId));
        self::assertNotFalse($userConfig);
        return $userConfig;
    }

    /**
     * @depends testGetConfig
     * @param Config $userConfig
     */
    public function testSave(Config $userConfig): void
    {
        $userConfig->setFontColor("#C39F40");
        $userConfig->setBgColor("#FFD23D");
        $userConfig->setSpeed(10);
        self::assertTrue(self::$db->saveConfig($userConfig));
        Config::deleteInstance();
        $userConfig = Config::get(self::$userId, self::$db->getConfigByUserId(self::$userId));
        self::assertEquals(10, $userConfig->getSpeed());
        self::assertEquals("#C39F40", $userConfig->getFontColor());
        self::assertEquals("#FFD23D", $userConfig->getBgColor());
    }

    /**
     * @depends testGetConfig
     * @param Config $userConfig
     */
    public function testSaveWrongData(Config $userConfig): void
    {
        $userConfig->setBgColor('WRONG_FORMAT');
        $userConfig->setFontColor('WRONG_FORMAT');
        $userConfig->setSpeed(0);

        self::assertTrue($userConfig->hasErrors());
        self::assertCount(3, $userConfig->getErrors());
    }
}