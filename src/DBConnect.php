<?php

class DBConnect
{
    /**
     * @var PDO
     */
    private $db;

    private function __construct($db)
    {
        $this->db = $db;
    }

    public static function connect()
    {
        $db = new PDO("pgsql:dbname=gifer;host=localhost", 'gifer', '01091986');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $createConfigsTable = "CREATE TABLE IF NOT EXISTS configs (user_id INTEGER UNIQUE, config TEXT)";
        $db->query($createConfigsTable);
        $createTasksTable = "CREATE TABLE IF NOT EXISTS tasks (id SERIAL, user_id INTEGER NOT NULL, bg_color VARCHAR (10), font_color VARCHAR (10), speed SMALLINT, text TEXT, status VARCHAR (10))";
        $db->query($createTasksTable);
        return new DBConnect($db);
    }

    public function getConfigByUserId($userId)
    {
        $statement = $this->db->prepare("SELECT config FROM configs WHERE user_id = :userId LIMIT 1");
        $statement->execute([':userId' => $userId]);
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        if ($config) {
            return json_decode($config['config'], true);
        } else {
            return false;
        }
    }

    public function saveConfig($userId, $config)
    {
        $statement = $this->db->prepare('UPDATE configs_tbl SET config = :config WHERE user_id = :userId LIMIT 1;');
        return $statement->execute([':config' => $config, ':userId' => $userId]);
    }

    public function newUserConfig($userId, $config)
    {
        $statement = $this->db->prepare("INSERT INTO configs (user_id, config) VALUES (:userId, :config) ON CONFLICT DO NOTHING");
        return $statement->execute([':config' => $config, ':userId' => $userId]);
    }

    public function newTask(Config $userConfig, string $text)
    {
        $sql = 'INSERT INTO tasks (user_id, bg_color, font_color, speed, text, status) VALUES (:userId, :bgColor, :fontColor, :speed, :text, :status)';
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

    public function getTask()
    {
        $sql = "SELECT * FROM tasks WHERE status = 'new' LIMIT 1 FOR UPDATE";
        $result = $this->db->query($sql);
        $task = $result->fetch(PDO::FETCH_ASSOC);
        if ($task) {
            $statement = $this->db->prepare("UPDATE tasks SET status = 'in_process' WHERE id = :taskId");
            $statement->execute([':taskId' => $task['id']]);
            return $task;
        }
        return false;
    }

    public function setTaskDone($taskId)
    {
        $statement = $this->db->prepare("UPDATE tasks SET status = 'done' WHERE id = :taskId");
        $statement->execute([':taskId' => $taskId]);
    }
}
