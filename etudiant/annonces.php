<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$niveau = $_SESSION['niveau'];

$stmt = $pdo->prepare("SELECT a.*, u.nom, u.prenom FROM annonces a 
                       JOIN utilisateurs u ON u.id = a.auteur_id 
                       WHERE a.cible IN ('tous','etudiants')
                         AND (a.niveau IS NULL OR a.niveau = ?)
                       ORDER BY a.date_publication DESC");
$stmt->execute([$niveau]);
$annonces = $stmt->fetchAll();
?>

<h2 class="mb-4">📢 Annonces — <?= htmlspecialchars($niveau) ?></h2>

<?php if (empty($annonces)): ?>
    <div class="alert alert-info">Aucune annonce pour votre niveau.</div>
<?php endif; ?>

<?php foreach ($annonces as $a): ?>
<div class="card mb-3 shadow-sm border-start border-4 border-primary">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5><?= htmlspecialchars($a['titre']) ?></h5>
            <?php if ($a['niveau']): ?>
                <span class="badge bg-info"><?= $a['niveau'] ?></span>
            <?php else: ?>
                <span class="badge bg-light text-dark">Tous niveaux</span>
            <?php endif; ?>
        </div>
        <p><?= nl2br(htmlspecialchars($a['contenu'])) ?></p>
        <small class="text-muted">
            <i class="bi bi-person"></i> <?= $a['nom'].' '.$a['prenom'] ?> —
            <i class="bi bi-clock"></i> <?= $a['date_publication'] ?>
        </small>
    </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>