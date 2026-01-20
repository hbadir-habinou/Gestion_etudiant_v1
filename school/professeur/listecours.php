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
$niveau = substr($login, 0, 2);
$table_cours = "cours_" . strtolower($niveau);

$db = new Database1();
$conn = $db->getConnection();

$query = "SELECT * FROM $table_cours WHERE matricule_enseignant = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $login);
$stmt->execute();
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Cours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e3d59;
            --secondary-color: #2a4d6e;
            --light-blue: #e6f0f9;
            --dark-gray: #424242;
            --light-pink: #FCE4EC;
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
            max-width: 800px;
            margin: 2rem auto;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .course-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-top: 20px;
        }

        .course-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }

        .course-table td {
            padding: 15px;
            background-color: white;
            transition: transform 0.2s ease;
        }

        .course-table tr td:first-child {
            border-radius: 8px 0 0 8px;
        }

        .course-table tr td:last-child {
            border-radius: 0 8px 8px 0;
        }

        .course-table tr:hover td {
            background-color: var(--light-pink);
            transform: scale(1.01);
        }

        .add-course-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .add-course-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
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
        <div class="card">
            <h2><i class="bi bi-book-half"></i> Liste des Cours</h2>
            
            <table class="course-table">
                <thead>
                    <tr>
                        <th><i class="bi bi-book"></i> Nom du Cours</th>
                        <th><i class="bi bi-info-circle"></i> Description</th>
                        <th><i class="bi bi-eye"></i> Prévisualisation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cours as $cour): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cour['nom_prenom']); ?></td>
                            <td><?php echo htmlspecialchars($cour['description']); ?></td>
                            <td>
                                <?php if (!empty($cour['nom_cours'])): ?>
                                    <a href="../cours/<?= strtolower($niveau) . "/" . $cour['nom_cours']; ?>" class="text-decoration-none">
                                        <i class="bi bi-file-earmark-text"></i> <?= $cour['nom_cours']; ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="button" class="add-course-btn" onclick="window.location.href='ajouter_cours.php'">
                <i class="bi bi-plus-circle"></i> Ajouter un Cours
            </button>
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