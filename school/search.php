<?php

// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\Etudiant;
$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

if (isset($_GET['column']) && isset($_GET['value'])) {
    $column = $_GET['column'];
    $value = $_GET['value'];
    $stmt = $etudiant->search($column, $value);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $etudiants_arr = array();
        $etudiants_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $etudiant_item = array(
                "id" => $id,
                "matricule" => $matricule,
                "image_path" => $image_path,
                "nom" => $nom,
                "prenom" => $prenom,
                "classe" => $classe,
                "email" => $email,
                "nom_parent" => $nom_parent,
                "email_parent" => $email_parent,
                "date_naissance" => $date_naissance,
                "montant_a_payer" => $montant_a_payer,
                "solvabilite" => $solvabilite,
            );
            array_push($etudiants_arr["records"], $etudiant_item);
        }
        echo json_encode($etudiants_arr);
    } else {
        echo json_encode(array("message" => "Aucun étudiant trouvé."));
    }
} else {
    echo json_encode(array("message" => "Paramètres manquants."));
}
?>
