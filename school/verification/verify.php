<?php
require_once '../classes/db_connect.php';

$message = '';
$isAuthentic = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signature'])) {
    $db = new Database1();
    $conn = $db->getConnection();

    $signature = $_POST['signature'];

    // Vérifier la signature dans la base de données
    $query = "SELECT matricule_etudiant, nom, prenom FROM releve_signature WHERE signature = :signature";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':signature', $signature);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $isAuthentic = true;
        $message = "Le fichier est authentique.";
    } else {
        $message = "Le fichier n'est pas authentique ou a été modifié.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérifier la signature numérique</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Animation CSS -->
    <style>
        .result-box {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            background-color: <?= $isAuthentic ? '#d4edda' : '#f8d7da' ?>;
            color: <?= $isAuthentic ? '#155724' : '#721c24' ?>;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title text-center">
                            <i class="fas fa-check-circle"></i> Vérifier la signature numérique
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Formulaire de vérification -->
                        <form method="post">
                            <div class="form-group">
                                <label for="signature">Collez la signature numérique :</label>
                                <input type="text" class="form-control" id="signature" name="signature" required>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i> Vérifier
                                </button>
                            </div>
                        </form>

                        <!-- Résultat de la vérification -->
                        <?php if ($message): ?>
                            <div class="result-box mt-4">
                                <h4 class="text-center">
                                    <?php if ($isAuthentic): ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-danger"></i>
                                    <?php endif; ?>
                                    <?= $message ?>
                                </h4>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS et dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>