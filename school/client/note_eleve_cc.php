<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
require('../extractor_pdf/fpdf.php');
use App\StudentAccount;
use App\Database1;

if (!isset($_SESSION['matricule'])) {
    header('Location: ../../index.php');
    exit();
}

$matricule = $_SESSION['matricule'];

// Récupération des informations de l'étudiant
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

// Récupération des notes
$query = "SELECT * FROM $table_notes WHERE matricule_etudiant = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $matricule);
$stmt->execute();
$notes = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour générer le PDF
function generateReleveNotes($matricule, $nom, $prenom, $classe, $notes) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    $pdf->SetFillColor(240, 240, 255);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetTextColor(50, 50, 50);

    $pdf->SetXY(20, 40);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(170, 10, 'Relevé de Notes', 0, 1, 'C', true);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prénom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Notes:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    foreach ($notes as $matiere => $note) {
        if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
            $pdf->Cell(0, 10, ucwords(str_replace('_', ' ', $matiere)) . ": " . $note, 0, 1, 'L');
        }
    }

    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="releve_notes_' . $matricule . '.pdf"');
    $pdf->Output('D');
    exit();
}

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
        }

        .dropdown-menu {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-left: 2rem;
            padding: 0.5rem 0;
            display: none;
            height: 0;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .dropdown-menu.show {
            display: block;
            height: auto;
            opacity: 1;
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

        .info-item {
            background: rgba(255, 255, 255, 0.5);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
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
            .card-container, footer {
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
            <button class="btn btn-primary conseil-button mb-3" id="conseilButton">
                <i class="fas fa-lightbulb me-2"></i>Recevoir des conseils
            </button>

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

    <!-- Modal pour les conseils -->
    <div class="modal fade" id="conseilModal" tabindex="-1" aria-labelledby="conseilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="conseilModalLabel">
                        <i class="fas fa-lightbulb me-2"></i>Conseils Personnalisés
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="conseilText" class="p-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div></div>
        </div>
    </div>

    <footer>
        <p class="mb-0">&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const conseilButton = document.getElementById('conseilButton');
            const conseilModal = new bootstrap.Modal(document.getElementById('conseilModal'));
            const conseilText = document.getElementById('conseilText');

            // Gestion des menus déroulants
            document.querySelectorAll('.dropdown-toggle').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                });
            });

            // Gestion du bouton de conseil
            conseilButton.addEventListener('click', function() {
                const notes = {};
                document.querySelectorAll('table tbody tr').forEach(row => {
                    const matiere = row.querySelector('td:first-child').textContent.trim();
                    const note = row.querySelector('td:nth-child(2)').textContent.trim();
                    notes[matiere] = note;
                });

                const nom = "<?php echo $studentInfo['nom']; ?>";
                const prenom = "<?php echo $studentInfo['prenom']; ?>";

                // Afficher un indicateur de chargement
                conseilText.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Génération des conseils en cours...</div>';
                conseilModal.show();

                // Appel à l'API Gemini
                fetchConseils(notes, nom, prenom)
                    .then(conseil => {
                        conseilText.innerHTML = formatConseil(conseil);
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des conseils:', error);
                        conseilText.innerHTML = '<div class="alert alert-danger">Une erreur est survenue lors de la génération des conseils. Veuillez réessayer.</div>';
                    });
            });
        });

        function fetchConseils(notes, nom, prenom) {
            const GKey = "AIzaSyDcU9lGwX3mrwmuIKFL89G5LKVhh9yOOlQ";
            let notesDescription = "";
            for (const matiere in notes) {
                notesDescription += `${matiere}: ${notes[matiere]}/20, `;
            }
            notesDescription = notesDescription.trim().slice(0, -1);

            const prompt = `En tant que conseiller d'orientation professionnel, analyse les résultats suivants pour l'étudiant ${nom} ${prenom}.

Notes: ${notesDescription}

Rédige des conseils professionnels, concis et sans partie à remplir, destinés à l'étudiant. elle sera adressé à l'étudiant, utilise un langage professionnel. Pour chaque matière, dis à l'étudiant ce qu'il doit faire pour s'améliorer.`;

            const url = `https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=${GKey}`;

            const data = {
                contents: [
                    { parts: [{ text: prompt }] }
                ]
            };

            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                return result.candidates[0].content.parts[0].text ?? "Une erreur est survenue lors de la génération du conseil d'orientation.";
            });
        }

        function formatConseil(conseil) {
            // Remplacer les doubles astérisques par du texte en gras
            let formattedText = conseil.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Remplacer les simples astérisques par des sauts de ligne
            formattedText = formattedText.replace(/\*(.*?)(?=\*|$)/g, '<br>$1');
            
            // Nettoyer les astérisques restants
            formattedText = formattedText.replace(/\*/g, '');
            
            // Nettoyer les sauts de ligne multiples
            formattedText = formattedText.replace(/<br>\s*<br>/g, '<br>');
            
            // Ajouter des classes Bootstrap pour le style
            formattedText = '<div class="conseil-content">' + formattedText + '</div>';
            
            return formattedText;
        }
    </script>
</body>
</html>