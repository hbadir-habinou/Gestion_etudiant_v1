<?php
require __DIR__ . '/autoloading/autoload.php';

use App\Database1;

$database = new Database1();
$db = $database->getConnection();

// Récupérer la classe depuis l'URL
$classe = isset($_GET['classe']) ? $_GET['classe'] : '';

// Récupérer les matières pour la classe sélectionnée
$query = "SELECT nom_matiere, nb_seance FROM matiere_prof WHERE login_enseignant LIKE :classe";
$stmt = $db->prepare($query);
$stmt->execute(['classe' => $classe . 'ENS%']);
$matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la sauvegarde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emploiTable = 'emploi_' . strtolower($classe);
    
    // Préparer les données pour chaque jour
    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $valeurs = [];
    foreach ($jours as $jour) {
        $matin = $_POST[$jour . '_matin'] ?? '';
        $apres = $_POST[$jour . '_apres'] ?? '';
        $valeurs[$jour] = $matin . '|' . $apres;
    }
    
    // Insérer dans la base de données
    $colonnes = implode(', ', $jours);
    $placeholders = ':' . implode(', :', $jours);
    
    $query = "INSERT INTO $emploiTable ($colonnes) VALUES ($placeholders)";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($valeurs);
        
        // Mettre à jour le nombre de séances restantes
        foreach ($_POST['seances_restantes'] as $matiere => $nbSeances) {
            $updateQuery = "UPDATE matiere_prof SET nb_seance = :nb_seance 
                          WHERE nom_matiere = :matiere AND login_enseignant LIKE :classe";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                'nb_seance' => $nbSeances,
                'matiere' => $matiere,
                'classe' => $classe . 'ENS%'
            ]);
        }
        
        header("Location: EmploiDuTemps" . $classe . ".php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de la sauvegarde : " . $e->getMessage();
    }
}

// [Le reste du code HTML et JavaScript reste le même...]
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un emploi du temps - <?php echo $classe; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .time-slot {
            background: #f8f9fa;
            font-weight: bold;
        }
        .lunch-break {
            background: #fff3e0;
            text-align: center;
            font-style: italic;
        }
        .seances-info {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <h2 class="mb-4">Planification de l'emploi du temps - <?php echo $classe; ?></h2>
        
        <form id="emploiForm" method="POST" class="card shadow">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Horaires</th>
                            <th>Lundi</th>
                            <th>Mardi</th>
                            <th>Mercredi</th>
                            <th>Jeudi</th>
                            <th>Vendredi</th>
                            <th>Samedi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Matin -->
                        <tr>
                            <td class="time-slot">8h30 - 12h30</td>
                            <?php foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour): ?>
                            <td>
                                <select class="form-select" name="<?php echo $jour; ?>_matin" 
                                        onchange="updateSeances(this)">
                                    <option value="">Sélectionner</option>
                                    <?php foreach($matieres as $matiere): ?>
                                    <option value="<?php echo $matiere['nom_matiere']; ?>" 
                                            data-seances="<?php echo $matiere['nb_seance']; ?>">
                                        <?php echo $matiere['nom_matiere']; ?> 
                                        (<?php echo $matiere['nb_seance']; ?> séances)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="seances-info"></div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <!-- Pause déjeuner -->
                        <tr>
                            <td colspan="7" class="lunch-break">Pause déjeuner (12h30 - 13h30)</td>
                        </tr>
                        <!-- Après-midi -->
                        <tr>
                            <td class="time-slot">13h30 - 17h30</td>
                            <?php foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour): ?>
                            <td>
                                <select class="form-select" name="<?php echo $jour; ?>_apres" 
                                        onchange="updateSeances(this)">
                                    <option value="">Sélectionner</option>
                                    <?php foreach($matieres as $matiere): ?>
                                    <option value="<?php echo $matiere['nom_matiere']; ?>" 
                                            data-seances="<?php echo $matiere['nb_seance']; ?>">
                                        <?php echo $matiere['nom_matiere']; ?> 
                                        (<?php echo $matiere['nb_seance']; ?> séances)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="seances-info"></div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>

                <!-- Champs cachés pour stocker les séances restantes -->
                <?php foreach($matieres as $matiere): ?>
                <input type="hidden" name="seances_restantes[<?php echo $matiere['nom_matiere']; ?>]" 
                       value="<?php echo $matiere['nb_seance']; ?>">
                <?php endforeach; ?>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Sauvegarder l'emploi du temps</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const seancesRestantes = {};
        
        <?php foreach($matieres as $matiere): ?>
        seancesRestantes['<?php echo $matiere['nom_matiere']; ?>'] = <?php echo $matiere['nb_seance']; ?>;
        <?php endforeach; ?>

        function updateSeances(select) {
            const matiere = select.value;
            const seances = select.options[select.selectedIndex].dataset.seances;
            
            // Mettre à jour l'affichage des séances restantes
            document.querySelectorAll('select').forEach(otherSelect => {
                if (otherSelect !== select) {
                    Array.from(otherSelect.options).forEach(option => {
                        if (option.value === matiere) {
                            option.dataset.seances = seances - 1;
                            option.textContent = `${matiere} (${seances - 1} séances)`;
                        }
                    });
                }
            });

            // Mettre à jour le champ caché
            if (matiere) {
                seancesRestantes[matiere]--;
                document.querySelector(`input[name="seances_restantes[${matiere}]"]`).value = 
                    seancesRestantes[matiere];
            }

            // Mettre à jour l'info des séances
            select.nextElementSibling.textContent = 
                matiere ? `Séance ${seancesRestantes[matiere] + 1}` : '';
        }

        // Désactiver les options quand il n'y a plus de séances
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => {
                document.querySelectorAll('select').forEach(otherSelect => {
                    Array.from(otherSelect.options).forEach(option => {
                        if (option.value) {
                            option.disabled = seancesRestantes[option.value] <= 0;
                        }
                    });
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>