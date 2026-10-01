<?php
function notifier($pdo, $userId, $titre, $message, $lien = null, $icone = 'bell', $couleur = 'primary') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, titre, message, lien, icone, couleur) 
                           VALUES (?,?,?,?,?,?)");
    $stmt->execute([$userId, $titre, $message, $lien, $icone, $couleur]);
}

function notifierAdmins($pdo, $titre, $message, $lien = null, $icone = 'bell', $couleur = 'primary') {
    $admins = $pdo->query("SELECT id FROM utilisateurs WHERE role='admin'")->fetchAll();
    foreach ($admins as $a) {
        notifier($pdo, $a['id'], $titre, $message, $lien, $icone, $couleur);
    }
}

function notifierNiveau($pdo, $niveau, $titre, $message, $lien = null, $icone = 'bell', $couleur = 'primary') {
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE role='etudiant' AND statut='valide' AND niveau = ?");
    $stmt->execute([$niveau]);
    foreach ($stmt->fetchAll() as $e) {
        notifier($pdo, $e['id'], $titre, $message, $lien, $icone, $couleur);
    }
}