<?php
namespace App;

use App\Database1;

class ProfessorAccount {
    private $db;

    public function __construct() {
        $database = new Database1();
        $this->db = $database->getConnection();
    }

    public function getProfessorInfoByLogin($login) {
        $query = "SELECT * FROM matiere_prof WHERE login_enseignant = :login";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getProfessorInfo($login) {
        $query = "SELECT nom_matiere FROM matiere_prof WHERE login_enseignant = :login";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function changePassword($login, $newPassword) {
        $query = "UPDATE matiere_prof SET password_enseignant = :newPassword WHERE login_enseignant = :login";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':newPassword', $newPassword);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
    }

    public function addCourse($matricule_enseignant, $nom_prenom, $nom_cours, $description, $niveau) {
        $table_cours = "cours_" . strtolower($niveau);
        $query = "INSERT INTO $table_cours (matricule_enseignant, nom_prenom, nom_cours, description) VALUES (:matricule_enseignant, :nom_prenom, :nom_cours, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->bindParam(':nom_prenom', $nom_prenom);
        $stmt->bindParam(':nom_cours', $nom_cours);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function getCourses($matricule_enseignant, $niveau) {
        $table_cours = "cours_" . strtolower($niveau);
        $query = "SELECT * FROM $table_cours WHERE matricule_enseignant = :matricule_enseignant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>