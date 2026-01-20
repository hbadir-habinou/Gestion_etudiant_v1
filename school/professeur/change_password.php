<?php
session_start();
require __DIR__ . '/../autoloading/autoload.php';
use App\Database1;
use App\ProfessorAccount;

if (!isset($_SESSION['login'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $professorAccount = new ProfessorAccount();
    $professorAccount->changePassword($_SESSION['login'], $newPassword);

    header('Location: notes_cc.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #D81B60;
            --secondary-color: #FF80AB;
            --light-pink: #FCE4EC;
            --dark-gray: #424242;
        }

        body {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #FF80AB;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #cccccc;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .input-group input:focus {
            border-color: #D81B60;
            box-shadow: 0 0 8px #FF80AB;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(145deg, #D81B60, #FF80AB);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px #D81B60;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Changer le mot de passe</h2>
    <form method="POST" action="">
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="new_password" class="input-field" placeholder="Nouveau mot de passe" required>
        </div>
        <button type="submit" class="login-btn">Changer le mot de passe</button>
    </form>
</div>
</body>
</html>
