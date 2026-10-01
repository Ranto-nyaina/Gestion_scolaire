<?php
require_once __DIR__ . '/../config/database.php';

$hash = password_hash('Etudiant123!', PASSWORD_DEFAULT);
$pdo->exec("UPDATE utilisateurs SET mot_de_passe = '$hash' WHERE role='etudiant'");
echo "✅ Mots de passe des étudiants régénérés : Etudiant123!<br>";
echo "⚠️ Supprime ce fichier après utilisation !";