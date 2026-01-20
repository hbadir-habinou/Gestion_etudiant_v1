<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
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

        .sidebar .nav {
            margin-top: 2rem;
            padding: 0;
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

        .dropdown-menu li {
            margin-bottom: 0.5rem;
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
            margin-top: 20px;
            padding: 2rem;
            flex-grow: 1;
            transition: all 0.3s;
        }

        .card {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            width: 400px;
            margin: auto;
            text-align: center;
        }

        .card h3 {
            margin-bottom: 15px;
            font-size: 1.2em;
            color: #333;
        }

        .credentials {
            font-size: 1em;
            line-height: 1.6;
            color: #555;
        }

        .credentials span {
            display: inline-block;
            margin: 10px 0;
            font-weight: bold;
            color: #4a4a4a;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="admin-profile">
            <img src="images_pages/logo_enseignant.png" alt="Admin" class="admin-avatar">
            <h5 class="mb-2">Administrateur</h5>
            <p class="text-light mb-0">Campus Manager</p>
        </div>

        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-mortarboard"></i> Étudiants
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EtudiantListe.php">Liste des Étudiants</a></li>
                    <li><a href="ajouter_etudiant.php">Ajouter un étudiant</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-person-workspace"></i> Professeurs
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ProfesseurListe.php">Liste des Professeurs</a></li>
                    <li><a href="ajouter_professeur.php">Ajouter un Professeur</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-book"></i> Matières
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ListeMatiere.php">Liste des Matières</a></li>
                    <li><a href="ajouter_matiere.php">Ajouter une Attribution</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-cash-coin"></i> Finances
                </a>
                <ul class="dropdown-menu">
                    <li><a href="VersementListe.php">Liste des versements</a></li>
                    <li><a href="versement.php">Effectuer un versement</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-card-checklist"></i> Notes
                </a>
                <ul class="dropdown-menu">
                    <li><a href="noteb1.php">Notes B1</a></li>
                    <li><a href="noteb2.php">Notes B2</a></li>
                    <li><a href="noteb3.php">Notes B3</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-calendar-week"></i> Emploi du temps
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EmploiDuTempsB1.php">Emploi du temps B1</a></li>
                    <li><a href="EmploiDuTempsB2.php">Emploi du temps B2</a></li>
                    <li><a href="EmploiDuTempsB3.php">Emploi du temps B3</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="graph.php">
                    <i class="bi bi-graph-up"></i> Graphiques
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="card">
            <h3>Informations d'Authentification</h3>
            <div class="credentials">
                <p><span>Login :</span> admin</p>
                <p><span>Mot de passe :</span> admin1234</p>
            </div>
        </div>
    </main>

    <script>
        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>