<?php

namespace Bot;

use PDO;

class DBConnect
{
    /**
     * @var PDO
     */
    private $db;

    private function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public static function connect($dbconfig): DBConnect
    {
        $db = new PDO("pgsql:dbname={$dbconfig['dbname']};host={$dbconfig['host']}", $dbconfig['username'], $dbconfig['password']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return new self($db);
    }

    public function createTables()
    {
        $createConfigsTable = "CREATE TABLE IF NOT EXISTS configs (user_id INTEGER UNIQUE, config TEXT)";
        $createTasksTable = "CREATE TABLE IF NOT EXISTS tasks (id SERIAL, user_id INTEGER NOT NULL, bg_color VARCHAR (10), font_color VARCHAR (10), speed SMALLINT, text TEXT, status VARCHAR (10))";
        $createConfigsTableResult = $this->db->query($createConfigsTable);
        $createTasksTableResult = $this->db->query($createTasksTable);
        return $createConfigsTableResult && $createTasksTableResult;
    }

    /**
     * @param $userId
     * @return null|array
     */
    public function getConfigByUserId($userId): ?array
    {
        $statement = $this->db->prepare("SELECT config FROM configs WHERE user_id=:userId LIMIT 1");
        $statement->execute([':userId' => $userId]);
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        if ($config) {
            return json_decode($config['config'], true);
        } else {
            return null;
        }
    }

    public function saveConfig($userId, array $config)
    {
        $statement = $this->db->prepare(
            'INSERT INTO configs (user_id, config)
                VALUES (:userId, :config) 
                ON CONFLICT (user_id) 
                DO UPDATE SET config=:config'
        );
        return $statement->execute([':config' => json_encode($config), ':userId' => $userId]);
    }

    /**
     * @param Config $userConfig
     * @param string $text
     * @return string
     */
    public function newTask(Config $userConfig, string $text): string
    {
        $sql = 'INSERT INTO tasks (user_id, bg_color, font_color, speed, text, status)
            VALUES (:userId, :bgColor, :fontColor, :speed, :text, :status)';
        $statement = $this->db->prepare($sql);
        $values = [
            ':userId' => $userConfig->getUserId(),
            ':bgColor' => $userConfig->getBgColor(),
            ':fontColor' => $userConfig->getFontColor(),
            ':speed' => $userConfig->getSpeed(),
            ':text' => $text,
            ':status' => 'new',
        ];
        $statement->execute($values);
        return $this->db->lastInsertId();
    }

    public function getTask(): ?array
    {
        $this->db->exec("BEGIN");
        $sql = "SELECT * FROM tasks WHERE status = 'new' LIMIT 1 FOR UPDATE";
        $result = $this->db->query($sql);
        $task = $result->fetch(PDO::FETCH_ASSOC);
        if ($task) {
            $statement = $this->db->prepare("UPDATE tasks SET status = 'in_process' WHERE id=:taskId");
            $statement->execute([':taskId' => $task['id']]);
            $this->db->exec("COMMIT");
            return $task;
        }
        $this->db->exec("COMMIT");
        return null;
    }

    public function setTaskDone($taskId)
    {
        $statement = $this->db->prepare("UPDATE tasks SET status = 'done' WHERE id=:taskId");
        $result = $statement->execute([':taskId' => $taskId]);
        $this->db->query("COMMIT");
        return $result;
    }

    public function query(string $sql)
    {
        return $this->db->query($sql);
    }
}
