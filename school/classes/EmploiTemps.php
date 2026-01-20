<?php
namespace App;

class EmploiTemps {
    // Connexion
    private $conn;
    private $table_name;

    // Propriétés
    public $id;
    public $lundi;
    public $mardi;
    public $mercredi;
    public $jeudi;
    public $vendredi;
    public $samedi;

    // Constructeur avec $db pour la connexion à la base de données
    public function __construct($db, $classe = 'b1') {
        $this->conn = $db;
        $this->table_name = "emploi_" . strtolower($classe); // Ex: emploi_b1, emploi_b2, emploi_b3
    }

    // Lecture des emplois du temps
    public function read() {
        // Requête SELECT
        $query = "SELECT 
                    id, lundi, mardi, mercredi, jeudi, vendredi, samedi
                FROM 
                    " . $this->table_name . "
                ORDER BY 
                    id ASC";

        // Préparation de la requête
        $stmt = $this->conn->prepare($query);

        // Exécution de la requête
        $stmt->execute();

        return $stmt;
    }

    // Lire un seul enregistrement
    public function readOne() {
        // Requête SELECT
        $query = "SELECT 
                    id, lundi, mardi, mercredi, jeudi, vendredi, samedi
                FROM 
                    " . $this->table_name . "
                WHERE 
                    id = ?
                LIMIT 0,1";

        // Préparation de la requête
        $stmt = $this->conn->prepare($query);

        // Liaison de l'ID
        $stmt->bindParam(1, $this->id);

        // Exécution de la requête
        $stmt->execute();

        // Récupération de la ligne
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row) {
            // Affectation des valeurs
            $this->lundi = $row['lundi'];
            $this->mardi = $row['mardi'];
            $this->mercredi = $row['mercredi'];
            $this->jeudi = $row['jeudi'];
            $this->vendredi = $row['vendredi'];
            $this->samedi = $row['samedi'];
            return true;
        }

        return false;
    }

    // Création d'un nouvel emploi du temps
    public function create() {
        // Requête INSERT
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    lundi = :lundi,
                    mardi = :mardi,
                    mercredi = :mercredi,
                    jeudi = :jeudi,
                    vendredi = :vendredi,
                    samedi = :samedi";

        // Préparation de la requête
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->lundi = htmlspecialchars(strip_tags($this->lundi));
        $this->mardi = htmlspecialchars(strip_tags($this->mardi));
        $this->mercredi = htmlspecialchars(strip_tags($this->mercredi));
        $this->jeudi = htmlspecialchars(strip_tags($this->jeudi));
        $this->vendredi = htmlspecialchars(strip_tags($this->vendredi));
        $this->samedi = htmlspecialchars(strip_tags($this->samedi));

        // Liaison des valeurs
        $stmt->bindParam(":lundi", $this->lundi);
        $stmt->bindParam(":mardi", $this->mardi);
        $stmt->bindParam(":mercredi", $this->mercredi);
        $stmt->bindParam(":jeudi", $this->jeudi);
        $stmt->bindParam(":vendredi", $this->vendredi);
        $stmt->bindParam(":samedi", $this->samedi);

        // Exécution de la requête
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Mise à jour de l'emploi du temps
    public function update() {
        // Requête UPDATE
        $query = "UPDATE " . $this->table_name . "
                SET
                    lundi = :lundi,
                    mardi = :mardi,
                    mercredi = :mercredi,
                    jeudi = :jeudi,
                    vendredi = :vendredi,
                    samedi = :samedi
                WHERE
                    id = :id";

        // Préparation de la requête
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->lundi = htmlspecialchars(strip_tags($this->lundi));
        $this->mardi = htmlspecialchars(strip_tags($this->mardi));
        $this->mercredi = htmlspecialchars(strip_tags($this->mercredi));
        $this->jeudi = htmlspecialchars(strip_tags($this->jeudi));
        $this->vendredi = htmlspecialchars(strip_tags($this->vendredi));
        $this->samedi = htmlspecialchars(strip_tags($this->samedi));

        // Liaison des valeurs
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":lundi", $this->lundi);
        $stmt->bindParam(":mardi", $this->mardi);
        $stmt->bindParam(":mercredi", $this->mercredi);
        $stmt->bindParam(":jeudi", $this->jeudi);
        $stmt->bindParam(":vendredi", $this->vendredi);
        $stmt->bindParam(":samedi", $this->samedi);

        // Exécution de la requête
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Vérifier si un emploi du temps existe déjà
    public function exists() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $row['count'] > 0;
    }

    // Récupérer les plages horaires spécifiques
    public function getHoraires($jour, $plage) {
        if($plage == "matin") {
            $query = "SELECT " . $jour . " FROM " . $this->table_name . " WHERE id = 1";
        } else {
            $query = "SELECT " . $jour . " FROM " . $this->table_name . " WHERE id = 2";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row[$jour] ?? '';
    }

    // Sauvegarder l'emploi du temps
    public function saveTimetable($timetable) {
        // Supprimer l'ancien emploi du temps
        $this->conn->exec("DELETE FROM " . $this->table_name);

        // Insérer le nouvel emploi du temps
        foreach ($timetable as $entry) {
            $day = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][$entry['day']];
            $timeSlot = $entry['timeSlot'] === 'morning' ? '8h30-12h30' : '13h30-17h30';
            $matiere = $entry['matiere'];

            $query = "INSERT INTO " . $this->table_name . " ($day) VALUES (:matiere)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['matiere' => $matiere]);
        }

        return true;
    }
}
?>