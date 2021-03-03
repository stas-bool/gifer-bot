<?php


namespace Test;


use Bot\model\Config;
use Bot\Registry;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConfigTest extends TestCase
{
    private static int $userId = 132763295;

    public static function setUpBeforeClass(): void
    {
        $pdo = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $registry = Registry::getInstance();
        $registry->pdo = $pdo;
        $pdo->exec("DELETE FROM task");
        $pdo->exec("DELETE FROM config");
    }

    public function testCreateConfig(): Config
    {
        $userConfig = new Config(self::$userId);
        $userConfig->insert();
        self::assertNotNull($userConfig->getSpeed());
        self::assertNotNull($userConfig->getFontColor());
        self::assertNotNull($userConfig->getBgColor());
        return $userConfig;
    }

    /**
     * @depends testCreateConfig
     */
    public function testGetConfig(): void
    {
        $userConfig = Config::find()->byId(self::$userId);
        self::assertInstanceOf(Config::class, $userConfig);
    }

    /**
     * @depends testCreateConfig
     */
    public function testSaveWrongData(): void
    {
        $userConfig = Config::find()->byId(self::$userId);
        $userConfig->setBgColor('WRONG_FORMAT');
        $userConfig->setFontColor('WRONG_FORMAT');
        $userConfig->setSpeed(0);

        self::assertTrue($userConfig->hasErrors());
        self::assertCount(3, $userConfig->getErrors());
        $this->expectException(RuntimeException::class);
        $userConfig->update();
    }

    public function testFindOrCreate(): void
    {
        $userId = 666;
        Config::findOrCreateDefault($userId);
        $configFromDb = Config::find()->byId($userId);
        self::assertInstanceOf(Config::class, $configFromDb);
    }

    public function testUpdateConfig(): void
    {
        $userId = 13;
        $config = Config::findOrCreateDefault($userId);
        $newSpeed = 10;
        $newFontColor = "#FFFFFF";
        $newBgColor = "#000000";
        $config->setSpeed($newSpeed)->setFontColor($newFontColor)->setBgColor($newBgColor);
        $config->update();
        $configFromDb = Config::find()->byId($userId);
        self::assertEquals($newSpeed, $configFromDb->getSpeed());
        self::assertEquals($newFontColor, $configFromDb->getFontColor());
        self::assertEquals($newBgColor, $configFromDb->getBgColor());
    }
}