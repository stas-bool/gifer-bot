<?php

class DBConnect
{
    /**
     * @var SQLite3
     */
    private $db;

    private function __construct($db)
    {
        $this->db = $db;
    }

    public static function connect()
    {
        $db = new SQLite3(__DIR__ .'/../gifer.sqlite3');
        $createUsersTableSQL = 'CREATE TABLE IF NOT EXISTS configs_tbl (user_id INTEGER PRIMARY KEY, config TEXT)';
        $createTasksTableSQL = 'CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, bg_color TEXT, font_color TEXT, speed INTEGER, text TEXT, status TEXT)';
        $db->exec($createUsersTableSQL);
        $db->exec($createTasksTableSQL);
        return new DBConnect($db);
    }

    public function getConfigByUserId($userId)
    {
        $statement = $this->db->prepare('SELECT config FROM configs_tbl WHERE user_id = :userId');
        $statement->bindValue(':userId', $userId);
        $result = $statement->execute();
        $config = $result->fetchArray(SQLITE3_ASSOC);
        if ($config) {
            return json_decode($config['config'], true);
        } else {
            return false;
        }
    }

    public function saveConfig($userId, $config)
    {
        $statement = $this->db->prepare('UPDATE configs_tbl SET config = :config WHERE user_id = :userId LIMIT 1;');
        $statement->bindValue(':config', $config, SQLITE3_TEXT);
        $statement->bindValue(':userId', $userId, SQLITE3_INTEGER);
        return $statement->execute();
    }

    public function createNew($userId, $config)
    {
        $statement = $this->db->prepare('INSERT INTO configs_tbl (user_id, config) VALUES (:userId, :config)');
        $statement->bindValue(':config', $config, SQLITE3_TEXT);
        $statement->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function newTask(Config $userConfig, string $text)
    {
        $sql = 'INSERT INTO tasks (user_id, bg_color, font_color, speed, text, status) VALUES (:userId, :bgColor, :fontColor, :speed, :text, :status)';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':userId', $userConfig->getUserId(), SQLITE3_INTEGER);
        $statement->bindValue(':bgColor', $userConfig->getBgColor(), SQLITE3_TEXT);
        $statement->bindValue(':fontColor', $userConfig->getFontColor(), SQLITE3_TEXT);
        $statement->bindValue(':speed', $userConfig->getSpeed(), SQLITE3_INTEGER);
        $statement->bindValue(':text', $text, SQLITE3_TEXT);
        $statement->bindValue(':status', 'new', SQLITE3_TEXT);
        $statement->execute();
    }

    public function getTask()
    {
        $sql = 'SELECT * FROM tasks WHERE status = "new" LIMIT 1';
        $result = $this->db->query($sql);
        $task = $result->fetchArray(SQLITE3_ASSOC);
        $statement = $this->db->prepare('UPDATE tasks SET status = "in_process" WHERE id = :taskId');
        $statement->bindValue(':taskId', $task['id']);
        $statement->execute();
        return $task;
    }

    public function setTaskDone($taskId)
    {
        $statement = $this->db->prepare('UPDATE tasks SET status = "done" WHERE id = :taskId');
        $statement->bindValue(':taskId', $taskId);
        $statement->execute();
    }
}
