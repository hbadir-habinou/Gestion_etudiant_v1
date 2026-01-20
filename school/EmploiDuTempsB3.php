<?php
require __DIR__ . '/autoloading/autoload.php';

use App\Database1;
use App\EmploiTemps;

$database = new Database1();
$db = $database->getConnection();

// Obtenir les dates de la semaine courante
$monday = date('Y-m-d', strtotime('monday this week'));
$dates = array();
for($i = 0; $i < 6; $i++) {
    $dates[] = date('d/m/Y', strtotime($monday . ' +' . $i . ' days'));
}

// Récupérer l'emploi du temps actuel
$query = "SELECT * FROM emploi_b3 ORDER BY id DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$emploi = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour séparer les cours du matin et de l'après-midi
function separerCours($cours) {
    if (empty($cours)) return ['', ''];
    $parties = explode('|', $cours);
    return [
        isset($parties[0]) ? $parties[0] : '',
        isset($parties[1]) ? $parties[1] : ''
    ];
}

// Récupérer les cours pour chaque jour
$jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$emploiDuTemps = [];
foreach ($jours as $jour) {
    $emploiDuTemps[$jour] = separerCours($emploi[$jour] ?? '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emploi du temps B3 - CampusFlow</title>
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

        .table-card {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .main-content table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .main-content table th,
        .main-content table td {
            padding: 15px;
            text-align: left;
            border: none;
            border-bottom: 1px solid #eee;
        }

        .main-content table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
        }

        .main-content table tr:last-child td {
            border-bottom: none;
        }

        .main-content table img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .main-content button {
            border: none;
            padding: 5px 10px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 2px;
        }

        .main-content button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .main-content .btn-add {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            width: 200px;
            color: white;
            padding: 10px 20px;
        }

        .main-content .btn-edit {
            background: linear-gradient(145deg, #4caf50, #81c784);
            color: white;
        }

        .main-content .btn-delete {
            background: linear-gradient(145deg, #f44336, #e57373);
            color: white;
        }

        .main-content .btn-info {
            background: linear-gradient(145deg, #ff9800, #ffb74d);
            color: white;
        }

        .add-button-container {
            text-align: left;
            margin-bottom: 20px;
        }

        .main-content table tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .timetable-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .timetable-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        .week-dates {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }

        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        .timetable th {
            background: #66a6ff;
            color: white;
            padding: 15px;
            font-weight: 600;
            border: 1px solid #ddd;
        }

        .timetable td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .time-slot {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .lunch-break {
            background: #fff3e0;
            color: #f57c00;
            font-style: italic;
            text-align: center;
            padding: 10px;
        }

        .btn-modify, .btn-new {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-modify {
            background: #4CAF50;
            margin-right: 10px;
        }

        .btn-new {
            background: #2196F3;
        }

        .btn-modify:hover, .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .course {
            background: #e3f2fd;
            padding: 5px;
            border-radius: 4px;
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
        <div class="timetable-card">
            <div class="timetable-header">
                <div class="week-dates">
                    Semaine du <?php echo $dates[0]; ?> au <?php echo $dates[4]; ?>
                </div>
                <div>
                    <button class="btn-modify" onclick="window.location.href='modifier_emploi.php'">
                        Modifier l'emploi du temps
                    </button>
                    <button class="btn-new" onclick="window.location.href='ajouter_emploi.php?classe=B3'">
                        Nouveau planning
                    </button>
                </div>
            </div>

            <table class="timetable">
                <thead>
                    <tr>
                        <th>Horaires</th>
                        <th>Lundi<br><?php echo $dates[0]; ?></th>
                        <th>Mardi<br><?php echo $dates[1]; ?></th>
                        <th>Mercredi<br><?php echo $dates[2]; ?></th>
                        <th>Jeudi<br><?php echo $dates[3]; ?></th>
                        <th>Vendredi<br><?php echo $dates[4]; ?></th>
                        <th>Samedi<br><?php echo $dates[5]; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cours du matin -->
                    <tr>
                        <td class="time-slot">8h30 - 12h30</td>
                        <?php foreach ($jours as $jour): ?>
                            <td class="course"><?php echo $emploiDuTemps[$jour][0] ?: ''; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <!-- Pause déjeuner -->
                    <tr>
                        <td colspan="7" class="lunch-break">
                            Pause déjeuner (12h30 - 13h30)
                        </td>
                    </tr>
                    <!-- Cours de l'après-midi -->
                    <tr>
                        <td class="time-slot">13h30 - 17h30</td>
                        <?php foreach ($jours as $jour): ?>
                            <td class="course"><?php echo $emploiDuTemps[$jour][1] ?: ''; ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = this.nextElementSibling;
                const allMenus = document.querySelectorAll('.dropdown-menu');
                
                // Ferme tous les autres menus
                allMenus.forEach(m => {
                    if (m !== menu) {
                        m.style.display = 'none';
                    }
                });

                // Bascule l'affichage du menu actuel
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>