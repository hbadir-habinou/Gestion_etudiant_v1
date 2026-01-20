<?php
require __DIR__ . '/autoloading/autoload.php';

use App\Database1;
use App\EmploiTemps;

$database = new Database1();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$timetable = $data['timetable'];
$classe = $data['classe'];

// Créer une instance de EmploiTemps avec la classe dynamique
$emploiTemps = new EmploiTemps($db, $classe);

// Sauvegarder l'emploi du temps dans la base de données
$success = $emploiTemps->saveTimetable($timetable);

echo json_encode(['success' => $success]);