<?php


class DBConnect
{
    private $db;

    public function __construct()
    {
        $this->db = new SQLite3('/tmp/test.sqlite3');
        $createTableSQL = 'CREATE TABLE IF NOT EXISTS configs_tbl (user_id INTEGER PRIMARY KEY, config TEXT)';
        $result = $this->db->exec($createTableSQL) or die($this->db->lastErrorMsg());
    }

    public function getConfigByUserId($userId)
    {
        $statement = $this->db->prepare('SELECT config FROM configs_tbl WHERE user_id = :userId');
        $statement->bindValue(':userId', $userId);
        $result = $statement->execute();
        $config = $result->fetchArray();
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
        try {
            $result = $statement->execute();
        } catch (\Exception $exception) {
            print_r($exception->getMessage());
        }
        return $result;
    }

    public function createNew($userId, $config)
    {
        $statement = $this->db->prepare('INSERT INTO configs_tbl (user_id, config) VALUES (:userId, :config)');
        $statement->bindValue(':config', $config, SQLITE3_TEXT);
        $statement->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $statement->execute();
    }
}