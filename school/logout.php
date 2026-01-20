<?php
// Détruire la session si elle existe
session_start();
session_unset();
session_destroy();

// Redirection vers la page d'accueil
header("Location: ../index.php");
exit();
?>
