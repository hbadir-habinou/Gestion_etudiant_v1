<?php
namespace App;

use App\Database1;

class MatiereProf {
    private $conn;
    private $table_name = "matiere_prof";
    
    public $id;
    public $matricule_enseignant;
    public $nom_prenom;
    public $nom_matiere;
    public $login_enseignant;
    public $password_enseignant;
    public $nb_seance; // Ajout de la propriété nb_seance

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET 
                matricule_enseignant=:matricule_enseignant,
                nom_prenom=:nom_prenom, 
                nom_matiere=:nom_matiere,
                login_enseignant=:login_enseignant,
                password_enseignant=:password_enseignant,
                nb_seance=:nb_seance 
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->matricule_enseignant = htmlspecialchars(strip_tags($this->matricule_enseignant));
        $this->nom_prenom = htmlspecialchars(strip_tags($this->nom_prenom));
        $this->nom_matiere = htmlspecialchars(strip_tags($this->nom_matiere));
        $this->login_enseignant = htmlspecialchars(strip_tags($this->login_enseignant));
        $this->password_enseignant = htmlspecialchars(strip_tags($this->password_enseignant));
        $this->nb_seance = htmlspecialchars(strip_tags($this->nb_seance)); // Ajout de nb_seance
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":matricule_enseignant", $this->matricule_enseignant);
        $stmt->bindParam(":nom_prenom", $this->nom_prenom);
        $stmt->bindParam(":nom_matiere", $this->nom_matiere);
        $stmt->bindParam(":login_enseignant", $this->login_enseignant);
        $stmt->bindParam(":password_enseignant", $this->password_enseignant);
        $stmt->bindParam(":nb_seance", $this->nb_seance); // Ajout de nb_seance
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function search($column, $value) {
        $query = "SELECT * FROM matiere_prof WHERE " . $column . " LIKE :value";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':value', '%' . $value . '%');
        $stmt->execute();
        return $stmt;
    }
}
?>