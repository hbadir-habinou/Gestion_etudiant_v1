<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\StudentAccount;
use App\Database1;

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header('Location: ../../index.php');
    exit();
}

function gemini($question)
{
    $GKey = "AIzaSyDcU9lGwX3mrwmuIKFL89G5LKVhh9yOOlQ";
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;

    $requestData = json_encode([
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $question]
                ]
            ]
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("Erreur cURL : " . curl_error($ch));
    }

    curl_close($ch);

    $responseObject = json_decode($response, true);

    if (isset($responseObject['candidates']) && count($responseObject['candidates']) > 0) {
        $content = $responseObject['candidates'][0]['content'] ?? null;
        if ($content && isset($content['parts']) && count($content['parts']) > 0) {
            return $content['parts'][0]['text'];
        } else {
            return "Aucune partie trouvée dans le contenu sélectionné.";
        }
    } else {
        return "Aucun candidat trouvé dans la réponse JSON.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = $_POST['message'];
    $response = gemini($userMessage);
    echo json_encode(['response' => formatResponse($response)]);
    exit();
}

function formatResponse($response) {
    // Remplacer les astérisques par du texte en gras
    $response = str_replace('**', '<strong>', $response);
    $response = str_replace('**', '</strong>', $response);

    // Gérer les listes avec des astérisques
    $lines = explode("\n", $response);
    $formattedResponse = "";
    $inList = false;

    foreach ($lines as $line) {
        if (strpos($line, '* ') === 0) {
            if (!$inList) {
                $formattedResponse .= "<ul>";
                $inList = true;
            }
            $formattedResponse .= "<li>" . substr($line, 2) . "</li>";
        } else {
            if ($inList) {
                $formattedResponse .= "</ul>";
                $inList = false;
            }
            $formattedResponse .= "<p>" . $line . "</p>";
        }
    }

    if ($inList) {
        $formattedResponse .= "</ul>";
    }

    return $formattedResponse;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
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
            margin: 0;
            padding: 0;
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

        .chat-container {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .chat-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease;
        }

        .message.bot {
            flex-direction: row;
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 1rem;
        }

        .message-content {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            max-width: 70%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message.user .message-content {
            background: var(--accent-color);
        }

        .chat-input-container {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            display: flex;
            gap: 1rem;
        }

        .chat-input {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            background: white;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chat-send-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0 2rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-send-btn:hover {
            background: var(--primary-color);
            transform: scale(1.05);
        }

        .speech-controls {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .speech-control-btn {
            background: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .speech-control-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .loading-message {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--primary-color);
            font-style: italic;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .side-bar {
                transform: translateX(-100%);
            }

            .chat-container {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <aside class="side-bar">
        <div class="admin-section">
            <img src="../images_pages/administrateur.png" alt="Admin" class="admin-icon">
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

    <div class="chat-container">
        <div class="chat-card">
            <div class="speech-controls">
                <div class="form-check">
                    <input type="checkbox" id="speech-toggle" class="form-check-input" checked>
                    <label class="form-check-label" for="speech-toggle">Synthèse vocale</label>
                </div>
                <button id="stop-speech" class="speech-control-btn" title="Pause">
                    <i class="fas fa-pause"></i>
                </button>
                <button id="resume-speech" class="speech-control-btn" title="Reprendre">
                    <i class="fas fa-play"></i>
                </button>
                <button id="fast-speech" class="speech-control-btn" title="Vitesse rapide">
                    <i class="fas fa-forward"></i>
                </button>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message bot">
                    <img src="../images_pages/robot-assistant.png" alt="Bot" class="message-avatar">
                    <div class="message-content">
                        Bonjour ! Comment puis-je vous aider ?
                    </div>
                </div>
            </div>

            <div class="chat-input-container">
                <input type="text" id="user-input" class="chat-input" placeholder="Tapez votre message ici...">
                <button onclick="sendMessage()" class="chat-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let speechSynthesisUtterance;
        let isSpeakingFast = false;

        function sendMessage() {
            const userInput = document.getElementById('user-input');
            const message = userInput.value;
            if (message.trim() === '') return;

            // Ajouter le message de l'utilisateur
            addMessageToChat('user', message);
            userInput.value = '';

            // Ajouter le message de chargement
            const loadingMessage = addLoadingMessage();

            // Envoyer la requête
            fetch('chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                // Supprimer le message de chargement
                loadingMessage.remove();
                // Ajouter la réponse du bot
                addMessageToChat('bot', data.response);
                if (document.getElementById('speech-toggle').checked) {
                    speakText(data.response);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                loadingMessage.remove();
                addMessageToChat('bot', "Désolé, une erreur s'est produite.");
            });
        }

        function addMessageToChat(sender, message) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const avatar = sender === 'user' ? '../images_pages/programmer.png' : '../images_pages/robot-assistant.png';

            messageDiv.innerHTML = `
                <img src="${avatar}" alt="${sender}" class="message-avatar">
                <div class="message-content">${message}</div>`;

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addLoadingMessage() {
            const chatMessages = document.getElementById('chat-messages');
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message bot';
            loadingDiv.innerHTML = `
                <img src="../images_pages/robot-assistant.png" alt="Bot" class="message-avatar">
                <div class="message-content loading-message">
                    <i class="fas fa-spinner fa-spin"></i>
                    En train de réfléchir...
                </div>`;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return loadingDiv;
        }

        function speakText(text) {
            if (speechSynthesis.speaking) {
                speechSynthesis.cancel();
            }

            speechSynthesisUtterance = new SpeechSynthesisUtterance(text);
            speechSynthesisUtterance.lang = 'fr-FR';
            speechSynthesisUtterance.rate = isSpeakingFast ? 1.5 : 1.0;
            speechSynthesis.speak(speechSynthesisUtterance);
        }

        // Gestion des contrôles vocaux
        document.getElementById('stop-speech').addEventListener('click', () => {
            if (speechSynthesis.speaking) {
                speechSynthesis.pause();
            }
        });

        document.getElementById('resume-speech').addEventListener('click', () => {
            if (speechSynthesis.paused) {
                speechSynthesis.resume();
            }
        });

        document.getElementById('fast-speech').addEventListener('click', () => {
            isSpeakingFast = !isSpeakingFast;
            if (speechSynthesis.speaking) {
                speechSynthesis.cancel();
                speakText(speechSynthesisUtterance.text);
            }
        });

        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdownMenu = e.currentTarget.nextElementSibling;
                const menuItem = e.currentTarget.closest('.menu-item');
                const allDropdowns = document.querySelectorAll('.dropdown-menu');

                allDropdowns.forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('show');
                        const parentItem = menu.closest('.menu-item');
                        if (parentItem) {
                            parentItem.style.marginBottom = '0.5rem';
                        }
                    }
                });

                dropdownMenu.classList.toggle('show');

                if (dropdownMenu.classList.contains('show')) {
                    const dropdownHeight = dropdownMenu.scrollHeight;
                    menuItem.style.marginBottom = `${dropdownHeight + 10}px`;
                } else {
                    menuItem.style.marginBottom = '0.5rem';
                }
            });
        });

        // Gestion de l'entrée avec la touche Entrée
        document.getElementById('user-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Message de bienvenue avec synthèse vocale
        window.addEventListener('DOMContentLoaded', () => {
            const welcomeMessage = document.querySelector('.message.bot .message-content').textContent;
            if (document.getElementById('speech-toggle').checked) {
                speakText(welcomeMessage);
            }
        });
    </script>
</body>
</html>
