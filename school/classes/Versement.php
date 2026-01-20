<?php
namespace App;

use App\Database1;

class Versement {
    private $conn;
    private $table_name = "versement";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function search($column, $value) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE $column LIKE :value";
        $stmt = $this->conn->prepare($query);
        $value = "%{$value}%";
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>