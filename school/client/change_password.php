<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';

// Utiliser les classes
use App\Database1;
use App\StudentAccount;

if (!isset($_SESSION['matricule'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $studentAccount = new StudentAccount();
    $studentAccount->changePassword($_SESSION['matricule'], $newPassword);

    header('Location: card.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #A2C2E3, #F1A7C4);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0px 15px 40px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            text-align: center;
            width: 100%;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: 600;
            color: #6B6B6B;
            letter-spacing: 1px;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #333;
            font-size: 16px;
            transition: border 0.3s ease-in-out;
        }
        .input-field:focus {
            border-color: #F1A7C4;
            outline: none;
        }
        .login-btn {
            background-color: #F1A7C4;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        .login-btn:hover {
            background-color: #A2C2E3;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Changer le mot de passe</h2>
    <form method="POST" action="">
        <input type="password" name="new_password" class="input-field" placeholder="Nouveau mot de passe" required>
        <button type="submit" class="login-btn">Changer le mot de passe</button>
    </form>
</div>
</body>
</html>
