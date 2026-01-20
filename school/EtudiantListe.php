<?php
require __DIR__ . '/autoloading/autoload.php';

use App\Database1;
use App\Etudiant;

$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);
$stmt = $etudiant->read();
$num = $stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .search-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-card select,
        .search-card input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        .search-card input {
            flex-grow: 1;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 15px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slide-down 0.3s ease;
        }

        @keyframes slide-down {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-body {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 15px;
            margin-top: 15px;
        }

        .modal-body img {
            max-width: 200px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-body .details {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
        }

        .modal-body .details strong {
            color: #666;
        }

        .sidebar-toggle {
            position: fixed;
            left: 1rem;
            top: 1rem;
            z-index: 1001;
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
                margin-left: 0 !important;
            }
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-primary sidebar-toggle">
        <i class="bi bi-list"></i>
    </button>

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
        <h1>Liste des Étudiants</h1>
        <div class="search-card">
            <select id="search-column">
                <option value="id">ID</option>
                <option value="matricule">Matricule</option>
                <option value="nom">Nom</option>
                <option value="prenom">Prénom</option>
                <option value="classe">Classe</option>
                <option value="email">Email</option>
                <option value="nom_parent">Nom Parent</option>
                <option value="email_parent">Email Parent</option>
                <option value="date_naissance">Date de Naissance</option>
                <option value="solvabilite">Solvabilité</option>
            </select>
            <input type="text" id="search-value" placeholder="Rechercher..." onkeyup="filterTable()">
        </div>
        <div class="table-card">
            <div class="add-button-container">
                <button class="btn-add" onclick="window.location.href='ajouter_etudiant.php'">
                    Ajouter un Étudiant
                </button>
            </div>
            <table id="student-table">
                <tr style="background-color: #ffffff;" class="dark-border">
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-file-earmark-text"></i> Matricule</th>
                    <th><i class="bi bi-image"></i> Image</th>
                    <th><i class="bi bi-person"></i> Nom</th>
                    <th><i class="bi bi-person"></i> Prénom</th>
                    <th><i class="bi bi-book"></i> Classe</th>
                    <th><i class="bi bi-envelope"></i> Email</th>
                    <th><i class="bi bi-person"></i> Nom Parent</th>
                    <th><i class="bi bi-envelope"></i> Email Parent</th>
                    <th><i class="bi bi-calendar"></i> Date de Naissance</th>
                    <th><i class="bi bi-cash-coin"></i> Montant à payer</th>
                    <th><i class="bi bi-credit-card"></i> Solvabilité</th>
                    <th><i class="bi bi-gear"></i> Actions</th>
                </tr>
                <tbody id="student-table-body">
                    <?php
                    if ($num > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($row);
                    ?>
                            <tr class="dark-border">
                                <td><?php echo $id; ?></td>
                                <td><?php echo $matricule; ?></td>
                                <td>
                                    <img src='photos/<?php echo $image_path; ?>' alt='Photo'>
                                </td>
                                <td><?php echo $nom; ?></td>
                                <td><?php echo $prenom; ?></td>
                                <td><?php echo $classe; ?></td>
                                <td><?php echo $email; ?></td>
                                <td><?php echo $nom_parent; ?></td>
                                <td><?php echo $email_parent; ?></td>
                                <td><?php echo $date_naissance; ?></td>
                                <td><?php echo $montant_a_payer; ?></td>
                                <td><?php echo $solvabilite; ?></td>
                                <td class="action-buttons">
                                    <button class="btn-edit" onclick="window.location.href='modifier_etudiant.php?id=<?php echo $id; ?>'">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-delete" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')) window.location.href='supprimer_etudiant.php?id=<?php echo $id; ?>'">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn-info" onclick="showStudentDetails(
                                        '<?php echo $id; ?>',
                                        '<?php echo $matricule; ?>',
                                        '<?php echo $image_path; ?>',
                                        '<?php echo $nom; ?>',
                                        '<?php echo $prenom; ?>',
                                        '<?php echo $classe; ?>',
                                        '<?php echo $email; ?>',
                                        '<?php echo $date_naissance; ?>',
                                        '<?php echo $nom_parent; ?>',
                                        '<?php echo $email_parent; ?>',
                                        '<?php echo $solvabilite; ?>'
                                    )">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="12">Aucun étudiant trouvé</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal pour les détails de l'étudiant -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de l'Étudiant</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="modalStudentImage" src="" alt="Photo de l'étudiant">
                <div class="details">
                    <strong>Nom:</strong> <span id="modalNom"></span>
                    <strong>Prénom:</strong> <span id="modalPrenom"></span>
                    <strong>Matricule:</strong> <span id="modalMatricule"></span>
                    <strong>Classe:</strong> <span id="modalClasse"></span>
                    <strong>Email:</strong> <span id="modalEmail"></span>
                    <strong>Date de Naissance:</strong> <span id="modalDateNaissance"></span>
                    <strong>Nom Parent:</strong> <span id="modalNomParent"></span>
                    <strong>Email Parent:</strong> <span id="modalEmailParent"></span>
                    <strong>Solvabilité:</strong> <span id="modalSolvabilite"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Responsive handling
        function handleResize() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        }

        window.addEventListener('resize', handleResize);

        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                const isExpanded = menu.classList.contains('show');

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                    }
                });

                // Toggle current dropdown
                menu.classList.toggle('show', !isExpanded);
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        function showStudentDetails(id, matricule, imagePath, nom, prenom, classe, email, dateNaissance, nomParent, emailParent, solvabilite) {
            document.getElementById('modalStudentImage').src = 'photos/' + imagePath;
            document.getElementById('modalNom').textContent = nom;
            document.getElementById('modalPrenom').textContent = prenom;
            document.getElementById('modalMatricule').textContent = matricule;
            document.getElementById('modalClasse').textContent = classe;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalDateNaissance').textContent = dateNaissance;
            document.getElementById('modalNomParent').textContent = nomParent;
            document.getElementById('modalEmailParent').textContent = emailParent;
            document.getElementById('modalSolvabilite').textContent = solvabilite;

            document.getElementById('studentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('studentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Fonction pour filtrer le tableau en temps réel
        function filterTable() {
            const column = document.getElementById('search-column').value;
            const value = document.getElementById('search-value').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search.php?column=' + column + '&value=' + value, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    const response = JSON.parse(this.responseText);
                    let output = '';
                    if (response.records) {
                        response.records.forEach(record => {
                            output += `
                                <tr class="dark-border">
                                    <td>${record.id}</td>
                                    <td>${record.matricule}</td>
                                    <td><img src='photos/${record.image_path}' alt='Photo'></td>
                                    <td>${record.nom}</td>
                                    <td>${record.prenom}</td>
                                    <td>${record.classe}</td>
                                    <td>${record.email}</td>
                                    <td>${record.nom_parent}</td>
                                    <td>${record.email_parent}</td>
                                    <td>${record.date_naissance}</td>
                                    <td>${record.montant_a_payer}</td>
                                    <td>${record.solvabilite}</td>
                                    <td class="action-buttons">
                                        <button class="btn-edit" onclick="window.location.href='modifier_etudiant.php?id=${record.id}'">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn-delete" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')) window.location.href='supprimer_etudiant.php?id=${record.id}'">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button class="btn-info" onclick="showStudentDetails(
                                            '${record.id}',
                                            '${record.matricule}',
                                            '${record.image_path}',
                                            '${record.nom}',
                                            '${record.prenom}',
                                            '${record.classe}',
                                            '${record.email}',
                                            '${record.date_naissance}',
                                            '${record.nom_parent}',
                                            '${record.email_parent}',
                                            '${record.solvabilite}'
                                        )">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        output += `<tr><td colspan="12">${response.message}</td></tr>`;
                    }
                    document.getElementById('student-table-body').innerHTML = output;
                }
            }
            xhr.send();
        }
    </script>
</body>

</html>