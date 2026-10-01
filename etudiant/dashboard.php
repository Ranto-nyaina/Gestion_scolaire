<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$etudiantId = $_SESSION['user_id'];
$niveau     = $_SESSION['niveau'];

$moy = $pdo->prepare("SELECT AVG(valeur) FROM notes WHERE etudiant_id = ?");
$moy->execute([$etudiantId]);
$moyenne = round($moy->fetchColumn() ?? 0, 2);

$nbNotes = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE etudiant_id = ?");
$nbNotes->execute([$etudiantId]);
$nbNotes = $nbNotes->fetchColumn();

$meilleureMatiere = $pdo->prepare("SELECT m.libelle, MAX(n.valeur) AS max_note 
                                   FROM notes n JOIN matieres m ON m.id = n.matiere_id 
                                   WHERE n.etudiant_id = ? GROUP BY m.id 
                                   ORDER BY max_note DESC LIMIT 1");
$meilleureMatiere->execute([$etudiantId]);
$meilleure = $meilleureMatiere->fetch();

$notesParMatiere = $pdo->prepare("SELECT m.libelle, ROUND(AVG(n.valeur), 2) AS moy 
                                  FROM notes n JOIN matieres m ON m.id = n.matiere_id 
                                  WHERE n.etudiant_id = ? GROUP BY m.id");
$notesParMatiere->execute([$etudiantId]);
$notesParMatiere = $notesParMatiere->fetchAll();

$dernieresNotes = $pdo->prepare("SELECT n.valeur, n.date_saisie, m.libelle 
                                 FROM notes n JOIN matieres m ON m.id = n.matiere_id 
                                 WHERE n.etudiant_id = ? ORDER BY n.date_saisie DESC LIMIT 5");
$dernieresNotes->execute([$etudiantId]);
$dernieresNotes = $dernieresNotes->fetchAll();

$jourActuel = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'][date('w')];
$prochainCours = $pdo->prepare("SELECT et.*, m.libelle FROM emplois_du_temps et 
                                JOIN matieres m ON m.id = et.matiere_id 
                                WHERE et.niveau = ? AND et.jour = ? 
                                ORDER BY et.heure_debut LIMIT 5");
$prochainCours->execute([$niveau, $jourActuel]);
$prochainCours = $prochainCours->fetchAll();

$annonces = $pdo->prepare("SELECT a.*, u.nom, u.prenom FROM annonces a 
                           JOIN utilisateurs u ON u.id = a.auteur_id 
                           WHERE a.cible IN ('tous','etudiants') 
                             AND (a.niveau IS NULL OR a.niveau = ?) 
                           ORDER BY a.date_publication DESC LIMIT 3");
$annonces->execute([$niveau]);
$annonces = $annonces->fetchAll();

$qcmDispo = $pdo->prepare("SELECT q.*, m.libelle FROM qcm q 
                           JOIN matieres m ON m.id = q.matiere_id 
                           WHERE (q.niveau IS NULL OR q.niveau = ?) 
                             AND q.id NOT IN (SELECT qcm_id FROM resultats_qcm WHERE etudiant_id = ?) 
                           LIMIT 3");
$qcmDispo->execute([$niveau, $etudiantId]);
$qcmDispo = $qcmDispo->fetchAll();

$qcmPasses = $pdo->prepare("SELECT COUNT(*) FROM resultats_qcm WHERE etudiant_id = ?");
$qcmPasses->execute([$etudiantId]);
$qcmPasses = $qcmPasses->fetchColumn();

$evolutionEtu = $pdo->prepare("SELECT DATE_FORMAT(date_saisie, '%Y-%m') AS mois, 
                                       ROUND(AVG(valeur), 2) AS moy
                                FROM notes 
                                WHERE etudiant_id = ? AND date_saisie >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                GROUP BY mois ORDER BY mois");
$evolutionEtu->execute([$etudiantId]);
$evolutionEtu = $evolutionEtu->fetchAll();
?>

<h2 class="mb-4">👋 Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> !</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6"><div class="card card-stat bg-primary p-3">
        <h6>Ma moyenne</h6><h2 class="mb-0"><?= $moyenne ?>/20</h2></div></div>
    <div class="col-md-3 col-sm-6"><div class="card card-stat bg-success p-3">
        <h6>Mon niveau</h6><h2 class="mb-0"><?= htmlspecialchars($niveau) ?></h2></div></div>
    <div class="col-md-3 col-sm-6"><div class="card card-stat bg-warning p-3">
        <h6>Notes</h6><h2 class="mb-0"><?= $nbNotes ?></h2></div></div>
    <div class="col-md-3 col-sm-6"><div class="card card-stat bg-danger p-3">
        <h6>QCM passés</h6><h2 class="mb-0"><?= $qcmPasses ?></h2></div></div>
</div>

<?php if ($moyenne >= 15): ?>
    <div class="alert alert-success"><i class="bi bi-trophy-fill"></i> <strong>Excellent !</strong> Continue comme ça !</div>
<?php elseif ($moyenne >= 10): ?>
    <div class="alert alert-info"><i class="bi bi-hand-thumbs-up"></i> Bon travail !</div>
<?php elseif ($nbNotes > 0): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Attention, ta moyenne est en dessous de 10.</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-7"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">📊 Mes moyennes par matière</h6>
        <?php if (empty($notesParMatiere)): ?>
            <p class="text-muted">Aucune note disponible.</p>
        <?php else: ?>
            <canvas id="chartMesNotes" height="130"></canvas>
        <?php endif; ?>
    </div></div></div>
    <div class="col-md-5"><div class="card shadow-sm h-100">
        <div class="card-header bg-info text-white"><strong>🏅 Ma meilleure matière</strong></div>
        <div class="card-body text-center">
            <?php if ($meilleure): ?>
                <h3><?= htmlspecialchars($meilleure['libelle']) ?></h3>
                <h1 class="text-success"><?= $meilleure['max_note'] ?>/20</h1>
            <?php else: ?>
                <p class="text-muted">Pas encore de note</p>
            <?php endif; ?>
        </div>
    </div></div>
</div>

<?php if (count($evolutionEtu) > 1): ?>
<div class="row g-3 mb-4">
    <div class="col-12"><div class="card shadow-sm"><div class="card-body">
        <h6 class="text-muted mb-3">📈 Mon évolution (6 mois)</h6>
        <canvas id="chartEvolutionEtu" height="60"></canvas>
    </div></div></div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white"><strong>📅 Mes cours (<?= $jourActuel ?>)</strong></div>
        <div class="card-body">
            <?php if (empty($prochainCours)): ?>
                <p class="text-muted mb-0">Aucun cours aujourd'hui 🎉</p>
            <?php else: ?>
                <?php foreach ($prochainCours as $c): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong><?= htmlspecialchars($c['libelle']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($c['enseignant']) ?> — <?= $c['salle'] ?></small>
                        </div>
                        <span class="badge bg-primary"><?= substr($c['heure_debut'],0,5) ?> - <?= substr($c['heure_fin'],0,5) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div></div>
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-warning text-dark"><strong>📢 Dernières annonces</strong></div>
        <div class="card-body">
            <?php if (empty($annonces)): ?>
                <p class="text-muted mb-0">Aucune annonce</p>
            <?php else: ?>
                <?php foreach ($annonces as $a): ?>
                    <div class="border-bottom py-2">
                        <strong><?= htmlspecialchars($a['titre']) ?></strong>
                        <?php if ($a['niveau']): ?>
                            <span class="badge bg-info"><?= $a['niveau'] ?></span>
                        <?php endif; ?><br>
                        <small class="text-muted"><?= mb_substr(htmlspecialchars($a['contenu']), 0, 80) ?>...</small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div></div>
</div>

<div class="row g-3">
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-danger text-white"><strong>✍️ QCM à passer</strong></div>
        <div class="card-body">
            <?php if (empty($qcmDispo)): ?>
                <p class="text-muted mb-0">🎉 Tous les QCM sont faits !</p>
            <?php else: ?>
                <?php foreach ($qcmDispo as $q): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong><?= htmlspecialchars($q['titre']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($q['libelle']) ?> — ⏱ <?= $q['duree_minutes'] ?> min</small>
                        </div>
                        <a href="qcm.php?passer=<?= $q['id'] ?>" class="btn btn-sm btn-danger">▶</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div></div>
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-success text-white"><strong>📝 Mes dernières notes</strong></div>
        <div class="card-body p-0">
            <?php if (empty($dernieresNotes)): ?>
                <p class="text-muted p-3 mb-0">Aucune note</p>
            <?php else: ?>
                <table class="table table-sm mb-0"><tbody>
                <?php foreach ($dernieresNotes as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['libelle']) ?></td>
                        <td class="text-end">
                            <span class="badge bg-<?= $n['valeur'] >= 10 ? 'success' : 'danger' ?>"><?= $n['valeur'] ?>/20</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </div>
    </div></div>
</div>

<?php if (!empty($notesParMatiere)): ?>
<script>
new Chart(document.getElementById('chartMesNotes'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($notesParMatiere, 'libelle')) ?>,
        datasets: [{
            label: 'Ma moyenne',
            data: <?= json_encode(array_column($notesParMatiere, 'moy')) ?>,
            backgroundColor: <?= json_encode(array_map(fn($n) => $n['moy'] >= 10 ? '#10b981' : '#ef4444', $notesParMatiere)) ?>,
            borderRadius: 8
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 20 } } }
});
</script>
<?php endif; ?>

<?php if (count($evolutionEtu) > 1): ?>
<script>
new Chart(document.getElementById('chartEvolutionEtu'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($evolutionEtu, 'mois')) ?>,
        datasets: [{
            label: 'Ma moyenne',
            data: <?= json_encode(array_column($evolutionEtu, 'moy')) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            tension: 0.4, fill: true, pointRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 20 } } }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>