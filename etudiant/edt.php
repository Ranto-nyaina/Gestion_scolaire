<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$niveau = $_SESSION['niveau'];

$stmt = $pdo->prepare("SELECT et.*, m.libelle FROM emplois_du_temps et 
                       JOIN matieres m ON m.id = et.matiere_id 
                       WHERE et.niveau = ?");
$stmt->execute([$niveau]);
$edt = [];
foreach ($stmt->fetchAll() as $r) $edt[$r['jour']][] = $r;

$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
?>

<h2 class="mb-4">📅 Mon emploi du temps — <?= htmlspecialchars($niveau) ?></h2>

<div class="card shadow-sm"><div class="card-body">
<table class="table table-bordered text-center">
    <thead class="table-dark">
        <tr><th>Heure</th><?php foreach ($jours as $j) echo "<th>$j</th>"; ?></tr>
    </thead>
    <tbody>
    <?php foreach (['08:00','10:00','14:00','16:00'] as $h): ?>
        <tr>
            <th><?= $h ?></th>
            <?php foreach ($jours as $j): ?>
                <td>
                <?php foreach (($edt[$j] ?? []) as $c): 
                    if (substr($c['heure_debut'],0,2) === substr($h,0,2)): ?>
                    <div class="bg-primary text-white p-2 rounded mb-1">
                        <strong><?= htmlspecialchars($c['libelle']) ?></strong><br>
                        <small><?= htmlspecialchars($c['enseignant']) ?> — <?= $c['salle'] ?></small>
                    </div>
                <?php endif; endforeach; ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>