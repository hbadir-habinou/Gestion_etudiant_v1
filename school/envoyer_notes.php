<?php
require_once 'verification/vendor/autoload.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require_once __DIR__ . '/signn/vendor/autoload.php';
require __DIR__ . '/autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use setasign\Fpdi\Tcpdf\Fpdi;

// Fonction pour générer un hash de vérification
function generateSignature($matricule, $nomPrenom, $notes, $moyenne, $rank) {
    $dataToHash = $matricule . $nomPrenom . implode(',', $notes) . $moyenne . $rank;
    return hash('sha256', $dataToHash);
}

class GradeProcessor {
    private $db;
    private $classe;
    private $base_table;
    private $all_averages = [];
    private $student_grades = [];
    private $certificate_path;
    private $private_key_path;

    public function __construct($db, $classe) {
        $this->db = $db;
        $this->classe = $classe;
        $this->base_table = strtolower($classe);
        $this->certificate_path = __DIR__ . '/signn/certificates/certificate.pem';
        $this->private_key_path = __DIR__ . '/signn/certificates/private_key.pem';
        
        // Vérifier l'existence des certificats
        if (!file_exists($this->certificate_path) || !file_exists($this->private_key_path)) {
            throw new Exception("Les fichiers de certificat sont manquants.");
        }
    }

    public function processGrades() {
        $students = $this->getStudents();
        foreach ($students as $student) {
            $this->calculateStudentGrades($student);
        }
        $this->calculateRankings();
        $this->sendReports($students);
    }

    private function getStudents() {
        $query = "SELECT DISTINCT e.matricule, e.nom, e.prenom, e.email, e.email_parent, e.nom_parent
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

    private function generatePDF($student, $grades, $average, $rank) {
        $mention = $this->getMention($average);
        
        // Créer une instance de TCPDF avec FPDI
        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Configurer le document
        $pdf->SetCreator('Keyce Informatique');
        $pdf->SetAuthor('Keyce Informatique et IA');
        $pdf->SetTitle('Relevé de Notes - ' . $student['nom'] . ' ' . $student['prenom']);
        
        // Ajouter une page
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20);

        // En-tête avec logo
        if (file_exists('images/keyce.jpeg')) {
            $pdf->Image('images/keyce.jpeg', 20, 15, 30);
        }

        // Générer la signature numérique
        $signature = generateSignature($student['matricule'], $student['nom'] . ' ' . $student['prenom'], $grades, $average, $rank);

        // En-tête du document
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 30, '', 0, 1);
        $pdf->Cell(0, 10, 'RELEVE DE NOTES', 1, 1, 'C');

        // Information de l'étudiant
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Ln(10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 30, '', 1, 1, 'L', true);
        $pdf->SetY($pdf->GetY() - 25);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MATRICULE: ' . $student['matricule'], 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'NOM ET PRENOM: ' . $student['nom'] . ' ' . $student['prenom'], 0, 1);

        // Tableau des notes
        $pdf->Ln(15);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(90, 10, 'MATIERE', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'MOYENNE', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'APPRECIATION', 1, 1, 'C', true);

        // Contenu du tableau
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        foreach ($grades as $subject => $grade) {
            $subject_mention = $this->getMention($grade);
            $subject = iconv('UTF-8', 'windows-1252//TRANSLIT', $subject);
            $pdf->Cell(90, 8, $subject, 1, 0, 'L', true);
            $pdf->Cell(30, 8, number_format($grade, 2), 1, 0, 'C', true);
            $pdf->Cell(50, 8, $subject_mention, 1, 1, 'C', true);
        }

        // Résultats finaux
        $pdf->Ln(10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 30, '', 1, 1, 'L', true);
        $pdf->SetY($pdf->GetY() - 25);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MOYENNE GENERALE: ' . number_format($average, 2), 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'RANG: ' . $rank, 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MENTION: ' . $mention, 0, 1);

        // Ajouter la signature visible (image)
        $pdf->Ln(10); // Espace avant la signature
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Signature:', 0, 1, 'L');
        $pdf->Ln(5); // Espace entre le texte et l'image

        // Chemin de l'image de la signature
        $signature_image_path = __DIR__ . '/signn/certificates/signature.png';
        if (file_exists($signature_image_path)) {
            // Position de la signature visible
            $signature_x = 20; // Position horizontale
            $signature_y = $pdf->GetY(); // Position verticale
            $signature_width = 40; // Largeur de l'image de signature
            $signature_height = 20; // Hauteur de l'image de signature

            // Ajouter l'image de la signature
            $pdf->Image($signature_image_path, $signature_x, $signature_y, $signature_width, $signature_height);
        }

        // Configuration de la signature numérique
        $info = [
            'Name' => 'KEYCE INFORMATIQUE ET IA',
            'Location' => 'Yaounde, Cameroon',
            'Reason' => 'Signature du relevé de notes',
            'ContactInfo' => 'palestine01gaza@gmail.com'
        ];
        
        try {
            // Ajouter la signature numérique
            $pdf->setSignature(
                file_get_contents($this->certificate_path),
                file_get_contents($this->private_key_path),
                'toto', // Mot de passe de la clé privée
                '', // Mot de passe du certificat
                2, // Type de signature (2 pour une signature visible)
                $info, // Informations de la signature
                'A' // Apparence de la signature
            );
            
            // Superposer la signature numérique sur la signature visible
            $pdf->setSignatureAppearance($signature_x, $signature_y, $signature_width, $signature_height);
        } catch (Exception $e) {
            error_log("Erreur lors de la signature du PDF: " . $e->getMessage());
        }

        // Créer le dossier releves s'il n'existe pas
        $releves_dir = __DIR__ . '/releves';
        if (!is_dir($releves_dir)) {
            mkdir($releves_dir, 0755, true);
        }

        // Enregistrer le PDF
        $pdf_path = $releves_dir . "/releve_{$student['matricule']}.pdf";
        $pdf->Output($pdf_path, 'F');

        // Insérer la signature dans la base de données
        $insertQuery = "INSERT INTO releve_signature (matricule_etudiant, nom, prenom, signature)
                       VALUES (:matricule, :nom, :prenom, :signature)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bindParam(':matricule', $student['matricule']);
        $insertStmt->bindParam(':nom', $student['nom']);
        $insertStmt->bindParam(':prenom', $student['prenom']);
        $insertStmt->bindParam(':signature', $signature);
        $insertStmt->execute();

        return [$pdf_path, $signature];
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
                 "Rédige un email professionnel, concis et sans partie à remplir, destiné aux parents. L'email sera adressé aux parents. Le nom de l'établissement c'est Keyce Informatique et IA et le message est envoyé de la part de la scolarité de l'établissement. Sois un peu plus large dans le message et utilise un langage professionnel.";

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

    private function sendEmail($to, $cc, $nom, $prenom, $pdf_info, $orientation_text) {
        list($pdf_path, $signature) = $pdf_info;
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
            $mail->addCC($cc);
            $mail->addAttachment($pdf_path);

            $mail->isHTML(true);
            $mail->Subject = "Relevé de Notes - " . $nom . " " . $prenom;

            $mail->Body = "<div style='font-family: helvetica, sans-serif;'>" .
                         "<h2>RELEVE DE NOTES ET CONSEIL D'ORIENTATION</h2>" .
                         "<div style='margin: 20px 0;'>" . nl2br(htmlspecialchars($orientation_text)) . "</div>" .
                         "<p style='font-style: italic;'>Signature numérique: " . $signature . "</p>" .
                         "<p style='color: #666;'>Message automatique - Ne pas répondre</p>" .
                         "</div>";

            $mail->AltBody = strip_tags($orientation_text) . "\n\nSignature numérique: " . $signature;

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi email pour {$nom} {$prenom}: {$mail->ErrorInfo}");
        }
    }

    public function sendReports($students) {
        $rank = 1;
        foreach ($this->all_averages as $matricule => $average) {
            $student = array_filter($students, function($s) use ($matricule) {
                return $s['matricule'] === $matricule;
            });
            $student = reset($student);

            if ($student && isset($this->student_grades[$matricule])) {
                $grades = $this->student_grades[$matricule];
                $pdf_info = $this->generatePDF($student, $grades, $average, $rank);
                $orientation_text = $this->getOrientationText($student['nom'], $student['prenom'], $grades);
                $this->sendEmail(
                    $student['email_parent'],
                    $student['email'],
                    $student['nom'],
                    $student['prenom'],
                    $pdf_info,
                    $orientation_text
                );
                $rank++;
            }
        }
    }
}

// Exécution principale
try {
    $database = new Database1();
    $processor = new GradeProcessor($database->getConnection(), $_GET['classe']);
    $processor->processGrades();
    header('Location: listeNote.php');
    exit();
} catch (Exception $e) {
    error_log("Erreur lors du traitement des notes: " . $e->getMessage());
    // Rediriger vers une page d'erreur ou afficher un message
    header('Location: error.php?message=' . urlencode("Une erreur est survenue lors du traitement des notes."));
    exit();
}