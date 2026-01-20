<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\ProfessorAccount;
use App\Database1;

if (!isset($_SESSION['login'])) {
    header('Location: ../../index.php');
    exit();
}

$login = $_SESSION['login'];
$professorAccount = new ProfessorAccount();
$professorInfo = $professorAccount->getProfessorInfo($login);
$nom_matiere = $professorInfo['nom_matiere'];
$niveau = substr($login, 0, 2);
$table_notes = "note_" . strtolower($niveau);

$db = new Database1();
$conn = $db->getConnection();
$query = "SELECT matricule_etudiant, nom, prenom, `$nom_matiere` as note FROM $table_notes";
$stmt = $conn->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(160, 222, 161),rgb(212, 234, 244));
            margin: 0;
            padding: 0;
            color: #333;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            float: left;
            display: block;
            color: #333;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar a.active, .navbar a:hover {
            background-color:rgb(232, 232, 232);
            color: #333;
            border-radius: 5px;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 80px auto;
            text-align: center;
            position: relative;
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 1s forwards;
        }
        .container:hover {
            transform: scale(1.03);
            box-shadow: 0px 15px 35px rgba(0, 0, 0, 0.2);
        }
        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: 600;
            color: #6B6B6B;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #F1A7C4;
            color: white;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s ease-in-out;
        }
        .input-field:focus {
            border-color: #A2C2E3;
            outline: none;
        }
        .save-btn {
            background-color: #F1A7C4;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .save-btn:hover {
            background-color: #A2C2E3;
            transform: scale(1.05);
        }
        .assign-btn {
            background-color: #A2C2E3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .assign-btn:hover {
            background-color: #F1A7C4;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        footer {
            background-color: rgba(255, 255, 255, 0.3);
            color: #666;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="navbar">
    <a href="notes.php" class="active">Gestion des Notes</a>
    <a href="listecours.php" class="active">Cours</a>

</div>
<div class="container">
    <h2>Gestion des Notes</h2>
    <form method="POST" action="assign_notes.php">
        <table>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Note (<?php echo $nom_matiere; ?>)</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['matricule_etudiant']); ?></td>
                    <td><?php echo htmlspecialchars($student['nom']); ?></td>
                    <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                    <td>
                        <?php if (isset($student['note'])): ?>
                            <?php echo htmlspecialchars($student['note']); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="button" class="assign-btn" onclick="window.location.href='assign_notes.php'">Attribuer les Notes</button>
    </form>
</div>
<footer>
    <p>&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
</footer>
</body>
</html>
