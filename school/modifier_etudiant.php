<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';
// Utiliser les classes
use App\Database1;
use App\Etudiant;

$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

$error = "";
$id = isset($_GET['id']) ? $_GET['id'] : die('ID de l\'étudiant non fourni.');

// Récupérer les informations de l'étudiant
$stmt = $db->prepare("SELECT * FROM etudiant_infos WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Étudiant non trouvé.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe = $_POST['classe'];
    $email = $_POST['email'];
    $email_parent = $_POST['email_parent'];
    $nom_parent = $_POST['nom_parent'];
    $date_naissance = $_POST['date_naissance'];
    $montant_a_payer = $_POST['montant_a_payer'];
    $image_path = $_POST['image_path'];
    $solvabilite = $_POST['solvabilite'];

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($classe) || empty($email) || empty($email_parent) || empty($date_naissance)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2011-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2011.";
    } else {
        // Mettre à jour les informations de l'étudiant
        $etudiant->matricule = $matricule;
        $etudiant->id = $id;
        $etudiant->nom = $nom;
        $etudiant->prenom = $prenom;
        $etudiant->classe = $classe;
        $etudiant->email = $email;
        $etudiant->email_parent = $email_parent;
        $etudiant->date_naissance = $date_naissance;
        $etudiant->nom_parent = $nom_parent;
        $etudiant->montant_a_payer = $montant_a_payer;
        $etudiant->image_path = $image_path;
        $etudiant->solvabilite = $solvabilite;
    

        if ($etudiant->update()) {
            header("Location: EtudiantListe.php");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour de l'étudiant.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Étudiant</title>
    <style>
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
        <h2 class="form-title">Modifier un Étudiant</h2>
        
        <?php if ($error): ?>
            <div class="form-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="matricule">Matricule</label>
                <input type="text" id="matricule" name="matricule" 
                       value="<?php echo htmlspecialchars($row['matricule']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" 
                       value="<?php echo htmlspecialchars($row['nom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" 
                       value="<?php echo htmlspecialchars($row['prenom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="classe">Classe</label>
                <select id="classe" name="classe" required>
                    <option value="B1" <?php if ($row['classe'] === 'B1') echo 'selected'; ?>>B1</option>
                    <option value="B2" <?php if ($row['classe'] === 'B2') echo 'selected'; ?>>B2</option>
                    <option value="B3" <?php if ($row['classe'] === 'B3') echo 'selected'; ?>>B3</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="nom_parent">Nom Parent</label>
                <input type="text" id="nom_parent" name="nom_parent" 
                       value="<?php echo htmlspecialchars($row['nom_parent']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email_parent">Email Parent</label>
                <input type="email" id="email_parent" name="email_parent" 
                       value="<?php echo htmlspecialchars($row['email_parent']); ?>" required>
            </div>
            <div class="form-group">
                <label for="montant_a_payer">Montant à payer</label>
                <input type="text" id="montant_a_payer" name="montant_a_payer" 
                       value="<?php echo htmlspecialchars($row['montant_a_payer']); ?>" required>
            </div>
            <div class="form-group">
                <label for="solvabilite">Solvabilité</label>
                <input type="text" id="solvabilite" name="solvabilite" 
                       value="<?php echo htmlspecialchars($row['solvabilite']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de Naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" 
                       value="<?php echo htmlspecialchars($row['date_naissance']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image_path">Chemin de l'image</label>
                <input type="text" id="image_path" name="image_path" 
                       value="<?php echo htmlspecialchars($row['image_path']); ?>" required>
            </div>
            <div class="form-buttons">
                <button type="button" class="btn btn-cancel" 
                        onclick="window.location.href='EtudiantListe.php'">Annuler</button>
                <button type="submit" class="btn btn-confirm">Confirmer</button>
            </div>
        </form>
    </div>

    <script>
        // Form input focus animation
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