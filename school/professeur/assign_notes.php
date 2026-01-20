<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\Database1;
use App\ProfessorAccount;

if (!isset($_SESSION['login'])) {
    header('Location: ../../index.php');
    exit();
}

$login = $_SESSION['login'];
$professorAccount = new ProfessorAccount();
$professorInfo = $professorAccount->getProfessorInfo($login);
$nom_matiere = $professorInfo['nom_matiere'];
$niveau = substr($login, 0, 2);

// Récupérer les étudiants pour les différentes tables de notes
$tables_notes = [
    "note_" . strtolower($niveau),
    "exam_" . strtolower($niveau),
    "tp_" . strtolower($niveau)
];

$db = new Database1();
$conn = $db->getConnection();

$students = [];
foreach ($tables_notes as $table_note) {
    $query = "SELECT matricule_etudiant, nom, prenom FROM $table_note";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students[$table_note] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($tables_notes as $table_note) {
        foreach ($students[$table_note] as $student) {
            $note = $_POST[$student['matricule_etudiant'] . "_" . $table_note];
            if (!empty($note)) {
                // Vérifier que la note est dans la plage autorisée
                if ($note < 0 || $note > 20) {
                    echo "La note pour l'étudiant " . htmlspecialchars($student['matricule_etudiant']) . " est invalide. Elle doit être entre 0 et 20.";
                    exit();
                }
                $updateQuery = "UPDATE $table_note SET `$nom_matiere` = :note WHERE matricule_etudiant = :matricule";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindParam(':note', $note);
                $updateStmt->bindParam(':matricule', $student['matricule_etudiant']);
                $updateStmt->execute();
            }
        }
    }
    header('Location: notes_cc.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribuer les Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
       :root {
            --primary-color: #1e3d59;
            --secondary-color: #2a4d6e;
            --light-blue: #e6f0f9;
            --dark-gray: #424242;
            --light-pink: #FCE4EC;
            --table-border-color:rgb(122, 127, 125);
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

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: 600;
            color: var(--primary-color);
            letter-spacing: 1px;
        }

        .container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            border: 1px solid var(--table-border-color);
        }

        .container table th,
        .container table td {
            padding: 12px;
            text-align: center;
            border: 1px solid var(--table-border-color);
        }

        .container table th {
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .container table tr:nth-child(even) {
            background-color: var(--light-pink);
        }

        .container table tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .input-field {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s ease-in-out;
        }

        .input-field:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .save-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .save-btn:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }

        footer {
            background-color: rgba(255, 255, 255, 0.3);
            color: #666;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 14px;
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

            .container {
                width: 95%;
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
            <img src="../images_pages/logo_enseignant.png" alt="Admin" class="admin-avatar">
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
                    <li><a href="assign_notes.php">Attribuer les notes</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-book"></i> Cours
                </a>
                <ul class="dropdown-menu">
                    <li><a href="listecours.php">Liste des cours</a></li>
                    <li><a href="ajouter_cours.php">Ajouter un cours</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cardProf.php">
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
        <div class="container">
            <h2>Attribuer les Notes</h2>
            <form method="POST" action="">
                <?php foreach ($tables_notes as $table_note): ?>
                    <h3><?php echo ucfirst(str_replace('_', ' ', $table_note)); ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Note (<?php echo $nom_matiere; ?>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students[$table_note] as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['matricule_etudiant']); ?></td>
                                    <td><?php echo htmlspecialchars($student['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                                    <td>
                                        <input type="number" name="<?php echo $student['matricule_etudiant'] . "_" . $table_note; ?>" class="input-field" placeholder="Note" min="0" max="20" required>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
                <button type="submit" class="save-btn">Sauvegarder les Notes</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>

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