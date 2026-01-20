<?php
namespace App;

use App\Database1;

class Professeur {
    private $conn;
    private $table_name = "prof";

    public $id;
    public $matricule_enseignant;
    public $NomPrenom;
    public $adresse_mail;
    public $date_naissance;
    public $image_path;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE matricule_enseignant = :matricule_enseignant LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET matricule_enseignant=:matricule_enseignant, NomPrenom=:NomPrenom, adresse_mail=:adresse_mail, date_naissance=:date_naissance, image_path=:image_path";
        $stmt = $this->conn->prepare($query);

        $this->matricule_enseignant = htmlspecialchars(strip_tags($this->matricule_enseignant));
        $this->NomPrenom = htmlspecialchars(strip_tags($this->NomPrenom));
        $this->adresse_mail = htmlspecialchars(strip_tags($this->adresse_mail));
        $this->date_naissance = htmlspecialchars(strip_tags($this->date_naissance));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));

        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":NomPrenom", $this->NomPrenom);
        $stmt->bindParam(":adresse_mail", $this->adresse_mail);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":image_path", $this->image_path);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET matricule_enseignant=:matricule_enseignant, NomPrenom=:NomPrenom, adresse_mail=:adresse_mail, date_naissance=:date_naissance, image_path=:image_path WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->matricule_enseignant = htmlspecialchars(strip_tags($this->matricule_enseignant));
        $this->NomPrenom = htmlspecialchars(strip_tags($this->NomPrenom));
        $this->adresse_mail = htmlspecialchars(strip_tags($this->adresse_mail));
        $this->date_naissance = htmlspecialchars(strip_tags($this->date_naissance));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":NomPrenom", $this->NomPrenom);
        $stmt->bindParam(":adresse_mail", $this->adresse_mail);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($column, $value) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE $column LIKE :value ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $value = "%$value%";
        $stmt->bindParam(":value", $value);
        $stmt->execute();
        return $stmt;
    }

    public function generateMatricule($db) {
        $prefix = "ENSkeyce";
        $stmt = $db->prepare("SELECT matricule_enseignant FROM prof WHERE matricule_enseignant LIKE :prefix ORDER BY matricule_enseignant DESC LIMIT 1");
        $prefixVar = $prefix . '%';
        $stmt->bindParam(':prefix', $prefixVar);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $lastMatricule = $row['matricule_enseignant'];
            $lastNumber = intval(substr($lastMatricule, strlen($prefix)));
            $newNumber = sprintf('%04d', $lastNumber + 1);
        } else {
            $newNumber = '0001';
        }
        return $prefix . $newNumber;
    }
}
?>