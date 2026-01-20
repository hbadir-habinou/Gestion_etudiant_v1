<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';
// Utiliser les classes
use App\Database1;
use App\Professeur;

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);
$error = "";
$id = isset($_GET['id']) ? $_GET['id'] : die('ID du professeur non fourni.');

$stmt = $db->prepare("SELECT * FROM prof WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Professeur non trouvé.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule_enseignant = $_POST['matricule_enseignant'];
    $NomPrenom = $_POST['NomPrenom'];
    $adresse_mail = $_POST['adresse_mail'];
    $date_naissance = $_POST['date_naissance'];
    $image_path = $_POST['image_path'];

    if (empty($NomPrenom) || empty($adresse_mail) || empty($date_naissance)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2010-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2010.";
    } else {
        $professeur->matricule_enseignant = $matricule_enseignant;
        $professeur->id = $id;
        $professeur->NomPrenom = $NomPrenom;
        $professeur->adresse_mail = $adresse_mail;
        $professeur->date_naissance = $date_naissance;
        $professeur->image_path = $image_path;

        if ($professeur->update()) {
            header("Location: ProfesseurListe.php");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour du professeur.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Professeur</title>
    <style>
        /* Ajoutez ici le style CSS de votre fichier modifier_etudiant.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(145deg, #89f7fe, #ff7fff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transition: transform 0.3s ease;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
        }

        .form-error {
            background-color: #ffdddd;
            color: #ff4d4d;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #89f7fe;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(137, 247, 254, 0.2);
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background-color: #f1f1f1;
            color: #333;
        }

        .btn-confirm {
            background: linear-gradient(145deg, #89f7fe, #ff7fff);
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="form-card">
        <h2 class="form-title">Modifier un Professeur</h2>
        <?php if ($error): ?>
            <div class="form-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="matricule_enseignant">Matricule</label>
                <input type="text" id="matricule_enseignant" name="matricule_enseignant" value="<?php echo htmlspecialchars($row['matricule_enseignant']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="NomPrenom">Nom et Prénom</label>
                <input type="text" id="NomPrenom" name="NomPrenom" value="<?php echo htmlspecialchars($row['NomPrenom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="adresse_mail">Email</label>
                <input type="email" id="adresse_mail" name="adresse_mail" value="<?php echo htmlspecialchars($row['adresse_mail']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de Naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($row['date_naissance']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image_path">Chemin de l'image</label>
                <input type="text" id="image_path" name="image_path" value="<?php echo htmlspecialchars($row['image_path']); ?>" required>
            </div>
            <div class="form-buttons">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='ProfesseurListe.php'">Annuler</button>
                <button type="submit" class="btn btn-confirm">Confirmer</button>
            </div>
        </form>
    </div>

    <script>
        const inputs = document.querySelectorAll('.form-group input, .form-group select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
