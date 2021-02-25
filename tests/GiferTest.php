<?php


namespace Test;


use Bot\Config;
use Bot\DB;
use Bot\Gifer;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class GiferTest extends TestCase
{
    private static $db;
    private static $userId = 132763295;
    public static function setUpBeforeClass(): void
    {
        self::$db = DB::connect($_ENV['DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    }

    public function testCreateGifFile(): void
    {
        $testText = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        self::$db->newTask(self::$userId, $testText);
        $task = self::$db->getNewTask();
        $font = __DIR__ . '/../fonts/NotoSans-Regular.ttf';

        $gifFilePath = (new Gifer($font, $task))->process();
        self::assertFileExists($gifFilePath);
        unlink($gifFilePath);
        self::$db->setTaskDone($task['id']);
    }
}