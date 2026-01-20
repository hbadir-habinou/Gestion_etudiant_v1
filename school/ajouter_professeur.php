<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';
require_once 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Utiliser les classes
use App\Database1;
use App\Professeur;

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);
$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NomPrenom = $_POST['NomPrenom'];
    $adresse_mail = $_POST['adresse_mail'];
    $date_naissance = $_POST['date_naissance'];
    $image_path = $_FILES['image']['name'];

    if (empty($NomPrenom) || empty($adresse_mail) || empty($date_naissance) || empty($image_path)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2010-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2010.";
    } else {
        $matricule_enseignant = $professeur->generateMatricule($db);
        $target_dir = "photos_enseignant/";
        $target_file = $target_dir . basename($image_path);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $professeur->matricule_enseignant = $matricule_enseignant;
            $professeur->NomPrenom = $NomPrenom;
            $professeur->adresse_mail = $adresse_mail;
            $professeur->date_naissance = $date_naissance;
            $professeur->image_path = $image_path;

            if ($professeur->create()) {
                $success = true;
                header("Location: ProfesseurListe.php");
                exit();
            } else {
                $error = "Erreur lors de l'ajout du professeur.";
            }
        } else {
            $error = "Erreur lors du téléchargement de l'image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Professeur</title>
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

        .form-group input[type="file"] {
            border: none;
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

        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Formulaire -->
        <div class="form-content" style="flex: 1;">
            <h2><i class="bi bi-person-plus icon"></i>Ajouter un Professeur</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="NomPrenom"><i class="bi bi-person icon"></i>Nom et Prénom</label>
                    <input type="text" id="NomPrenom" name="NomPrenom" required>
                </div>
                <div class="form-group">
                    <label for="adresse_mail"><i class="bi bi-envelope icon"></i>Email</label>
                    <input type="email" id="adresse_mail" name="adresse_mail" required>
                </div>
                <div class="form-group">
                    <label for="date_naissance"><i class="bi bi-calendar icon"></i>Date de Naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>
                <div class="form-group">
                    <label for="image"><i class="bi bi-image icon"></i>Photo</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <button type="submit"><i class="bi bi-save icon"></i>Ajouter Professeur</button>
                </div>
            </form>
        </div>

        <!-- Prévisualisation de l'image -->
        <div class="image-preview-container">
            <div class="image-preview">
                <img id="imagePreview" src="#" alt="Aperçu de l'image" style="display:none;">
            </div>
        </div>
    </div>

    <script>
        // Prévisualisation de l'image
        document.getElementById('image').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                const preview = document.getElementById('imagePreview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
    </script>
</body>
</html>