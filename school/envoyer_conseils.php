<?php
require __DIR__ . '/verification/vendor/autoload.php';
require_once 'verification/vendor/autoload.php'; // Charge l'autoloader de Composer
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
// Inclure l'autoloading de Composer
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ConseilProcessor {
    private $db;
    private $classe;
    private $base_table;
    private $all_averages = [];
    private $student_grades = [];

    public function __construct($db, $classe) {
        $this->db = $db;
        $this->classe = $classe;
        $this->base_table = strtolower($classe);
    }

    public function processConseils() {
        $students = $this->getStudents();
        foreach ($students as $student) {
            $this->calculateStudentGrades($student);
        }
        $this->calculateRankings();
        $this->sendConseils($students);
    }

    private function getStudents() {
        $query = "SELECT DISTINCT e.matricule, e.nom, e.prenom, e.email
                 FROM etudiant_infos e
                 INNER JOIN note_{$this->base_table} n ON e.matricule = n.matricule_etudiant";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasValidGrades($result) {
        if (!$result) return false;
        foreach ($result as $key => $value) {
            if (!in_array($key, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
                if (empty($value) || floatval($value) <= 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function calculateStudentGrades($student) {
        $matricule = $student['matricule'];
        $grades = [];
        $valid_grades = true;

        $tables = ["note_{$this->base_table}", "exam_{$this->base_table}", "tp_{$this->base_table}"];
        foreach ($tables as $table) {
            $query = "SELECT * FROM {$table} WHERE matricule_etudiant = :matricule";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':matricule', $matricule);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->hasValidGrades($result)) {
                $valid_grades = false;
                break;
            }

            if ($result) {
                foreach ($result as $subject => $grade) {
                    if (!in_array($subject, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
                        $grades[$subject][] = floatval($grade);
                    }
                }
            }
        }

        if ($valid_grades && !empty($grades)) {
            $final_grades = [];
            $sum = 0;
            $count = 0;

            foreach ($grades as $subject => $notes) {
                if (count($notes) === 3) {
                    $average = array_sum($notes) / 3;
                    $final_grades[$subject] = $average;
                    $sum += $average;
                    $count++;
                }
            }

            if ($count > 0) {
                $overall_average = $sum / $count;
                $this->all_averages[$matricule] = $overall_average;
                $this->student_grades[$matricule] = $final_grades;
            }
        }
    }

    private function calculateRankings() {
        arsort($this->all_averages);
    }

    private function getMention($average) {
        if ($average >= 16) return 'EXCELLENT';
        if ($average >= 14) return 'BIEN';
        if ($average >= 12) return 'ASSEZ BIEN';
        if ($average >= 10) return 'PASSABLE';
        return 'INSUFFISANT';
    }

    private function getOrientationText($nom, $prenom, $grades) {
        $GKey = "AIzaSyA7OteJoYw6GM-IM7bgRdT2nglSvo_HZn0";

        $notes_description = "";
        foreach ($grades as $matiere => $note) {
            $notes_description .= "$matiere: $note/20, ";
        }
        $notes_description = rtrim($notes_description, ", ");

        $prompt = "En tant que conseiller d'orientation professionnel, analyse les résultats suivants pour l'étudiant " .
                 $nom . " " . $prenom . ".\n\n" .
                 "Notes: " . $notes_description . "\n\n" .
                 "Rédige un email professionnel, concis et sans partie à remplir, destiné à l'étudiant. L'email sera adressé à l'étudiant. Le nom de l'établissement c'est Keyce Informatique et IA et le message est envoyé de la part de la scolarité de l'établissement. Sois un peu plus large dans le message et utilise un langage professionnel. Pour chaque matière, dis à l'étudiant ce qu'il doit faire pour s'améliorer.";

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

    private function sendEmail($to, $nom, $prenom, $orientation_text) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gaza45palestine@gmail.com';
            $mail->Password = 'tira vtly vbec schk';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('gaza45palestine@gmail.com', 'Keyce Informatique');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = "Conseil d'Orientation - " . $nom . " " . $prenom;

            $mail->Body = "<div style='font-family: Arial, sans-serif;'>" .
                         "<h2>CONSEIL D'ORIENTATION</h2>" .
                         "<div style='margin: 20px 0;'>" . nl2br(htmlspecialchars($orientation_text)) . "</div>" .
                         "<p style='color: #666;'>Message automatique - Ne pas répondre</p>" .
                         "</div>";

            $mail->AltBody = strip_tags($orientation_text);

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi email pour {$nom} {$prenom}: {$mail->ErrorInfo}");
        }
    }

    public function sendConseils($students) {
        foreach ($this->all_averages as $matricule => $average) {
            $student = array_filter($students, function($s) use ($matricule) {
                return $s['matricule'] === $matricule;
            });
            $student = reset($student);

            if ($student && isset($this->student_grades[$matricule])) {
                $grades = $this->student_grades[$matricule];
                $orientation_text = $this->getOrientationText($student['nom'], $student['prenom'], $grades);
                $this->sendEmail(
                    $student['email'],
                    $student['nom'],
                    $student['prenom'],
                    $orientation_text
                );
            }
        }
    }
}

// Main execution
$database = new Database1();
$processor = new ConseilProcessor($database->getConnection(), $_GET['classe']);
$processor->processConseils();

header('Location: listeNote.php');
exit();
