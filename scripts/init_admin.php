<?php
require_once __DIR__ . '/../config/database.php';

$hash = password_hash('Admin123!', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO utilisateurs 
    (nom, prenom, email, telephone, mot_de_passe, role, statut) 
    VALUES ('Admin', 'Système', 'admin@ecole.com', '0340000000', ?, 'admin', 'valide')
    ON DUPLICATE KEY UPDATE nom = VALUES(nom)");
$stmt->execute([$hash]);

echo "✅ Admin créé : admin@ecole.com / Admin123!<br>";
echo "⚠️ Supprime ce fichier après utilisation !";