<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\Database1;
use App\StudentAccount;

require_once 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

if (!isset($_SESSION['matricule'])) {
    header('Location: ../../index.php');
    exit();
}

$matricule = $_SESSION['matricule'];

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
$table_cours = "cours_" . strtolower($classe);

$query = "SELECT * FROM $table_cours";
$stmt = $conn->prepare($query);
$stmt->execute();
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

function extractTextFromPdf($filePath) {
    if (!file_exists($filePath)) {
        return "Fichier non trouvé.";
    }

    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    return $pdf->getText();
}

function gemini($message, $filePath = null) {
    $GKey = "AIzaSyA7OteJoYw6GM-IM7bgRdT2nglSvo_HZn0";
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;

    $fileContent = $filePath ? extractTextFromPdf($filePath) : null;

    if (strpos($message, 'quizz') !== false) {
        $message = "En te basant sur le contenu du fichier fourni, crée un quiz au format HTML suivant ce modèle exact:
        <div class='quiz-container'>
            <form id='quizForm'>
                <div class='quiz-question'>
                    <p>Question 1</p>
                    <div class='quiz-options'>
                        <label><input type='radio' name='q1' value='correct'> Bonne réponse</label>
                        <label><input type='radio' name='q1' value='incorrect'> Mauvaise réponse 1</label>
                        <label><input type='radio' name='q1' value='incorrect'> Mauvaise réponse 2</label>
                    </div>
                </div>
                <!-- Répéter ce format pour chaque question -->
                <button type='submit' class='quiz-submit'>Valider les réponses</button>
            </form>
            <div class='score-display'></div>
        </div>";
    }

    $requestData = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $message]
                ]
            ]
        ]
    ];

    if ($fileContent) {
        $requestData['contents'][0]['parts'][] = ['text' => $fileContent];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return json_encode(['error' => "Erreur cURL : " . curl_error($ch)]);
    }

    curl_close($ch);
    $responseObject = json_decode($response, true);

    if (isset($responseObject['candidates'][0]['content']['parts'][0]['text'])) {
        return json_encode(['summary' => $responseObject['candidates'][0]['content']['parts'][0]['text']]);
    }

    return json_encode(['error' => "Réponse non disponible ou mal formatée."]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $question = $_POST['question'];
    $filePath = "../cours/" . strtolower($classe) . "/" . $_POST['course_file'];

    if (file_exists($filePath)) {
        $response = gemini($question, $filePath);
        echo $response;
    } else {
        echo json_encode(['summary' => "Fichier non disponible."]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Cours</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        /* Remplacez la section du menu dans le CSS par ceci */
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
       

        .side-bar {
            background-color: #D81B60;
            color: white;
            width: 220px;
            padding: 20px 10px;
            position: fixed;
            top: 0;
            bottom: 0;
            height: 100vh;
            z-index: 1001;
        }

        .admin-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-section .admin-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .admin-section .admin-name {
            margin-top: 10px;
            font-weight: bold;
            font-size: 18px;
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
            padding: 12px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .menu a:hover {
            background-color: #34495E;
            transform: scale(1.05);
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

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: 80px auto;
            text-align: center;
            position: relative;
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 1s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .quiz-container {
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-question {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .quiz-options {
            margin-top: 10px;
        }

        .quiz-options label {
            display: block;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quiz-options label:hover {
            background-color: #f0f0f0;
        }

        .quiz-submit {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .quiz-submit:hover {
            background-color: #45a049;
        }

        .score-display {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 4px;
            text-align: center;
            font-size: 18px;
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .correct-answer {
            background-color: #c8e6c9;
            border-color: #4caf50;
        }

        .incorrect-answer {
            background-color: #ffcdd2;
            border-color: #f44336;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .export-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .export-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <!-- Barre latérale -->
    
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

    <div class="card">
        <h2>Mes Cours</h2>
        <p><strong>Matricule:</strong> <?php echo htmlspecialchars($studentInfo['matricule']); ?></p>
        <p><strong>Nom:</strong> <?php echo htmlspecialchars($studentInfo['nom']); ?></p>

        <h3>Cours disponibles:</h3>
        <?php if ($cours): ?>
            <table>
                <tr>
                    <th>Nom du Cours</th>
                    <th>Description</th>
                    <th>Prévisualisation</th>
                    <th>Télécharger</th>
                    <th>Résumé</th>
                    <th>Quizz</th>
                </tr>
                <?php foreach ($cours as $cour): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cour['nom_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($cour['description']); ?></td>
                        <td>
                            <?php if (!empty($cour['nom_cours'])): ?>
                                <a href="../cours/<?= htmlspecialchars(strtolower($classe) . "/" . $cour['nom_cours']); ?>">
                                    <?= htmlspecialchars($cour['nom_cours']); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $file_path = "../cours/" . htmlspecialchars(strtolower($classe) . "/" . $cour['nom_cours']);
                            if (file_exists($file_path)) {
                                echo "<a href='$file_path' download>Télécharger</a>";
                            } else {
                                echo "Fichier non disponible.";
                            }
                            ?>
                        </td>
                        <td>
                            <button class="export-btn" onclick="generateSummary(<?php echo htmlspecialchars($cour['id']); ?>, 'Donne-moi un résumé de ce Cours.', '<?php echo htmlspecialchars($cour['nom_cours']); ?>')">Résumé</button>
                        </td>
                        <td>
                            <button class="export-btn" onclick="generateSummary(<?php echo htmlspecialchars($cour['id']); ?>, 'quizz', '<?php echo htmlspecialchars($cour['nom_cours']); ?>')">Quizz</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Aucun cours disponible pour le moment.</p>
        <?php endif; ?>
    </div>

    <!-- Popup pour afficher le résumé ou le quiz -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <div id="summaryContent"></div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
         // Ferme les menus déroulants lors d'un clic à l'extérieur
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
         // Remplacez le JavaScript existant par celui-ci
         document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdownMenu = e.currentTarget.nextElementSibling;
                const menuItem = e.currentTarget.closest('.menu-item');
                const allDropdowns = document.querySelectorAll('.dropdown-menu');

                // Ferme tous les autres menus déroulants
                allDropdowns.forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('show');
                        const parentItem = menu.closest('.menu-item');
                        if (parentItem) {
                            parentItem.style.marginBottom = '0.5rem';
                        }
                    }
                });

                // Bascule l'état du menu actuel
                dropdownMenu.classList.toggle('show');

                // Ajuste l'espacement en fonction de l'état du menu
                if (dropdownMenu.classList.contains('show')) {
                    const dropdownHeight = dropdownMenu.scrollHeight;
                    menuItem.style.marginBottom = `${dropdownHeight + 10}px`;
                } else {
                    menuItem.style.marginBottom = '0.5rem';
                }
            });
        });
        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Fonction pour afficher le popup avec le contenu
        function showPopup(content) {
            const popup = document.getElementById('popup');
            const summaryContent = document.getElementById('summaryContent');

            // Nettoyer le contenu précédent
            summaryContent.innerHTML = '';

            // Si le contenu contient un quiz
            if (content.includes('quiz-container')) {
                summaryContent.innerHTML = content;

                // Ajouter les gestionnaires d'événements pour le quiz
                const quizForm = summaryContent.querySelector('form');
                if (quizForm) {
                    quizForm.onsubmit = function(e) {
                        e.preventDefault();

                        // Réinitialiser les classes des réponses précédentes
                        quizForm.querySelectorAll('.quiz-options label').forEach(label => {
                            label.classList.remove('correct-answer', 'incorrect-answer');
                        });

                        // Calculer le score
                        let correct = 0;
                        const questions = quizForm.querySelectorAll('.quiz-question');
                        const totalQuestions = questions.length;

                        questions.forEach((question, index) => {
                            const selectedAnswer = question.querySelector('input[type="radio"]:checked');
                            if (selectedAnswer) {
                                const label = selectedAnswer.closest('label');
                                if (selectedAnswer.value === 'correct') {
                                    correct++;
                                    label.classList.add('correct-answer');
                                } else {
                                    label.classList.add('incorrect-answer');
                                    // Montrer la bonne réponse
                                    question.querySelectorAll('input[value="correct"]').forEach(input => {
                                        input.closest('label').classList.add('correct-answer');
                                    });
                                }
                            }
                        });

                        // Afficher le score
                        let scoreDisplay = quizForm.querySelector('.score-display');
                        if (!scoreDisplay) {
                            scoreDisplay = document.createElement('div');
                            scoreDisplay.className = 'score-display';
                            quizForm.appendChild(scoreDisplay);
                        }

                        const percentage = (correct / totalQuestions) * 100;
                        scoreDisplay.innerHTML = `
                            <h3>Résultats du Quiz</h3>
                            <p>Score: ${correct}/${totalQuestions} (${percentage.toFixed(2)}%)</p>
                            <p>${correct === totalQuestions ? 'Parfait !' :
                               correct >= totalQuestions * 0.7 ? 'Très bien !' :
                               correct >= totalQuestions * 0.5 ? 'Continuez vos efforts !' :
                               'Révisez encore un peu.'}</p>
                        `;
                        scoreDisplay.style.display = 'block';

                        // Désactiver les réponses après la soumission
                        quizForm.querySelectorAll('input[type="radio"]').forEach(input => {
                            input.disabled = true;
                        });

                        // Cacher le bouton de soumission
                        quizForm.querySelector('.quiz-submit').style.display = 'none';
                    };
                }
            } else {
                // Pour le contenu normal (non-quiz)
                summaryContent.innerHTML = content;
            }

            popup.style.display = 'block';
        }

        // Fonction pour fermer le popup
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        // Fonction pour générer le résumé ou le quiz
        function generateSummary(courseId, question, courseFile) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.summary) {
                                showPopup(response.summary);
                            } else {
                                showPopup("Erreur: " + (response.error || "Réponse non disponible"));
                            }
                        } catch (e) {
                            showPopup("Erreur: Impossible de traiter la réponse");
                        }
                    } else {
                        showPopup("Erreur: Problème de communication avec le serveur");
                    }
                }
            };
            xhr.send('course_id=' + encodeURIComponent(courseId) +
                    '&course_file=' + encodeURIComponent(courseFile) +
                    '&question=' + encodeURIComponent(question));
        }

        // Fermer le popup si on clique en dehors
        window.onclick = function(event) {
            const popup = document.getElementById('popup');
            if (event.target === popup) {
                closePopup();
            }
        };
    </script>
</body>
</html>