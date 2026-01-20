<?php
header('Content-Type: application/json');

// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\MatiereProf;

$database = new Database1();
$db = $database->getConnection();
$matiere_prof = new MatiereProf($db);

$column = isset($_GET['column']) ? $_GET['column'] : '';
$value = isset($_GET['value']) ? $_GET['value'] : '';

$results = $matiere_prof->search($column, $value);

if ($results->rowCount() > 0) {
    $records_arr = array();
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $records_arr[] = array(
            'id' => $row['id'],
            'matricule_enseignant' => $row['matricule_enseignant'],
            'nom_prenom' => $row['nom_prenom'],
            'nom_matiere' => $row['nom_matiere'],
            'login_enseignant' => $row['login_enseignant']
        );
    }
    echo json_encode(array('records' => $records_arr));
} else {
    echo json_encode(array('message' => 'Aucun résultat trouvé.'));
}