<?php

// Inclure la connexion à la base de données
require __DIR__ . '/autoloading/autoload.php';
// Utiliser les classes
use App\Database1;
use App\Etudiant;
require_once 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe = $_POST['classe'];
    $email = $_POST['email'];
    $email_parent = $_POST['email_parent'];
    $nom_parent = $_POST['nom_parent'];
    $date_naissance = $_POST['date_naissance'];
    $image_path = $_FILES['image']['name'];

    // Déterminer le montant à payer en fonction de la classe
    $montant_a_payer = 0;
    if ($classe === 'B1') {
        $montant_a_payer = 1000000;
    } elseif ($classe === 'B2') {
        $montant_a_payer = 2000000;
    } elseif ($classe === 'B3') {
        $montant_a_payer = 3000000;
    }

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($classe) || empty($email) || empty($email_parent) || empty($date_naissance) || empty($image_path)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2011-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2011.";
    } else {
        // Générer le matricule
        $matricule = generateMatricule($db, $classe);

        // Déplacer l'image téléchargée
        $target_dir = "photos/";
        $target_file = $target_dir . basename($image_path);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Insérer les données dans la table etudiant_infos
            $etudiant->nom = $nom;
            $etudiant->prenom = $prenom;
            $etudiant->matricule = $matricule;
            $etudiant->image_path = $image_path;
            $etudiant->classe = $classe;
            $etudiant->email = $email;
            $etudiant->email_parent = $email_parent;
            $etudiant->nom_parent = $nom_parent;
            $etudiant->date_naissance = $date_naissance;
            $etudiant->montant_a_payer = $montant_a_payer;
            $etudiant->solvabilite = 'INSOLVABLE';

            if ($etudiant->create()) {
                // Insérer les données dans la table student_compte
                $stmt = $db->prepare("INSERT INTO student_compte (matricule, password) VALUES (:matricule, :password)");
                $matriculeVar = $matricule; // Utiliser une variable pour passer par référence
                $stmt->bindParam(':matricule', $matriculeVar);
                $stmt->bindParam(':password', $matriculeVar);
                $stmt->execute();

                // Générer la carte étudiant
                $pdf_path = generateStudentCard($matricule, $nom, $prenom, $classe, $date_naissance, $image_path);

                // Envoyer l'email
                sendEmail($email_parent, $email, "Carte d'Etudiant", "Recevez ci-joint la carte d'étudiant pour l'étudiant : $nom $prenom, dont le matricule est le suivant : $matricule", $pdf_path);

                $success = true;
                header("Location: EtudiantListe.php");
                exit();
            } else {
                $error = "Erreur lors de l'ajout de l'étudiant.";
            }
        } else {
            $error = "Erreur lors du téléchargement de l'image.";
        }
    }
}

function generateMatricule($db, $classe) {
    $year = date('y');
    $prefix = $year . $classe;
    $stmt = $db->prepare("SELECT matricule FROM etudiant_infos WHERE matricule LIKE :prefix ORDER BY matricule DESC LIMIT 1");
    $prefixVar = $prefix . '%'; // Utiliser une variable pour passer par référence
    $stmt->bindParam(':prefix', $prefixVar);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $lastMatricule = $row['matricule'];
        $lastNumber = intval(substr($lastMatricule, -3));
        $newNumber = sprintf('%03d', $lastNumber + 1);
    } else {
        $newNumber = '001';
    }

    return $prefix . $newNumber;
}

function generateStudentCard($matricule, $nom, $prenom, $classe, $date_naissance, $image_path) {
    // Initialiser FPDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Style de la carte étudiant
    $pdf->SetFillColor(240, 240, 255); // Fond clair
    $pdf->SetDrawColor(200, 200, 200); // Bordure douce
    $pdf->SetTextColor(50, 50, 50);

    // Conteneur de la carte
    $pdf->SetXY(20, 40);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(170, 10, 'Carte Etudiant', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Ajout de l'image (photo de profil)
    if (!empty($image_path) && file_exists('photos/' . $image_path)) {
        $pdf->Image('photos/' . $image_path, 85, 55, 40, 40); // Centré
        $pdf->Ln(50);
    }

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prenom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de Naissance: ' . $date_naissance, 0, 1, 'C');

    // Ajout du logo de Keyce Informatique
    $pdf->Image('images/keyce.jpeg', 15, 10, 30, 30);

    // Ajout d'une bordure autour de la carte
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF dans le sous-dossier "cartes"
    $pdf_path = 'cartes/carte_etudiant_' . $matricule . '.pdf';
    $pdf->Output('F', $pdf_path);

    return $pdf_path;
}

function sendEmail($to, $cc, $subject, $body, $attachment) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output
        $mail->isSMTP();                           // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';            // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                    // Enable SMTP authentication
        $mail->Username = 'gaza45palestine@gmail.com';  // SMTP username
        $mail->Password = 'tira vtly vbec schk';     // SMTP password (app-specific password)
        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                         // TCP port to connect to

        //Recipients
        $mail->setFrom('gaza45palestine@gmail.com', 'FREE PALESTINE');
        $mail->addAddress($to);                    // Add a recipient
        $mail->addCC($cc);                         // Add a CC recipient

        // Attachments
        $mail->addAttachment($attachment);         // Add attachments

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Étudiant</title>
    <style>
        /* Fond avec un dégradé doux */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(145deg, #fce4ec, #e3f2fd);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Conteneur du formulaire */
        .form-container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 400px;
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .form-container .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-container .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #555;
        }

        .form-container .form-group input,
        .form-container .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-container .form-group input[type="file"] {
            border: none;
        }

        .form-container .form-group button {
            background: linear-gradient(145deg, #64b5f6, #f06292);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .form-container .form-group button:hover {
            background: linear-gradient(145deg, #42a5f5, #ec407a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(64, 115, 255, 0.2);
        }

        .form-container .error {
            color: #f44336;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .form-container img {
            margin-top: 10px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Ajouter un Étudiant</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="classe">Classe</label>
                <select id="classe" name="classe" required>
                    <option value="B1">B1</option>
                    <option value="B2">B2</option>
                    <option value="B3">B3</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="nom_parent">Nom Parent</label>
                <input type="text" id="nom_parent" name="nom_parent" required>
            </div>
            <div class="form-group">
                <label for="email_parent">Email Parent</label>
                <input type="email" id="email_parent" name="email_parent" required>
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de Naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" required>
            </div>
            <div class="form-group">
                <label for="image">Photo</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <img id="imagePreview" src="#" alt="Aperçu de l'image" style="display:none;">
            </div>
            <div class="form-group">
                <button type="submit">Ajouter Étudiant</button>
            </div>
        </form>
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