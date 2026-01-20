<?php
require_once 'classes/db_connect.php';
require 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Connexion à la base de données
$database = new Database1();
$conn = $database->getConnection();

// Récupération des données des étudiants
$query = "SELECT matricule, nom, prenom, email, email_parent FROM etudiant_infos";
$stmt = $conn->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'];
    $montant_verse = $_POST['montant'];
    $date_versement = date('Y-m-d H:i:s');

    // Vérification des données de l'étudiant
    $query = "SELECT montant_a_payer, nom, prenom, email, email_parent FROM etudiant_infos WHERE matricule = :matricule";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':matricule', $matricule);
    $stmt->execute();
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($etudiant && $montant_verse > 0) {
        $montant_a_payer = $etudiant['montant_a_payer'];
        $nom = $etudiant['nom'];
        $prenom = $etudiant['prenom'];
        $email_parent = $etudiant['email_parent'];
        $email_eleve = $etudiant['email'];

        if ($montant_verse <= $montant_a_payer) {
            // Mise à jour du montant à payer
            $nouveau_montant = $montant_a_payer - $montant_verse;
            $solvabilite = $nouveau_montant == 0 ? 'SOLVABLE' : 'INSOLVABLE';

            $query = "UPDATE etudiant_infos SET montant_a_payer = :nouveau_montant, solvabilite = :solvabilite WHERE matricule = :matricule";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nouveau_montant', $nouveau_montant);
            $stmt->bindParam(':solvabilite', $solvabilite);
            $stmt->bindParam(':matricule', $matricule);
            $stmt->execute();

            // Générer numéro de facture
            $query = "SELECT COUNT(*) AS total FROM versement";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['total'] + 1;
            $numero_facture = "2412V" . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Insertion dans la table versement
            $query = "INSERT INTO versement (numero_facture, montant_verse, reste_pension, matricule_etudiant, date_versement)
                      VALUES (:numero_facture, :montant_verse, :reste_pension, :matricule_etudiant, :date_versement)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':numero_facture', $numero_facture);
            $stmt->bindParam(':montant_verse', $montant_verse);
            $stmt->bindParam(':reste_pension', $nouveau_montant);
            $stmt->bindParam(':matricule_etudiant', $matricule);
            $stmt->bindParam(':date_versement', $date_versement);
            $stmt->execute();

            // Générer reçu PDF
            $pdf_path = generateReceipt($numero_facture, $nom, $prenom, $montant_verse, $nouveau_montant, $date_versement);

            // Envoyer email avec message dynamique généré par Gemini
            sendEmail($email_parent, $email_eleve, $nom, $prenom, $montant_verse, $nouveau_montant, $pdf_path);

            header('Location: VersementListe.php');
            exit();
        } else {
            $error = "Le montant versé dépasse le montant restant à payer.";
        }
    } else {
        $error = "Montant invalide ou étudiant introuvable.";
    }
}

// Fonction pour générer un reçu PDF
function generateReceipt($numero_facture, $nom, $prenom, $montant_verse, $reste_pension, $date_versement) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Ajout du logo
    $logo_path = 'images/keyce.jpeg'; // Chemin du logo
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 10, 10, 30, 30); // Logo en haut à gauche
    }

    // Contour de la page
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(10, 10, 190, 270, 'D'); // Bordure autour du contenu

    // Titre principal
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetXY(50, 20); // Position centrée
    $pdf->Cell(110, 10, 'Reçu de Versement', 0, 1, 'C');

    $pdf->Ln(20);

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, "Numero de Facture : $numero_facture", 0, 1, 'C');
    $pdf->Cell(0, 10, "Nom et Prenom : $nom $prenom", 0, 1, 'C');
    $pdf->Cell(0, 10, "Montant Verse : $montant_verse FCFA", 0, 1, 'C');
    $pdf->Cell(0, 10, "Reste a Payer : $reste_pension FCFA", 0, 1, 'C');
    $pdf->Cell(0, 10, "Date de Versement : $date_versement", 0, 1, 'C');

    $pdf->Ln(20);

    // Texte informatif (footer)
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, 'Produit par Keyce Informatique et Intelligence Artificielle', 0, 1, 'C');

    // Sortie PDF
    $pdf_path = "recus/recu_$numero_facture.pdf";
    $pdf->Output('F', $pdf_path);

    return $pdf_path;
}

// Fonction pour générer un message via l'API Gemini
function gemini($nom, $prenom, $montantPayer, $reste)
{
    // Clé API
    $GKey = "AIzaSyCppIR7-I0eqmDgYYXv_EqONSxD3z6eLYc";

    // Définir la question
    $question = "Peux-tu me produire un mail professionnel, concis et sans partie à remplir pour décrire la situation financière d'un étudiant dont le nom est $nom, le prénom $prenom, qui vient de payer une somme de $montantPayer FCFA. Le reste à payer est de $reste FCFA. Le mail sera adressé aux parents. Le nom de l'établissement c'est Keyce Informatique et IA et le message est envoyé de la part de la scolarité de l'établissement. Sois un peu plus large dans le message et utilise un langage professionnel.";

    // Construire l'URL de l'API
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;

    // Préparer les données de la requête
    $requestData = json_encode([
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $question]
                ]
            ]
        ]
    ]);

    // Initialiser cURL
    $ch = curl_init($url);

    // Configurer les options cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);

    // Désactiver la vérification SSL (utiliser uniquement pour les tests)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Envoyer la requête et récupérer la réponse
    $response = curl_exec($ch);

    // Vérifier les erreurs cURL
    if (curl_errno($ch)) {
        die("Erreur cURL : " . curl_error($ch));
    }

    // Fermer la connexion cURL
    curl_close($ch);

    // Décoder la réponse JSON
    $responseObject = json_decode($response, true);

    // Vérifier si des candidats existent dans la réponse
    if (isset($responseObject['candidates']) && count($responseObject['candidates']) > 0) {
        // Obtenir le contenu du premier candidat
        $content = $responseObject['candidates'][0]['content'] ?? null;

        // Vérifier si le contenu existe
        if ($content && isset($content['parts']) && count($content['parts']) > 0) {
            return $content['parts'][0]['text'];
        } else {
            return "Aucune partie trouvée dans le contenu sélectionné.";
        }
    } else {
        return "Aucun candidat trouvé dans la réponse JSON.";
    }
}

// Fonction pour envoyer un email avec PHPMailer
function sendEmail($to, $cc, $nom, $prenom, $montant_paye, $reste_payer, $attachment) {
    $mail = new PHPMailer(true);
    try {
        $body = gemini($nom, $prenom, $montant_paye, $reste_payer);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gaza45palestine@gmail.com';
        $mail->Password = 'tira vtly vbec schk';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('gaza45palestine@gmail.com', 'FREE PALESTINE');
        $mail->addAddress($to);
        $mail->addCC($cc);
        $mail->addAttachment($attachment);

        $mail->isHTML(true);
        $mail->Subject = "Reçu de Versement";
        $mail->Body = nl2br($body);

        $mail->send();
    } catch (Exception $e) {
        echo "Erreur d'envoi: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Effectuer un Versement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #A2C2E3, #F1A7C4);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0px 15px 40px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[readonly] {
            background: #f0f0f0;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #F1A7C4;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn:hover {
            background: #A2C2E3;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
    <script>
        function setMatricule(value) {
            document.getElementById('matricule').value = value;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Effectuer un Versement</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="versement.php">
        <div class="form-group">
            <label for="etudiant">Étudiant</label>
            <select id="etudiant" onchange="setMatricule(this.value)">
                <option value="">Sélectionnez un étudiant</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['matricule']; ?>">
                        <?php echo $student['matricule'] . ' - ' . $student['nom'] . ' ' . $student['prenom']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="matricule">Matricule</label>
            <input type="text" id="matricule" name="matricule" readonly>
        </div>
        <div class="form-group">
            <label for="montant">Montant à verser</label>
            <input type="number" id="montant" name="montant" required>
        </div>
        <div class="form-group">
            <label for="date_versement">Date de versement</label>
            <input type="text" id="date_versement" name="date_versement" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
        </div>
        <button type="submit" class="btn">Effectuer le paiement</button>
    </form>
</div>
</body>
</html>
