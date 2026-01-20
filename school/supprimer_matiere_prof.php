<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\MatiereProf;

$database = new Database1();
$db = $database->getConnection();
$matiere_prof = new MatiereProf($db);

if (isset($_GET['id'])) {
    $matiere_prof->id = $_GET['id'];
    
    if ($matiere_prof->delete()) {
        header("Location: MatiereProfListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression.";
    }
} else {
    echo "ID non fourni.";
}
?>