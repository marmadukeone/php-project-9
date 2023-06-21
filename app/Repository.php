<?php

namespace App;

use PDO;
use Carbon\Carbon;

class Repository
{
    public PDO $db;

    public function __construct()
    {
        $dbUrl = 'postgresql://aleksandrsarapulov:2211@localhost:5432/project-9';

        //$databaseUrl = parse_url($_ENV['DATABASE_URL']);
        $databaseUrl = parse_url($dbUrl);
        $username = $databaseUrl['user'];
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'];
        $dbName = ltrim($databaseUrl['path'], '/');

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName;user=$username;password=$password";
        //var_dump($dsn);
        //var_dump($this->db = new PDO($dsn));
        $this->db = new PDO($dsn);
    }

    public function insertUrl(string $name)
    {
        // подготовка запроса для добавления данных
        $created_at = Carbon::now();
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':created_at', $created_at);

        $stmt->execute();

        // возврат полученного значения id
        return $this->db->lastInsertId();
    }

    public function all()
    {
        $sql = 'SELECT * FROM urls';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll($this->db::FETCH_ASSOC);
        return $result;
    }

    public function findUrl(int $id)
    {
        $sql = 'SELECT id, name, created_at FROM urls WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch($this->db::FETCH_ASSOC);
        return $result;
    }

    public function findId(string $name)
    {
        $sql = 'SELECT id FROM urls WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        $result = $stmt->fetch($this->db::FETCH_ASSOC);
        return $result;
    }

    public function addCheck(
        int $urlId,
        int $statusCode,
        ?string $title = '',
        ?string $h1 = '',
        ?string $description = ''
    ) {
        // подготовка запроса для добавления данных
        $created_at = Carbon::now();
        $sql = 'INSERT INTO urls_checks (url_id, status_code, h1, title, description, created_at)
        VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':url_id', $urlId);
        $stmt->bindValue(':created_at', $created_at);
        $stmt->bindValue(':status_code', $statusCode);
        $stmt->bindValue(':h1', $h1);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);

        $stmt->execute();
        print_r("HUI");

        // возврат полученного значения id
        return $this->db->lastInsertId();
    }

    public function findCheckUrl(int $id)
    {
        $sql = 'SELECT * FROM urls_checks WHERE url_id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchAll($this->db::FETCH_ASSOC);
        return $result;
    }

    public function findLastCheck(int $id)
    {
        $sql = 'SELECT created_at, status_code FROM urls_checks WHERE url_id = :id ORDER BY id DESC LIMIT 1;';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch($this->db::FETCH_ASSOC);
        return $result;
    }
}
