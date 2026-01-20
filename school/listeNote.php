<?php
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;

$database = new Database1();
$db = $database->getConnection();

// Récupérer les notes pour chaque classe
$classes = ['B1', 'B2', 'B3'];
$notes = [];

foreach ($classes as $classe) {
    $table_notes = "note_" . strtolower($classe);
    $table_tp = "tp_" . strtolower($classe);
    $table_exam = "exam_" . strtolower($classe);

    $query_notes = "SELECT * FROM $table_notes";
    $stmt_notes = $db->prepare($query_notes);
    $stmt_notes->execute();
    $notes[$classe]['notes'] = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);

    $query_tp = "SELECT * FROM $table_tp";
    $stmt_tp = $db->prepare($query_tp);
    $stmt_tp->execute();
    $notes[$classe]['tp'] = $stmt_tp->fetchAll(PDO::FETCH_ASSOC);

    $query_exam = "SELECT * FROM $table_exam";
    $stmt_exam = $db->prepare($query_exam);
    $stmt_exam->execute();
    $notes[$classe]['exam'] = $stmt_exam->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les notes d'un étudiant
function getStudentNotes($conn, $matricule, $classe) {
    $table_notes = "note_" . strtolower($classe);
    $table_tp = "tp_" . strtolower($classe);
    $table_exam = "exam_" . strtolower($classe);

    $query_notes = "SELECT * FROM $table_notes WHERE matricule_etudiant = :matricule";
    $stmt_notes = $conn->prepare($query_notes);
    $stmt_notes->bindParam(':matricule', $matricule);
    $stmt_notes->execute();
    $notes = $stmt_notes->fetch(PDO::FETCH_ASSOC);

    $query_tp = "SELECT * FROM $table_tp WHERE matricule_etudiant = :matricule";
    $stmt_tp = $conn->prepare($query_tp);
    $stmt_tp->bindParam(':matricule', $matricule);
    $stmt_tp->execute();
    $tp = $stmt_tp->fetch(PDO::FETCH_ASSOC);

    $query_exam = "SELECT * FROM $table_exam WHERE matricule_etudiant = :matricule";
    $stmt_exam = $conn->prepare($query_exam);
    $stmt_exam->bindParam(':matricule', $matricule);
    $stmt_exam->execute();
    $exam = $stmt_exam->fetch(PDO::FETCH_ASSOC);

    return [
        'notes' => $notes,
        'tp' => $tp,
        'exam' => $exam
    ];
}

// Fonction pour calculer la moyenne générale
function calculateAverage($notes) {
    $sum = 0;
    $count = 0;
    foreach ($notes as $key => $value) {
        if (!in_array($key, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
            $sum += $value;
            $count++;
        }
    }
    return $count > 0 ? $sum / $count : 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Notes</title>
    
    <style>
        /* --- Style général --- */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f4f4f9;
        }
        /* --- Barre latérale gauche --- */
        .side-bar {
            background-color: #4a4a4a; /* Couleur sombre */
            color: white;
            width: 210px;
            padding: 20px 10px;
            position: fixed;
            top: 0;
            bottom: 0;
            height: 100vh;
            z-index: 1001;
        }
        .top-bar .logo img {
            margin-left: 260px; /* Ajuste la valeur selon tes besoins */
        }
        .admin-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .admin-section .admin-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .admin-section .admin-name {
            margin-top: 10px;
            font-weight: bold;
        }
        .divider {
            border: 0;
            border-top: 1px solid #777;
            margin: 15px 0;
        }
        .menu {
            list-style: none;
            padding: 0;
        }
        .menu li {
            margin: 10px 0;
        }
        .menu a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .menu a:hover {
            background-color: #5a5a5a;
        }
        .menu-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .dropdown-menu {
            list-style: none;
            padding-left: 20px;
            display: none;
        }
        .dropdown-menu a {
            color: #bbb;
        }
        .dropdown-menu a:hover {
            color: white;
        }
        /* --- Contenu principal --- */
        .main-content {
            margin-left: 250px;
            margin-right: 10px;
            margin-top: 80px;
            padding: 20px;
            flex-grow: 1;
            text-align: center;
        }
        .main-content img {
            width: 150px; /* Agrandir l'emoji */
            margin-bottom: 20px;
        }
        .main-content h2 {
            margin: 10px 0;
            font-size: 1.5em;
        }
        .main-content p {
            margin-bottom: 20px;
            color: #555;
        }
        .main-content button {
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .main-content button:hover {
            background-color: #357abd;
        }
        .main table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }
        .main table th, .main table td {
            padding: 10px;
            text-align: center;
        }
        .main table th {
            background-color: #66a6ff;
            color: #ffffff;
            position: sticky;
            top: 0;
            z-index: 5;
            border: 1px solid #ddd; /* Ajouter des bordures à l'en-tête */
        }
        .main table img {
            width: 40px; /* Largeur de l'image */
            height: 40px; /* Hauteur de l'image */
            border-radius: 50%; /* Bordures arrondies pour un cercle */
            object-fit: cover; /* Ajuster l'image pour qu'elle soit bien contenue */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optionnel : un ombrage léger pour un effet esthétique */
        }
        .main button {
            border: none;
            padding: 5px 10px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 2px;
        }
        .main button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }
        .main .btn-add {
            background: linear-gradient(145deg, #66a6ff, #89f7fe);
            width: 200px;
        }
        .main .btn-edit {
            background: linear-gradient(145deg, #4caf50, #81c784);
        }
        .main .btn-delete {
            background: linear-gradient(145deg, #f44336, #e57373);
        }
        .main .btn-info {
            background: linear-gradient(145deg, #ff9800, #ffb74d);
        }
        .card {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        /* Style du card pour le tableau */
        .table-card {
            background-color: #fff; /* Couleur de fond blanche */
            border-radius: 10px; /* Bords arrondis */
            padding: 20px; /* Espacement intérieur */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Ombre */
            margin: 20px auto; /* Centre le card */
            max-width: 100%; /* Limite la largeur du card */
        }
        /* Styles pour le tableau */
        .main-content table {
            width: 100%; /* Occupe toute la largeur du card */
            border-collapse: collapse; /* Évite les doubles bordures */
            font-size: 12px; /* Réduit la taille de la police */
        }
        .main-content table th, .main-content table td {
            border: none; /* Enlève les bordures */
            padding: 5px; /* Réduit l'espace intérieur des cellules */
            text-align: center; /* Centre le contenu des cellules */
        }
        /* Arrondir l'image de l'étudiant */
        .main-content table img {
            width: 40px; /* Largeur de l'image */
            height: 40px; /* Hauteur de l'image */
            border-radius: 50%; /* Arrondi l'image */
            object-fit: cover; /* Garde le ratio de l'image */
        }
        /* Aligner les boutons horizontalement */
        .action-buttons {
            display: flex; /* Utilise flexbox pour l'alignement */
            gap: 5px; /* Espace entre les boutons */
        }
        .add-button-container {
            text-align: left;
            margin-bottom: 20px;
        }
        /* Réduire la hauteur des lignes et alterner les couleurs */
        .main-content table tr:nth-child(even) {
            background-color: #f9f9f9; /* Couleur grise claire pour les lignes paires */
        }
        .main-content table tr:nth-child(odd) {
            background-color: #ffffff; /* Couleur blanche pour les lignes impaires */
        }
        /* Style pour le card de recherche */
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
        .search-card select, .search-card input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        .search-card input {
            flex-grow: 1;
        }
        .send-notes-button {
            background: linear-gradient(145deg, #66a6ff, #89f7fe);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            display: inline-block;
            text-align: left;
        }
        .send-notes-button:hover {
            background: linear-gradient(145deg, #4a90e2, #357abd);
        }
    </style>
</head>
<body>
    <!-- Barre latérale gauche -->
    <aside class="side-bar">
        <div class="admin-section">
            <img src="images_pages/administrateur.png" alt="Admin" class="admin-icon">
            <p class="admin-name">Administrateur</p>
        </div>
        <hr class="divider">
        <ul class="menu">
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/eleve.png" alt="Etudiant" class="menu-icon"> Etudiant
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EtudiantListe.php">Liste des Etudiants</a></li>
                    <li><a href="ajouter_etudiant.php">Ajouter un étudiant</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/logo_enseignant.png" alt="Professeurs" class="menu-icon"> Professeurs
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ProfesseurListe.php">Liste des Professeurs</a></li>
                    <li><a href="ajouter_professeur.php">Ajouter un Professeur</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/back-to-school.png" alt="Professeurs" class="menu-icon"> Matières
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ListeMatiere.php">Liste des Matières</a></li>
                    <li><a href="ajouter_matiere.php">Ajouter une Attribution</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/budget.png" alt="Finances" class="menu-icon"> Finances
                </a>
                <ul class="dropdown-menu">
                    <li><a href="VersementListe.php">Liste des versements</a></li>
                    <li><a href="versement.php">Effectuer un versement</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/exam.png" alt="Notes" class="menu-icon"> Notes
                </a>
                <ul class="dropdown-menu">
                    <li><a href="noteb1.php">Notes B1 </a></li>
                    <li><a href="noteb2.php">Notes B2 </a></li>
                    <li><a href="noteb3.php">Notes B3 </a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/schedule.png" alt="Notes" class="menu-icon"> Emploi de Temps
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EmploiDuTempsB1.php">Emploi B1 </a></li>
                    <li><a href="EmploiDuTempsB2.php">Emploi B2 </a></li>
                    <li><a href="EmploiDuTempsB3.php">Emploi B3 </a></li>
                </ul>
            </li>
            <li>
                <a href="graph.php">
                    <img src="images_pages/graph.png" alt="Graphiques" class="menu-icon"> Graphiques
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <img src="images_pages/settings.png" alt="Paramètres" class="menu-icon"> Paramètres
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <img src="images_pages/login.png" alt="Déconnexion" class="menu-icon"> Déconnexion
                </a>
            </li>
        </ul>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <?php foreach ($classes as $classe): ?>
            <?php if (isset($notes[$classe]) && !empty($notes[$classe])): ?>
                <div class="table-card">
                    <h2>Notes des étudiants de la classe <?php echo $classe; ?></h2>
                    <button class="send-notes-button" onclick="window.location.href='envoyer_notes.php?classe=<?php echo $classe; ?>'">Envoyer les notes</button>
<button class="send-notes-button" onclick="window.location.href='envoyer_conseils.php?classe=<?php echo $classe; ?>'">Envoyer des conseils d'orientation</button>


                    <h3>Notes CC</h3>
                    <table>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <?php foreach ($notes[$classe]['notes'][0] as $column => $value): ?>
                                <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                    <th><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($notes[$classe]['notes'] as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['matricule_etudiant'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['prenom'] ?? ''); ?></td>
                                <?php foreach ($student as $column => $value): ?>
                                    <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                        <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <h3>Notes de TP</h3>
                    <table>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <?php foreach ($notes[$classe]['tp'][0] as $column => $value): ?>
                                <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                    <th><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($notes[$classe]['tp'] as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['matricule_etudiant'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['prenom'] ?? ''); ?></td>
                                <?php foreach ($student as $column => $value): ?>
                                    <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                        <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <h3>Notes d'Exam</h3>
                    <table>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <?php foreach ($notes[$classe]['exam'][0] as $column => $value): ?>
                                <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                    <th><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($notes[$classe]['exam'] as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['matricule_etudiant'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['prenom'] ?? ''); ?></td>
                                <?php foreach ($student as $column => $value): ?>
                                    <?php if ($column !== 'id' && $column !== 'matricule_etudiant' && $column !== 'nom' && $column !== 'prenom'): ?>
                                        <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucune note disponible pour la classe <?php echo $classe; ?>.</p>
            <?php endif; ?>
        <?php endforeach; ?>
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

        // Redirection vers la page des étudiants
        function redirectToEtudiants() {
            window.location.href = "EtudiantListe.php";
        }
    </script>
</body>
</html>
