<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';


// Utiliser les classes
use App\Database1;
use App\MatiereProf;

$database = new Database1();
$db = $database->getConnection();
$matiere_prof = new MatiereProf($db);

$id = isset($_GET['id']) ? $_GET['id'] : die('ID non fourni.');
$matiere_prof->id = $id;
$data = $matiere_prof->readOne();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matiere_prof->matricule_enseignant = $_POST['matricule_enseignant'];
    $matiere_prof->nom_prenom = $_POST['nom_prenom'];
    $matiere_prof->nom_matiere = $_POST['nom_matiere'];
    $matiere_prof->login_enseignant = $_POST['login_enseignant'];
    $matiere_prof->password_enseignant = $_POST['password_enseignant'];

    if ($matiere_prof->update()) {
        header("Location: MatiereProfListe.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'Enseignant</title>
    <style>
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
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm {
            background: linear-gradient(145deg, #89f7fe, #ff7fff);
            color: white;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Modifier l'Enseignant</h2>
        <form method="POST">
            <div class="form-group">
                <label>Matricule</label>
                <input type="text" name="matricule_enseignant" value="<?php echo $data['matricule_enseignant']; ?>" required>
            </div>
            <div class="form-group">
                <label>Nom et Prénom</label>
                <input type="text" name="nom_prenom" value="<?php echo $data['nom_prenom']; ?>" required>
            </div>
            <div class="form-group">
                <label>Matière</label>
                <input type="text" name="nom_matiere" value="<?php echo $data['nom_matiere']; ?>" required>
            </div>
            <div class="form-group">
                <label>Login</label>
                <input type="text" name="login_enseignant" value="<?php echo $data['login_enseignant']; ?>" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password_enseignant" value="<?php echo $data['password_enseignant']; ?>" required>
            </div>
            <button type="submit" class="btn btn-confirm">Modifier</button>
        </form>
    </div>
</body>
</html>