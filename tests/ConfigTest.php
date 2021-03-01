<?php


namespace Test;


use Bot\model\Config;
use Bot\Registry;
use PDO;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private static int $userId = 132763295;

    public static function setUpBeforeClass(): void
    {
        $pdo = new PDO($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $registry = Registry::getInstance();
        $registry->pdo = $pdo;
        $pdo->exec("DELETE FROM task");
        $pdo->exec("DELETE FROM user_config");
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
    }
}