<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$etudiantId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT n.*, m.libelle, m.coefficient 
                       FROM notes n 
                       JOIN matieres m ON m.id = n.matiere_id 
                       WHERE n.etudiant_id = ? 
                       ORDER BY n.date_saisie DESC");
$stmt->execute([$etudiantId]);
$notes = $stmt->fetchAll();

$total = 0; $coefs = 0;
foreach ($notes as $n) { $total += $n['valeur'] * $n['coefficient']; $coefs += $n['coefficient']; }
$moyenne = $coefs ? round($total / $coefs, 2) : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📓 Mes notes</h2>
    <a href="/gestion-scolaire/export/pdf_bulletin.php" target="_blank" class="btn btn-danger">
        <i class="bi bi-file-pdf"></i> Télécharger mon bulletin PDF
    </a>
</div>

<div class="alert alert-<?= $moyenne >= 10 ? 'success' : 'danger' ?>">
    <strong>Moyenne générale pondérée : <?= $moyenne ?>/20</strong>
</div>

<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover">
    <thead class="table-dark">
        <tr><th>Matière</th><th>Note</th><th>Coef</th><th>Semestre</th><th>Date</th></tr>
    </thead>
    <tbody>
    <?php foreach ($notes as $n): ?>
        <tr>
            <td><?= htmlspecialchars($n['libelle']) ?></td>
            <td><span class="badge bg-<?= $n['valeur'] >= 10 ? 'success' : 'danger' ?>"><?= $n['valeur'] ?>/20</span></td>
            <td><?= $n['coefficient'] ?></td>
            <td>S<?= $n['semestre'] ?></td>
            <td><?= $n['date_saisie'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>