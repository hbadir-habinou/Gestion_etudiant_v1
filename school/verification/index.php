<?php
require_once 'vendor/autoload.php'; // Charge l'autoloader de Composer
require_once '../classes/db_connect.php';
require '../extractor_pdf/fpdf.php';
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use Smalot\PdfParser\Parser; // Importe la bibliothèque PDFParser
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fonction pour générer une signature numérique
function generateSignature($matricule, $nomPrenom, $notes, $moyenne, $rank) {
    $dataToHash = $matricule . $nomPrenom . implode(',', $notes) . $moyenne . $rank;
    return hash('sha256', $dataToHash);
}

// Fonction pour extraire les données du PDF
function extractDataFromPDF($filePath) {
    $parser = new Parser();
    try {
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        // Extraire les données du PDF
        preg_match('/MATRICULE: (\d+)/', $text, $matriculeMatches);
        preg_match('/NOM ET PRENOM: ([^\n]+)/', $text, $nomPrenomMatches);
        preg_match_all('/([A-Za-zÀ-ÿ]+): (\d+\.\d+)/', $text, $notesMatches);

        if (empty($matriculeMatches) || empty($nomPrenomMatches) || empty($notesMatches)) {
            return null;
        }

        $matricule = $matriculeMatches[1];
        $nomPrenom = $nomPrenomMatches[1];
        $notes = array_combine($notesMatches[1], $notesMatches[2]);

        // Extraire la moyenne générale
        preg_match('/MOYENNE GENERALE: (\d+\.\d+)/', $text, $moyenneMatches);
        $moyenne = $moyenneMatches[1];

        // Extraire le rang
        preg_match('/RANG: (\d+)/', $text, $rangMatches);
        $rank = $rangMatches[1];

        return [
            'matricule' => $matricule,
            'nomPrenom' => $nomPrenom,
            'notes' => $notes,
            'moyenne' => $moyenne,
            'rank' => $rank
        ];
    } catch (Exception $e) {
        return null;
    }
}

// Traitement du formulaire de génération de signature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    if ($_FILES['pdf_file']['type'] !== 'application/pdf') {
        $message = "Veuillez téléverser un fichier PDF valide.";
    } else {
        $uploadedFile = $_FILES['pdf_file']['tmp_name'];
        $data = extractDataFromPDF($uploadedFile);

        if ($data) {
            $signature = generateSignature($data['matricule'], $data['nomPrenom'], $data['notes'], $data['moyenne'], $data['rank']);
            $message = "Signature générée avec succès.";
        } else {
            $message = "Impossible d'extraire les données du fichier PDF.";
        }
    }
}

// Traitement du formulaire de vérification de signature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_signature'])) {
    $db = new Database1();
    $conn = $db->getConnection();

    $signature = $_POST['verify_signature'];

    // Vérifier la signature dans la base de données
    $query = "SELECT matricule_etudiant, nom, prenom FROM releve_signature WHERE signature = :signature";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':signature', $signature);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $isAuthentic = true;
        $verificationMessage = "Le fichier est authentique.";
    } else {
        $verificationMessage = "Le fichier n'est pas authentique ou a été modifié.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Plateforme de gestion des relevés</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Style personnalisé -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .upload-box {
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .upload-box:hover {
            background-color: #e9f5ff;
        }
        .upload-box i {
            font-size: 50px;
            color: #007bff;
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        .result-box {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            color: #333;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3><i class="fas fa-file-signature"></i> Plateforme de gestion des relevés</h3>
                    </div>
                    <div class="card-body">
                        <!-- Onglets -->
                        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="generate-tab" data-bs-toggle="tab" data-bs-target="#generate" type="button" role="tab" aria-controls="generate" aria-selected="true">
                                    <i class="fas fa-file-upload"></i> Générer une signature
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="verify-tab" data-bs-toggle="tab" data-bs-target="#verify" type="button" role="tab" aria-controls="verify" aria-selected="false">
                                    <i class="fas fa-check-circle"></i> Vérifier une signature
                                </button>
                            </li>
                        </ul>

                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="myTabContent">
                            <!-- Onglet Générer une signature -->
                            <div class="tab-pane fade show active" id="generate" role="tabpanel" aria-labelledby="generate-tab">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="upload-box" onclick="document.getElementById('pdf_file').click()">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p class="mb-0">Cliquez ici pour téléverser un fichier PDF</p>
                                        <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" hidden required>
                                    </div>
                                    <div class="text-center mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle"></i> Générer la signature
                                        </button>
                                    </div>
                                </form>

                                <?php if (isset($message)): ?>
                                    <div class="result-box mt-4">
                                        <h4 class="text-center">
                                            <?php if (isset($signature)): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php endif; ?>
                                            <?= $message ?>
                                        </h4>
                                        <?php if (isset($signature)): ?>
                                            <hr>
                                            <p class="text-center">
                                                <strong>Signature numérique :</strong><br>
                                                <input type="text" class="form-control text-center" value="<?= $signature ?>" readonly>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Onglet Vérifier une signature -->
                            <div class="tab-pane fade" id="verify" role="tabpanel" aria-labelledby="verify-tab">
                                <form method="post">
                                    <div class="form-group">
                                        <label for="verify_signature">Collez la signature numérique :</label>
                                        <input type="text" class="form-control" id="verify_signature" name="verify_signature" required>
                                    </div>
                                    <div class="text-center mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle"></i> Vérifier
                                        </button>
                                    </div>
                                </form>

                                <?php if (isset($verificationMessage)): ?>
                                    <div class="result-box mt-4">
                                        <h4 class="text-center">
                                            <?php if ($isAuthentic): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php endif; ?>
                                            <?= $verificationMessage ?>
                                        </h4>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>