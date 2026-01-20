<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';


// Utiliser les classes
use App\Database1;
use App\Etudiant;
$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $etudiant->id = $_GET['id'];

    if ($etudiant->delete()) {
        // Supprimer également l'entrée correspondante dans la table student_compte
        $stmt = $db->prepare("DELETE FROM student_compte WHERE matricule = :matricule");
        $stmt->bindParam(':matricule', $etudiant->matricule);
        $stmt->execute();

        header("Location: EtudiantListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression de l'étudiant.";
    }
} else {
    echo "ID de l'étudiant non fourni.";
}
?>
