<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\Professeur;

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $professeur->id = $_GET['id'];
    if ($professeur->delete()) {
        header("Location: ProfesseurListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression du professeur.";
    }
} else {
    echo "ID du professeur non fourni.";
}
?>
