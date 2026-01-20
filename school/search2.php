<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';
// Utiliser les classes
use App\Database1;
use App\Versement;
$database = new Database1();
$db = $database->getConnection();
$versement = new Versement($db);

if (isset($_GET['column']) && isset($_GET['value'])) {
    $column = $_GET['column'];
    $value = $_GET['value'];
    $stmt = $versement->search($column, $value);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $versements_arr = array();
        $versements_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $versement_item = array(
                "id" => $row['id'],
                "numero_facture" => $row['numero_facture'],
                "montant_verse" => $row['montant_verse'],
                "reste_pension" => $row['reste_pension'],
                "matricule_etudiant" => $row['matricule_etudiant'],
                "date_versement" => $row['date_versement'],
            );
            array_push($versements_arr["records"], $versement_item);
        }
        echo json_encode($versements_arr);
    } else {
        echo json_encode(array("message" => "Aucun versement trouvé."));
    }
} else {
    echo json_encode(array("message" => "Paramètres manquants."));
}
?>
