<?php
namespace App;

use App\Database1;

class Etudiant {
    private $conn;
    private $table_name = "etudiant_infos";

    public $id;
    public $matricule;
    public $nom;
    public $prenom;
    public $image_path;
    public $classe;
    public $email;
    public $email_parent;
    public $date_naissance;
    public $montant_a_payer;
    public $nom_parent;
    public $solvabilite;

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE matricule = :matricule LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET matricule=:matricule, nom=:nom, prenom=:prenom, image_path=:image_path, classe=:classe, email=:email, email_parent=:email_parent, date_naissance=:date_naissance, nom_parent=:nom_parent, montant_a_payer=:montant_a_payer, solvabilite=:solvabilite";
        $stmt = $this->conn->prepare($query);

        $this->matricule=htmlspecialchars(strip_tags($this->matricule));
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->image_path=htmlspecialchars(strip_tags($this->image_path));
        $this->classe=htmlspecialchars(strip_tags($this->classe));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->email_parent=htmlspecialchars(strip_tags($this->email_parent));
        $this->nom_parent=htmlspecialchars(strip_tags($this->nom_parent));
        $this->montant_a_payer=htmlspecialchars(strip_tags($this->montant_a_payer));
        $this->date_naissance=htmlspecialchars(strip_tags($this->date_naissance));
        $this->solvabilite=htmlspecialchars(strip_tags($this->solvabilite));

        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":classe", $this->classe);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":email_parent", $this->email_parent);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":montant_a_payer", $this->montant_a_payer);
        $stmt->bindParam(":nom_parent", $this->nom_parent);
        $stmt->bindParam(":solvabilite", $this->solvabilite);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET matricule=:matricule, nom=:nom, prenom=:prenom, image_path=:image_path, classe=:classe, email=:email, email_parent=:email_parent, date_naissance=:date_naissance, nom_parent=:nom_parent, montant_a_payer=:montant_a_payer, solvabilite=:solvabilite WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->matricule=htmlspecialchars(strip_tags($this->matricule));
        $this->nom=htmlspecialchars(strip_tags($this->nom));
        $this->prenom=htmlspecialchars(strip_tags($this->prenom));
        $this->image_path=htmlspecialchars(strip_tags($this->image_path));
        $this->classe=htmlspecialchars(strip_tags($this->classe));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->email_parent=htmlspecialchars(strip_tags($this->email_parent));
        $this->date_naissance=htmlspecialchars(strip_tags($this->date_naissance));
        $this->nom_parent=htmlspecialchars(strip_tags($this->nom_parent));
        $this->montant_a_payer=htmlspecialchars(strip_tags($this->montant_a_payer));
        $this->solvabilite=htmlspecialchars(strip_tags($this->solvabilite));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":classe", $this->classe);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":email_parent", $this->email_parent);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":montant_a_payer", $this->montant_a_payer);
        $stmt->bindParam(":nom_parent", $this->nom_parent);
        $stmt->bindParam(":solvabilite", $this->solvabilite);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()){
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
}
?>