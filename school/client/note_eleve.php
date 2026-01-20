<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
require('../extractor_pdf/fpdf.php'); // Assurez-vous que la bibliothèque FPDF est correctement incluse.

if (!isset($_SESSION['matricule'])) {
    header('Location: ../../index.php');
    exit();
}
use App\StudentAccount;
use App\Database1;

$matricule = $_SESSION['matricule'];

// Récupération des informations de l'étudiant à partir de la base de données
$database = new Database1();
$conn = $database->getConnection();
$query = "SELECT * FROM etudiant_infos WHERE matricule = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $matricule);
$stmt->execute();
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$studentInfo) {
    echo "Étudiant non trouvé.";
    exit();
}

$classe = $studentInfo['classe'];
$table_notes = "note_" . strtolower($classe);

// Récupération des notes de l'étudiant
$query = "SELECT * FROM $table_notes WHERE matricule_etudiant = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $matricule);
$stmt->execute();
$notes = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour générer le relevé de notes en PDF et l'envoyer au navigateur
function generateReleveNotes($matricule, $nom, $prenom, $classe, $notes) {
    // Initialiser FPDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Style du relevé de notes
    $pdf->SetFillColor(240, 240, 255); // Fond clair
    $pdf->SetDrawColor(200, 200, 200); // Bordure douce
    $pdf->SetTextColor(50, 50, 50);

    // Conteneur du relevé de notes
    $pdf->SetXY(20, 40);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(170, 10, 'Relevé de Notes', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prénom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Ln(10);

    // Notes de l'étudiant
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Notes:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    foreach ($notes as $matiere => $note) {
        if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
            $pdf->Cell(0, 10, ucwords(str_replace('_', ' ', $matiere)) . ": " . $note, 0, 1, 'L');
        }
    }

    // Ajout d'une bordure autour du relevé de notes
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF directement dans le navigateur
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="releve_notes_' . $matricule . '.pdf"'); // Modifié pour le téléchargement
    $pdf->Output('D'); // 'D' pour forcer le téléchargement

    exit();
}

// Vérifiez si le bouton "Exporter" a été cliqué
if (isset($_POST['export'])) {
    generateReleveNotes(
        $studentInfo['matricule'],
        $studentInfo['nom'],
        $studentInfo['prenom'],
        $studentInfo['classe'],
        $notes
    );
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(160, 222, 161),rgb(212, 234, 244));
            margin: 0;
            padding: 0;
            color: #333;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            float: left;
            display: block;
            color: #333;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar a.active, .navbar a:hover {
            background-color:rgb(232, 232, 232);
            color: #333;
            border-radius: 5px;
        }
        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 80px auto;
            text-align: center;
            position: relative;
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 1s forwards;
        }
        .card:hover {
            transform: scale(1.03);
            box-shadow: 0px 15px 35px rgba(0, 0, 0, 0.2);
        }
        .card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card img:hover {
            transform: scale(1.1);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }
        .logout-btn {
            background-color: #e0e0e0;
            color: #333;
            padding: 8px 15px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            position: absolute;
            top: 20px;
            right: 20px;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .logout-btn:hover {
            background-color: #f1f1f1;
            transform: scale(1.05);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }
        .export-btn {
            background-color: #e0e0e0;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            display: inline-block;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .export-btn:hover {
            background-color: #f1f1f1;
            transform: scale(1.05);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        footer {
            background-color: rgba(255, 255, 255, 0.3);
            color: #666;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="card.php" class="active">Mes infos</a>
        <a href="chatbot.php">Chatbot</a>
        <a href="note_eleve.php">Mes Notes</a>
        <a href="mes_cours.php">Mes Cours</a>
    </div>
    <div class="card">
        <button class="logout-btn" onclick="location.href='logout.php'">Se déconnecter</button>
        <h2>Relevé de Notes</h2>
        <p><strong>Matricule:</strong> <?php echo $studentInfo['matricule']; ?></p>
        <p><strong>Nom:</strong> <?php echo $studentInfo['nom']; ?></p>

        <h3>Notes:</h3>
        <?php if ($notes): ?>
            <table>
                <tr>
                    <th>Matière</th>
                    <th>Note</th>
                </tr>
                <?php foreach ($notes as $matiere => $note): ?>
                    <?php if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])): ?>
                        <tr>
                            <td><?php echo ucwords(str_replace('_', ' ', $matiere)); ?></td>
                            <td><?php echo $note; ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Aucune note disponible pour le moment.</p>
        <?php endif; ?>

        <form method="POST" action="">
            <button type="submit" name="export" class="export-btn">Exporter le relevé de notes</button>
        </form>
    </div>
    <footer>
        <p>&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>
</body>
</html>
