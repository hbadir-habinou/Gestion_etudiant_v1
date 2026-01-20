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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_cours = $_POST['nom_cours'];
    $description = $_POST['description'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_path = "../cours/" . strtolower($niveau) . "/" . $file_name;

    // Vérifier si le fichier a été téléchargé avec succès
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Insérer les informations du cours dans la base de données
        $query = "INSERT INTO $table_cours (matricule_enseignant, nom_prenom, nom_cours, description) VALUES (:matricule, :nom_prenom, :nom_cours, :description)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':matricule', $login);
        $stmt->bindParam(':nom_prenom', $nom_cours);
        $stmt->bindParam(':nom_cours', $file_name);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        header('Location: listecours.php');
        exit();
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Cours</title>
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group button {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s;
            display: block;
            width: 100%;
            cursor: pointer;
        }

        .form-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
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
            <h2 class="text-center mb-4">Ajouter un Cours</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nom_cours"><i class="bi bi-book"></i> Nom du Cours</label>
                    <input type="text" id="nom_cours" name="nom_cours" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description"><i class="bi bi-card-text"></i> Description</label>
                    <textarea id="description" name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="file"><i class="bi bi-file-earmark"></i> Fichier du Cours</label>
                    <input type="file" id="file" name="file" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
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