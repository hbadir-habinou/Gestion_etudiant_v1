<?php
require_once 'fpdf.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matricule'])) {
    $matricule = $_POST['matricule'];

    // Récupérer les informations de l'étudiant
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM etudiant_infos WHERE matricule = :matricule");
    $stmt->bindParam(':matricule', $matricule);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die('Étudiant non trouvé.');
    }

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
    if (!empty($row['image_path']) && file_exists('photos/' . $row['image_path'])) {
        $pdf->Image('photos/' . $row['image_path'], 85, 55, 40, 40); // Centré
        $pdf->Ln(50);
    }

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $row['matricule'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $row['nom'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prenom: ' . $row['prenom'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $row['classe'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de Naissance: ' . $row['date_naissance'], 0, 1, 'C');

    // Ajout d'une bordure autour de la carte
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF
    $pdf->Output('D', 'carte_etudiant_' . $row['matricule'] . '.pdf'); // Téléchargement direct
    exit();
}
?>