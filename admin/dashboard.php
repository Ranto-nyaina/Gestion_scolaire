<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$nbEtudiants = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant' AND statut='valide'")->fetchColumn();
$nbAttente   = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant' AND statut='en_attente'")->fetchColumn();
$nbMatieres  = $pdo->query("SELECT COUNT(*) FROM matieres")->fetchColumn();
$nbAnnonces  = $pdo->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
$nbQcm       = $pdo->query("SELECT COUNT(*) FROM qcm")->fetchColumn();
$nbNotes     = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$moyGen = round($pdo->query("SELECT AVG(valeur) FROM notes")->fetchColumn() ?? 0, 2);

$repartition = $pdo->query("SELECT niveau, COUNT(*) AS nb FROM utilisateurs 
                            WHERE role='etudiant' AND statut='valide' 
                            GROUP BY niveau ORDER BY niveau")->fetchAll();

$moyNiveau = $pdo->query("SELECT u.niveau, ROUND(AVG(n.valeur), 2) AS moy 
                          FROM notes n JOIN utilisateurs u ON u.id = n.etudiant_id 
                          GROUP BY u.niveau ORDER BY u.niveau")->fetchAll();

$topEtudiants = $pdo->query("SELECT u.nom, u.prenom, u.niveau, ROUND(AVG(n.valeur), 2) AS moy 
                             FROM notes n JOIN utilisateurs u ON u.id = n.etudiant_id 
                             GROUP BY u.id ORDER BY moy DESC LIMIT 5")->fetchAll();

$dernieresNotes = $pdo->query("SELECT n.valeur, n.date_saisie, 
                                      u.nom, u.prenom, m.libelle 
                               FROM notes n 
                               JOIN utilisateurs u ON u.id = n.etudiant_id 
                               JOIN matieres m ON m.id = n.matiere_id 
                               ORDER BY n.date_saisie DESC LIMIT 5")->fetchAll();

$evolution = $pdo->query("SELECT DATE_FORMAT(date_saisie, '%Y-%m') AS mois, 
                                 ROUND(AVG(valeur), 2) AS moy
                          FROM notes 
                          WHERE date_saisie >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                          GROUP BY mois ORDER BY mois")->fetchAll();

$inscriptions = $pdo->query("SELECT DATE_FORMAT(cree_le, '%Y-%m') AS mois, COUNT(*) AS nb
                             FROM utilisateurs 
                             WHERE role='etudiant' AND cree_le >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                             GROUP BY mois ORDER BY mois")->fetchAll();

$repartitionNotes = $pdo->query("SELECT 
    SUM(CASE WHEN valeur >= 16 THEN 1 ELSE 0 END) AS excellent,
    SUM(CASE WHEN valeur >= 14 AND valeur < 16 THEN 1 ELSE 0 END) AS bien,
    SUM(CASE WHEN valeur >= 12 AND valeur < 14 THEN 1 ELSE 0 END) AS assez_bien,
    SUM(CASE WHEN valeur >= 10 AND valeur < 12 THEN 1 ELSE 0 END) AS passable,
    SUM(CASE WHEN valeur < 10 THEN 1 ELSE 0 END) AS insuffisant
    FROM notes")->fetch();
?>

<h2 class="mb-4">📊 Tableau de bord — Admin</h2>

<?php if ($nbAttente > 0): ?>
<div class="alert alert-warning d-flex align-items-center">
    <i class="bi bi-hourglass-split fs-3 me-3"></i>
    <div><strong><?= $nbAttente ?> inscription(s) en attente</strong><br>
    <a href="etudiants.php" class="btn btn-sm btn-warning mt-2">Voir les demandes</a></div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <a href="etudiants.php" class="text-decoration-none">
            <div class="card card-stat bg-primary p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Étudiants</h6>
                        <h2 class="mb-0"><?= $nbEtudiants ?></h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="matieres.php" class="text-decoration-none">
            <div class="card card-stat bg-success p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Matières</h6>
                        <h2 class="mb-0"><?= $nbMatieres ?></h2>
                    </div>
                    <i class="bi bi-book fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="notes.php" class="text-decoration-none">
            <div class="card card-stat bg-warning p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Notes</h6>
                        <h2 class="mb-0"><?= $nbNotes ?></h2>
                    </div>
                    <i class="bi bi-journal-check fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="qcm.php" class="text-decoration-none">
            <div class="card card-stat bg-danger p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>QCM</h6>
                        <h2 class="mb-0"><?= $nbQcm ?></h2>
                    </div>
                    <i class="bi bi-question-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card shadow-sm"><div class="card-body text-center">
        <h6 class="text-muted">Moyenne générale</h6>
        <h1 class="text-<?= $moyGen >= 10 ? 'success' : 'danger' ?>"><?= $moyGen ?>/20</h1>
    </div></div></div>
    <div class="col-md-8"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">📈 Moyenne par niveau</h6>
        <canvas id="chartMoyNiveau" height="70"></canvas>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">📈 Évolution des moyennes (12 mois)</h6>
        <canvas id="chartEvolution" height="90"></canvas>
    </div></div></div>
    <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">🎯 Répartition des notes</h6>
        <canvas id="chartRepartitionNotes" height="180"></canvas>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">👥 Répartition étudiants</h6>
        <canvas id="chartRepartition" height="120"></canvas>
    </div></div></div>
    <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-body">
        <h6 class="text-muted mb-3">📊 Nouvelles inscriptions par mois</h6>
        <canvas id="chartInscriptions" height="120"></canvas>
    </div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-warning text-dark"><strong>🏆 Top 5 étudiants</strong></div>
        <div class="card-body p-0"><table class="table table-hover mb-0">
            <tbody>
            <?php foreach ($topEtudiants as $i => $t): ?>
                <tr>
                    <td><?= $i==0?'🥇':($i==1?'🥈':($i==2?'🥉':$i+1)) ?></td>
                    <td><?= htmlspecialchars($t['nom'].' '.$t['prenom']) ?></td>
                    <td><span class="badge bg-info"><?= $t['niveau'] ?></span></td>
                    <td><span class="badge bg-<?= $t['moy'] >= 10 ? 'success' : 'danger' ?>"><?= $t['moy'] ?>/20</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div></div>
    <div class="col-md-6"><div class="card shadow-sm h-100">
        <div class="card-header bg-success text-white"><strong>📝 Dernières notes</strong></div>
        <div class="card-body p-0"><table class="table table-sm mb-0"><tbody>
            <?php foreach ($dernieresNotes as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['nom'].' '.$n['prenom']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($n['libelle']) ?></small></td>
                    <td><span class="badge bg-<?= $n['valeur'] >= 10 ? 'success' : 'danger' ?>"><?= $n['valeur'] ?>/20</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table></div>
    </div></div>
</div>

<script>
new Chart(document.getElementById('chartMoyNiveau'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($moyNiveau, 'niveau')) ?>,
        datasets: [{ label: 'Moyenne', data: <?= json_encode(array_column($moyNiveau, 'moy')) ?>,
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'], borderRadius: 8 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 20 } } }
});

new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($evolution, 'mois')) ?>,
        datasets: [{ label: 'Moyenne', data: <?= json_encode(array_column($evolution, 'moy')) ?>,
            borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
            tension: 0.4, fill: true, pointRadius: 5 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 20 } } }
});

new Chart(document.getElementById('chartRepartitionNotes'), {
    type: 'doughnut',
    data: {
        labels: ['Excellent','Bien','Assez Bien','Passable','Insuffisant'],
        datasets: [{ data: [
            <?= $repartitionNotes['excellent'] ?>,<?= $repartitionNotes['bien'] ?>,
            <?= $repartitionNotes['assez_bien'] ?>,<?= $repartitionNotes['passable'] ?>,
            <?= $repartitionNotes['insuffisant'] ?>
        ], backgroundColor: ['#10b981','#22c55e','#84cc16','#f59e0b','#ef4444'] }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } } }
});

new Chart(document.getElementById('chartRepartition'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($repartition, 'niveau')) ?>,
        datasets: [{ data: <?= json_encode(array_column($repartition, 'nb')) ?>,
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'] }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('chartInscriptions'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($inscriptions, 'mois')) ?>,
        datasets: [{ label: 'Inscriptions', data: <?= json_encode(array_column($inscriptions, 'nb')) ?>,
            backgroundColor: '#8b5cf6', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>