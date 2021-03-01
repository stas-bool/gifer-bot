<?php

namespace Bot;

use Bot\model\Config;
use PDO;
use RuntimeException;

class DB
{
    /**
     * @var PDO
     */
    private $db;

    private function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public static function connect($dsn, $user, $password): DB
    {
        $db = new PDO($dsn, $user, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = self::createTables($db);
        if ($result) {
            return new self($db);
        }
        throw new RuntimeException("Не удалось подключиться к базе");
    }

    public static function createTables(PDO $db): bool
    {
        $createConfigsTable = "CREATE TABLE IF NOT EXISTS config (
    id INTEGER UNIQUE, 
    speed INTEGER, 
    bg_color VARCHAR (10), 
    font_color VARCHAR (10) 
                                       )";
        $createTasksTable = "CREATE TABLE IF NOT EXISTS task (
    id SERIAL, 
    config INTEGER REFERENCES config (id), 
    text TEXT, status SMALLINT
                                )";
        $createConfigsTableResult = $db->query($createConfigsTable);
        $createTasksTableResult = $db->query($createTasksTable);
        return $createConfigsTableResult && $createTasksTableResult;
    }

    /**
     * Получает массив настроек
     * @param $userId
     * @return mixed Массив настроек или false
     */
    public function getConfigByUserId($userId)
    {
        $statement = $this->db->prepare("SELECT * FROM user_config WHERE user_id=:userId LIMIT 1");
        $statement->execute([':userId' => $userId]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Сохраняет настройки пользователя
     * @param Config $config
     * @return bool true в случае успеха, иначе - false
     */
    public function saveConfig(Config $config): bool
    {
        if ($config->hasErrors()) {
            return false;
        }
        $statement = $this->db->prepare(
            'INSERT INTO user_config (user_id, speed, bg_color, font_color) '.
                'VALUES (:userId, :speed, :bg_color, :font_color) '.
                'ON CONFLICT (user_id) '.
                'DO UPDATE SET speed=:speed, bg_color=:bg_color, font_color=:font_color'
        );
        $result = $statement->execute([
            ':userId' => $config->getId(),
            ':speed' => $config->getSpeed(),
            ':bg_color' => $config->getBgColor(),
            ':font_color' => $config->getFontColor(),
        ]);
        $statement->closeCursor();
        return $result;
    }

    /**
     * Создает новое задание
     * @param int $userId
     * @param string $text
     * @return int
     */
    public function newTask(int $userId, string $text): int
    {
        if (iconv_strlen($text) > 300) {
            throw new RuntimeException("Слишком длинный текст");
        }
        $sql = 'INSERT INTO task (config, text, status)
            VALUES (:config, :text, :status)';
        $statement = $this->db->prepare($sql);
        $values = [
            ':config' => $userId,
            ':text' => $text,
            ':status' => 'new',
        ];
        $statement->execute($values);
        $statement->closeCursor();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Берет новое задание со статусом new и ставит статус in_process
     * @return array|null
     */
    public function getNewTask(): ?array
    {
        $this->db->exec("BEGIN");
        $sql = "SELECT * FROM task t JOIN user_config c on t.config = c.user_id WHERE status = 'new' LIMIT 1 FOR UPDATE";
        $result = $this->db->query($sql);
        $task = $result->fetch(PDO::FETCH_ASSOC);
        if ($task) {
            $statement = $this->db->prepare("UPDATE task SET status = 'in_process' WHERE id=:taskId");
            $statement->execute([':taskId' => $task['id']]);
            $this->db->exec("COMMIT");
            return $task;
        }
        $this->db->exec("COMMIT");
        return null;
    }

    /**
     * Устанавливает статус done
     * @param $taskId
     * @return bool
     */
    public function setTaskDone($taskId): bool
    {
        $statement = $this->db->prepare("UPDATE task SET status = 'done' WHERE id=:taskId");
        $result = $statement->execute([':taskId' => $taskId]);
        $this->db->exec("COMMIT");
        $statement->closeCursor();
        return $result;
    }

    public function deleteConfig($userId): bool
    {
        $statement = $this->db->prepare("DELETE FROM user_config WHERE user_id=:userId");
        $result = $statement->execute([':userId' => $userId]);
        $this->db->exec("COMMIT");
        return $result;
    }

    public function deleteTask($taskId): bool
    {
        $statement = $this->db->prepare("DELETE FROM task WHERE id=:taskId");
        $result = $statement->execute([':taskId' => $taskId]);
        $this->db->exec("COMMIT");
        return $result;
    }

    public function query(string $sql)
    {
        return $this->db->query($sql);
    }
}
