<?php
// Inclure la connexion à la base de données
require __DIR__ . '/autoloading/autoload.php';
// Utiliser les classes
use App\Database1;

// Récupérer les enseignants depuis la base de données
$database = new Database1();
$conn = $database->getConnection();
$query = "SELECT matricule_enseignant, NomPrenom FROM prof";
$stmt = $conn->prepare($query);
$stmt->execute();
$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $matricule_enseignant = $_POST['matricule_enseignant'];
    $nom_prenom = $_POST['nom_prenom'];
    $niveau = $_POST['niveau'];
    $nom_matiere = strtoupper(trim($_POST['nom_matiere']));
    $nb_seance = $_POST['nb_seance']; // Récupérer le nombre de séances

    try {
        // Début de la transaction
        $conn->beginTransaction();

        // Vérifier les étudiants dans la table etudiant_infos pour le niveau sélectionné
        $etudiants_query = "SELECT matricule, nom, prenom FROM etudiant_infos WHERE classe = :niveau";
        $stmt_etudiants = $conn->prepare($etudiants_query);
        $stmt_etudiants->execute([':niveau' => $niveau]);
        $etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);

        // Déterminer les tables correspondantes pour le niveau
        $table_notes = "note_" . strtolower($niveau);
        $table_tp = "tp_" . strtolower($niveau);
        $table_exam = "exam_" . strtolower($niveau);

        // Fonction pour vérifier et ajouter une colonne
        function addColumnIfNotExists($conn, $table, $column) {
            $check_column_query = "SHOW COLUMNS FROM $table LIKE :column";
            $stmt_column = $conn->prepare($check_column_query);
            $stmt_column->execute([':column' => $column]);

            if ($stmt_column->rowCount() === 0) {
                $add_column_query = "ALTER TABLE $table ADD COLUMN `$column` FLOAT DEFAULT NULL";
                $conn->exec($add_column_query);
            }
        }

        // Vérifier et ajouter la colonne pour la matière dans les tables notes, tp, et exam
        addColumnIfNotExists($conn, $table_notes, $nom_matiere);
        addColumnIfNotExists($conn, $table_tp, $nom_matiere);
        addColumnIfNotExists($conn, $table_exam, $nom_matiere);

        // Fonction pour ajouter les étudiants dans une table si nécessaire
        function addStudentsIfNotExists($conn, $table, $etudiants) {
            foreach ($etudiants as $etudiant) {
                $check_etudiant_query = "SELECT * FROM $table WHERE matricule_etudiant = :matricule";
                $stmt_check = $conn->prepare($check_etudiant_query);
                $stmt_check->execute([':matricule' => $etudiant['matricule']]);

                if ($stmt_check->rowCount() === 0) {
                    $insert_etudiant_query = "INSERT INTO $table (matricule_etudiant, nom, prenom) VALUES (:matricule, :nom, :prenom)";
                    $stmt_insert = $conn->prepare($insert_etudiant_query);
                    $stmt_insert->execute([
                        ':matricule' => $etudiant['matricule'],
                        ':nom' => $etudiant['nom'],
                        ':prenom' => $etudiant['prenom']
                    ]);
                }
            }
        }

        // Ajouter les étudiants dans les tables notes, tp, et exam si nécessaire
        addStudentsIfNotExists($conn, $table_notes, $etudiants);
        addStudentsIfNotExists($conn, $table_tp, $etudiants);
        addStudentsIfNotExists($conn, $table_exam, $etudiants);

        // Générer un login_enseignant unique
        $login_base = $niveau . 'ENS';
        $login_enseignant = $login_base . '001';
        $stmt_check_login = $conn->prepare("SELECT * FROM matiere_prof WHERE login_enseignant = :login");
        $stmt_check_login->bindParam(':login', $login_enseignant);
        $stmt_check_login->execute();

        while ($stmt_check_login->rowCount() > 0) {
            $last_number = intval(substr($login_enseignant, -3)) + 1;
            $login_enseignant = $login_base . str_pad($last_number, 3, '0', STR_PAD_LEFT);
            $stmt_check_login->execute([':login' => $login_enseignant]);
        }

        // Ajouter la matière dans la table matiere_prof avec le nombre de séances
        $query_insert = "INSERT INTO matiere_prof (matricule_enseignant, nom_prenom, nom_matiere, login_enseignant, password_enseignant, nb_seance)
                          VALUES (:matricule, :nom_prenom, :nom_matiere, :login, :password, :nb_seance)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->execute([
            ':matricule' => $matricule_enseignant,
            ':nom_prenom' => $nom_prenom,
            ':nom_matiere' => $nom_matiere,
            ':login' => $login_enseignant,
            ':password' => $login_enseignant,
            ':nb_seance' => $nb_seance // Ajout du nombre de séances
        ]);

        // Valider la transaction
        $conn->commit();
    } catch (Exception $e) {
        // Vérifier si une transaction est active avant de faire un rollback
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // Enregistrer l'erreur dans un fichier de log
        error_log("Erreur : " . $e->getMessage() . "\n", 3, "error_log.txt");
    }

    // Redirection vers la page ListeMatiere.php
    header('Location: ListeMatiere.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Matière</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e3d59;
            --secondary-color: #2a4d6e;
            --light-blue: #e6f0f9;
            --dark-gray: #424242;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 800px; /* Largeur augmentée pour un cadre paysage */
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: var(--dark-gray);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input[readonly] {
            background: #f0f0f0;
        }

        .form-group button {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .form-group button:hover {
            background: linear-gradient(145deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .error {
            color: #f44336;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .icon {
            margin-right: 10px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="bi bi-book icon"></i>Ajouter une Matière</h2>
        <form method="POST">
            <div class="form-group">
                <label for="enseignant"><i class="bi bi-person icon"></i>Enseignant</label>
                <select id="enseignant" onchange="setMatricule(this.value)" required>
                    <option value="">Sélectionnez un enseignant</option>
                    <?php foreach ($enseignants as $enseignant): ?>
                        <option value="<?php echo $enseignant['matricule_enseignant']; ?>">
                            <?php echo $enseignant['NomPrenom']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="matricule"><i class="bi bi-id-card icon"></i>Matricule Enseignant</label>
                <input type="text" id="matricule" name="matricule_enseignant" readonly>
            </div>
            <div class="form-group">
                <label for="nom_prenom"><i class="bi bi-person icon"></i>Nom & Prénom</label>
                <input type="text" id="nom_prenom" name="nom_prenom" readonly>
            </div>
            <div class="form-group">
                <label for="niveau"><i class="bi bi-layers icon"></i>Niveau</label>
                <select id="niveau" name="niveau" required>
                    <option value="">Sélectionnez un niveau</option>
                    <option value="B1">B1</option>
                    <option value="B2">B2</option>
                    <option value="B3">B3</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nom_matiere"><i class="bi bi-book icon"></i>Nom de la Matière</label>
                <input type="text" id="nom_matiere" name="nom_matiere" placeholder="Entrez le nom de la matière" required>
            </div>
            <div class="form-group">
                <label for="nb_seance"><i class="bi bi-calendar icon"></i>Nombre de séances</label>
                <input type="number" id="nb_seance" name="nb_seance" placeholder="Entrez le nombre de séances" required>
            </div>
            <div class="form-group">
                <button type="submit"><i class="bi bi-save icon"></i>Ajouter la Matière</button>
            </div>
        </form>
    </div>

    <script>
        function setMatricule(value) {
            const selectedOption = document.querySelector(`#enseignant option[value='${value}']`);
            const nomPrenom = selectedOption ? selectedOption.textContent : '';
            document.getElementById('matricule').value = value;
            document.getElementById('nom_prenom').value = nomPrenom;
        }
    </script>
</body>
</html>