<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\Versement;
$database = new Database1(); // Utilisation de Database1
$db = $database->getConnection();
$versement = new Versement($db);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($versement->delete($id)) {
        header("Location: VersementListe.php?success=1");
    } else {
        header("Location: VersementListe.php?error=1");
    }
}
?>