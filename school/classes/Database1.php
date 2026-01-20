<?php
namespace App;

class Database1 {
    private $host = 'localhost';
    private $db_name = 'ecole';
    private $username = 'root';
    private $password = 'hbadir';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(\PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>