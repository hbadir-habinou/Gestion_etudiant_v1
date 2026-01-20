<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
require('../extractor_pdf/fpdf.php'); // Assurez-vous que la bibliothèque FPDF est correctement incluse.

use App\StudentAccount;
use App\Database1;

if (!isset($_SESSION['matricule'])) {
    header('Location: ../../windex.php');
    exit();
}

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
$table_notes = "exam_" . strtolower($classe);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7986CB;
            --secondary-color: #9FA8DA;
            --accent-color: #5C6BC0;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #E8EAF6, #C5CAE9);
            min-height: 100vh;
        }

        .side-bar {
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 2rem 1rem;
            color: white;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-section {
            text-align: center;
            padding: 1rem 0;
        }

        .admin-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.2);
            padding: 3px;
            background: white;
            transition: transform 0.3s ease;
        }

        .admin-icon:hover {
            transform: scale(1.1);
        }

        .menu {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }

        .menu-item {
            margin-bottom: 0.5rem;
            position: relative;
        }

        .menu-link {
            color: white;
            text-decoration: none;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .menu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
            color: white;
        }

        .dropdown-menu {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-left: 2rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            display: none;
            padding: 0.5rem 0;
            height: 0;
            opacity: 0;
            transition: height 0.3s ease, opacity 0.3s ease;
        }

        .dropdown-menu.show {
            display: block;
            height: auto;
            opacity: 1;
        }

        .dropdown-item {
            color: white;
            padding: 0.8rem 1rem;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .menu-icon {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .card-container {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .student-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
            transition: transform 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-5px);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--primary-color);
            margin: 0 auto 2rem;
            display: block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-image:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.5);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(5px);
        }

        footer {
            margin-left: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.1);
            color: #333;
            padding: 1rem;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        @media (max-width: 768px) {
            .side-bar {
                transform: translateX(-100%);
            }

            .card-container {
                margin-left: 0;
            }

            footer {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <aside class="side-bar">
        <div class="admin-section">
            <img src="../images_pages/test2.png" alt="Admin" class="admin-icon">
            <h4 class="mt-3 mb-4">Etudiant</h4>
        </div>
        <ul class="menu">
            <li class="menu-item">
                <a href="#" class="menu-link dropdown-toggle">
                    <i class="fas fa-graduation-cap menu-icon"></i> Mes notes
                </a>
                <div class="dropdown-menu">
                    <a href="note_eleve_cc.php" class="dropdown-item">Notes cc</a>
                    <a href="note_eleve_exam.php" class="dropdown-item">Notes exam</a>
                    <a href="note_eleve_tp.php" class="dropdown-item">Notes tp</a>
                </div>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link dropdown-toggle">
                    <i class="fas fa-book menu-icon"></i> Mes cours
                </a>
                <div class="dropdown-menu">
                    <a href="mes_cours.php" class="dropdown-item">Liste des cours</a>
                </div>
            </li>
            <li class="menu-item">
                <a href="card.php" class="menu-link">
                    <i class="fas fa-user menu-icon"></i> Mes informations
                </a>
            </li>
            <li class="menu-item">
                <a href="chatbot.php" class="menu-link">
                    <i class="fas fa-robot menu-icon"></i> ChatBot
                </a>
            </li>
            <li class="menu-item">
                <a href="logout.php" class="menu-link">
                    <i class="fas fa-sign-out-alt menu-icon"></i> Déconnexion
                </a>
            </li>
        </ul>
    </aside>

    <div class="card-container">
        <div class="student-card">
            <div class="text-end mb-4">
                <button class="btn btn-custom" onclick="location.href='logout.php'">
                    <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                </button>
            </div>

            <h2 class="text-center mb-4">
                <i class="fas fa-file-alt me-2"></i>Relevé de Notes
            </h2>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-item">
                        <i class="fas fa-fingerprint me-2"></i>
                        <strong>Matricule:</strong> <?php echo $studentInfo['matricule']; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <i class="fas fa-user me-2"></i>
                        <strong>Nom:</strong> <?php echo $studentInfo['nom']; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <i class="fas fa-user me-2"></i>
                        <strong>Prénom:</strong> <?php echo $studentInfo['prenom']; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <strong>Classe:</strong> <?php echo $studentInfo['classe']; ?>
                    </div>
                </div>
            </div>

            <h3 class="text-center mt-4 mb-3">Notes:</h3>
            <?php if ($notes): ?>
                <table class="table table-bordered table-hover mx-auto">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notes as $matiere => $note): ?>
                            <?php if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])): ?>
                                <tr>
                                    <td><?php echo ucwords(str_replace('_', ' ', $matiere)); ?></td>
                                    <td><?php echo $note; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">Aucune note disponible pour le moment.</p>
            <?php endif; ?>

            <form method="POST" action="" class="text-center mt-4">
                <button type="submit" name="export" class="btn btn-custom">
                    <i class="fas fa-download me-2"></i>Exporter le relevé de notes
                </button>
            </form>
        </div>
    </div>

    <footer>
        <p class="mb-0">&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdownMenu = e.currentTarget.nextElementSibling;
                const menuItem = e.currentTarget.closest('.menu-item');
                const allDropdowns = document.querySelectorAll('.dropdown-menu');

                allDropdowns.forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('show');
                        const parentItem = menu.closest('.menu-item');
                        if (parentItem) {
                            parentItem.style.marginBottom = '0.5rem';
                        }
                    }
                });

                dropdownMenu.classList.toggle('show');

                if (dropdownMenu.classList.contains('show')) {
                    const dropdownHeight = dropdownMenu.scrollHeight;
                    menuItem.style.marginBottom = `${dropdownHeight + 10}px`;
                } else {
                    menuItem.style.marginBottom = '0.5rem';
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.menu-item')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                    const parentItem = menu.closest('.menu-item');
                    if (parentItem) {
                        parentItem.style.marginBottom = '0.5rem';
                    }
                });
            }
        });
    </script>
</body>
</html>
