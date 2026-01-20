<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';


// Utiliser les classes
use App\Database1;
use App\Professeur;
$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);

if (isset($_GET['column']) && isset($_GET['value'])) {
    $column = $_GET['column'];
    $value = $_GET['value'];
    $stmt = $professeur->search($column, $value);
    $num = $stmt->rowCount();
    if ($num > 0) {
        $professeurs_arr = array();
        $professeurs_arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $professeur_item = array(
                "id" => $id,
                "matricule_enseignant" => $matricule_enseignant,
                "image_path" => $image_path,
                "NomPrenom" => $NomPrenom,
                "adresse_mail" => $adresse_mail,
                "date_naissance" => $date_naissance,
            );
            array_push($professeurs_arr["records"], $professeur_item);
        }
        echo json_encode($professeurs_arr);
    } else {
        echo json_encode(array("message" => "Aucun professeur trouvé."));
    }
} else {
    echo json_encode(array("message" => "Paramètres manquants."));
}
?>
