<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\Database1;
use App\ProfessorAccount;
require('../extractor_pdf/fpdf.php');

if (!isset($_SESSION['matricule_enseignant']) || empty($_SESSION['matricule_enseignant'])) {
    header('Location: ../../index.php');
    exit();
}

$matricule_enseignant = htmlspecialchars($_SESSION['matricule_enseignant']);

try {
    $database = new Database1();
    $conn = $database->getConnection();
    $query = "SELECT * FROM prof WHERE matricule_enseignant = :matricule_enseignant";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':matricule_enseignant', $matricule_enseignant);
    $stmt->execute();
    $teacherInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacherInfo) {
        die("Erreur : Aucune information trouvée pour le matricule : " . htmlspecialchars($matricule_enseignant));
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

function generateTeacherCard($matricule_enseignant, $NomPrenom, $adresse_mail, $date_naissance, $image_path)
{
    $pdf = new FPDF('P', 'mm', array(210, 297));
    $pdf->AddPage();
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(0, 0, 210, 297, 'F');
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 40, 'Carte de l\'enseignant', 0, 1, 'C');
    
    $startX = 30;
    $startY = 80;
    
    if (!empty($image_path) && file_exists('../../photos_enseignant/' . $image_path)) {
        $pdf->Image('../photos_enseignant/' . $image_path, 85, $startY, 40, 40);
    } else {
        $pdf->Image('../images_pages/logo_enseignant.png', 85, $startY, 40, 40);
    }
    
    $pdf->SetY($startY + 60);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule_enseignant, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $NomPrenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Email: ' . $adresse_mail, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de Naissance: ' . $date_naissance, 0, 1, 'C');
    
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(30, $startY + 140, 180, $startY + 140);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="carte_enseignant_' . $matricule_enseignant . '.pdf"');
    $pdf->Output('D');
    exit();
}

if (isset($_POST['export'])) {
    generateTeacherCard(
        $teacherInfo['matricule_enseignant'],
        $teacherInfo['NomPrenom'],
        $teacherInfo['adresse_mail'],
        $teacherInfo['date_naissance'],
        $teacherInfo['image_path']
    );
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte de l'enseignant</title>
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
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            width: 280px;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 1.5rem 1rem;
            color: white;
            transition: all 0.3s;
            z-index: 1001;
            overflow-y: auto;
        }

        .admin-profile {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .nav-item {
            width: 100%;
            margin-bottom: 0.5rem;
            list-style: none;
            position: relative;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .dropdown-menu {
            padding: 0.5rem 0;
            margin: 0;
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            list-style: none;
            position: static;
            width: 100%;
            border-radius: 10px;
            margin-left: 2rem;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: block;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .dropdown-menu a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            flex-grow: 1;
            transition: all 0.3s;
        }

        .card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            padding: 2rem;
            max-width: 600px;
            margin: 2rem auto;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1.5rem;
            display: block;
            border: 3px solid var(--primary-color);
        }

        .export-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s;
            display: block;
            width: fit-content;
            margin: 2rem auto 0;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
        }

        .sidebar-toggle {
            position: fixed;
            left: 1rem;
            top: 1rem;
            z-index: 1002;
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <button class="btn btn-primary sidebar-toggle">
        <i class="bi bi-list"></i>
    </button>

    <nav class="sidebar">
        <div class="admin-profile">
            <img src="../images_pages/logo_enseignant.png" alt="Enseignant" class="admin-avatar">
            <h5 class="mb-2">Enseignant</h5>
            <p class="text-light mb-0">Campus Manager</p>
        </div>

        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-mortarboard"></i> Gestion des notes
                </a>
                <ul class="dropdown-menu">
                    <li><a href="notes_cc.php">Notes cc</a></li>
                    <li><a href="notes_exam.php">Notes exam</a></li>
                    <li><a href="notes_tp.php">Notes tp</a></li>
                    <li><a href="assign_notes.php">Atribuer les notes</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-book"></i> Cours
                </a>
                <ul class="dropdown-menu">
                    <li><a href="listecours.php">Liste des cours</a></li>
                    <li><a href="ajouter_cours.php">Ajouter un cour</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="cardProf.php">
                    <i class="bi bi-person-workspace"></i> Mes informations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="card">
            <h2 class="text-center mb-4">Ma Carte Enseignant</h2>
            <img src="../photos_enseignant/<?php echo htmlspecialchars($teacherInfo['image_path']); ?>" 
                 alt="Photo de l'enseignant" 
                 onerror="this.src='../images_pages/logo_enseignant.png'">
            
            <div class="info-group mb-3">
                <p class="mb-2"><i class="bi bi-person-badge me-2"></i><strong>Matricule:</strong> 
                    <?php echo htmlspecialchars($teacherInfo['matricule_enseignant']); ?>
                </p>
                <p class="mb-2"><i class="bi bi-person me-2"></i><strong>Nom:</strong> 
                    <?php echo htmlspecialchars($teacherInfo['NomPrenom']); ?>
                </p>
                <p class="mb-2"><i class="bi bi-envelope me-2"></i><strong>Email:</strong> 
                    <?php echo htmlspecialchars($teacherInfo['adresse_mail']); ?>
                </p>
                <p class="mb-2"><i class="bi bi-calendar me-2"></i><strong>Date de Naissance:</strong> 
                    <?php echo htmlspecialchars($teacherInfo['date_naissance']); ?>
                </p>
            </div>

            <form method="POST">
                <button type="submit" name="export" class="export-btn">
                    <i class="bi bi-download me-2"></i>Télécharger ma carte
                </button>
            </form>
        </div>
    </main>

    <script>
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                const isExpanded = menu.classList.contains('show');
                
                document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                    }
                });
                
                menu.classList.toggle('show', !isExpanded);
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        document.querySelector('.sidebar-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        function handleResize() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        }

        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>