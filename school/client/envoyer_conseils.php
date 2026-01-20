<?php

require __DIR__ . '/../autoloading/autoload.php';
use App\ProfessorAccount;
use App\Database1;

// Fonction pour obtenir les conseils d'orientation
function getOrientationText($nom, $prenom, $notes) {
    $GKey = "AIzaSyDcU9lGwX3mrwmuIKFL89G5LKVhh9yOOlQ";

    $notes_description = "";
    foreach ($notes as $matiere => $note) {
        $notes_description .= "$matiere: $note/20, ";
    }
    $notes_description = rtrim($notes_description, ", ");

    $prompt = "En tant que conseiller d'orientation professionnel, analyse les résultats suivants pour l'étudiant $nom $prenom.\n\nNotes: $notes_description\n\nRédige un email professionnel, concis et sans partie à remplir, destiné à l'étudiant. L'email sera adressé à l'étudiant. Le nom de l'établissement c'est Keyce Informatique et IA et le message est envoyé de la part de la scolarité de l'établissement. Sois un peu plus large dans le message et utilise un langage professionnel. Pour chaque matière, dis à l'étudiant ce qu'il doit faire pour s'améliorer.";

    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;

    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ??
           "Une erreur est survenue lors de la génération du conseil d'orientation.";
}

// Vérifiez si le bouton "Envoyer des conseils" a été cliqué
if (isset($_POST['envoyer_conseils'])) {
    $matricule = $_POST['matricule'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $notes = $_POST['notes'];

    $orientation_text = getOrientationText($nom, $prenom, $notes);

    echo json_encode(['conseil' => $orientation_text]);
    exit();
}
?>
