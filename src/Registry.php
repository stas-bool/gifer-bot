<?php


namespace Bot;


use PDO;

/**
 * Class Registry
 * @package Bot
 * @property PDO $pdo
 */
class Registry
{
    private static $instance;
    private $values = [];

    public function __get($name)
    {
        return $this->values[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }

    public function __isset($name): bool
    {
        return array_key_exists($name, $this->values);
    }

    private function __construct() {}

    /**
     * @return Registry
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}