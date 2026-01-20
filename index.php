<?php
session_start();

require __DIR__ . '/school/autoloading/autoload.php';

use App\Login;
use App\ProfessorAccount;
use App\StudentAccount;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifier si c'est un admin
    $login = new Login($username, $password);
    $adminValidation = $login->validate();
    if ($adminValidation === true) {
        $_SESSION['username'] = $username;
        header("Location: school/dashboard.php");
        exit();
    }

    // Vérifier si c'est un professeur
    $professorAccount = new ProfessorAccount();
    $professorInfo = $professorAccount->getProfessorInfoByLogin($username);
    if ($professorInfo && $professorInfo['password_enseignant'] === $password) {
        if ($professorInfo['password_enseignant'] !== $username) {
            $_SESSION['login'] = $username;
            $_SESSION['matricule_enseignant'] = $professorInfo['matricule_enseignant']; // Ajout du matricule
            header('Location: school/professeur/CardProf.php');
            exit();
        } else {
            $_SESSION['login'] = $username;
            $_SESSION['matricule_enseignant'] = $professorInfo['matricule_enseignant']; // Ajout du matricule
            header('Location: school/professeur/change_password.php');
            exit();
        }
    }

    // Vérifier si c'est un étudiant
    $studentAccount = new StudentAccount();
    $isFirstLogin = $studentAccount->isFirstLogin($username, $password);
    if ($isFirstLogin) {
        $_SESSION['matricule'] = $username;
        header('Location: school/client/change.php');
        exit();
    } elseif ($studentAccount->authenticate($username, $password)) {
        $_SESSION['matricule'] = $username;
        header('Location: school/client/card.php');
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail Universitaire - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3d59;
            --secondary: #ff6e40;
            --accent: #ffc13b;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            font-family: 'Open Sans', sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 450px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .university-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .university-logo {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .university-logo i {
            font-size: 2.5rem;
            color: white;
        }

        .form-control {
            border: 2px solid #e4e8eb;
            border-radius: 8px;
            padding: 12px 45px;
            height: auto;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(30, 61, 89, 0.15);
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 10;
        }

        .role-selector {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .role-btn {
            background: #f5f7fa;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            color: var(--primary);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-btn.active {
            background: var(--primary);
            color: white;
        }

        .btn-connect {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-connect:hover {
            background: #15293d;
            transform: translateY(-2px);
        }

        .semester-info {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.9rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .footer-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .footer-link:hover {
            color: var(--secondary);
        }

        .error-modal .modal-content {
            border-radius: 10px;
        }

        .error-modal .modal-header {
            background: #f8d7da;
            color: #721c24;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="login-container">
        <div class="semester-info">
            <i class="fas fa-calendar-alt"></i>
            <span>Semestre 2023-2024</span>
        </div>

        <div class="university-header">
            <div class="university-logo">
                <i class="fas fa-university"></i>
            </div>
            <h1 class="h4 text-primary mb-3">Portail Universitaire</h1>
            <p class="text-muted">Accédez à votre espace personnel</p>
        </div>

        <form method="POST" action="" id="loginForm">
            <div class="input-group">
                <i class="fas fa-id-card input-icon"></i>
                <input type="text" class="form-control" name="username" placeholder="Numéro d'étudiant / Identifiant" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit" class="btn btn-connect w-100">
                <i class="fas fa-sign-in-alt me-2"></i>
                Se connecter
            </button>
        </form>

        <div class="footer-links">
            <a href="#" class="footer-link">
                <i class="fas fa-question-circle"></i>
                Aide
            </a>
            <a href="#" class="footer-link">
                <i class="fas fa-key"></i>
                Mot de passe oublié
            </a>
            <a href="#" class="footer-link">
                <i class="fas fa-envelope"></i>
                Contact
            </a>
        </div>
    </div>

    <div class="modal fade error-modal" id="errorModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Erreur de connexion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="errorMessage"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion des rôles
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Gestion des erreurs
        window.onload = function() {
            const error = "<?php echo $error; ?>";
            if (error) {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorMessage').innerText = error;
                errorModal.show();
            }
        }
    </script>
</body>
</html>