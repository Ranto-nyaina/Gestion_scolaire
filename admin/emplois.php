<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO emplois_du_temps 
        (niveau, matiere_id, enseignant, jour, heure_debut, heure_fin, salle) 
        VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([
        $_POST['niveau'], $_POST['matiere_id'], $_POST['enseignant'],
        $_POST['jour'], $_POST['heure_debut'], $_POST['heure_fin'], $_POST['salle']
    ]);
    header('Location: emplois.php?niveau=' . urlencode($_POST['niveau'])); exit;
}

if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM emplois_du_temps WHERE id=?")->execute([$_GET['supprimer']]);
    header('Location: emplois.php?niveau=' . urlencode($_GET['niveau'] ?? '')); exit;
}

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY libelle")->fetchAll();
$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$niveaux = ['L1','L2','L3','M1','M2'];

$niveau = $_GET['niveau'] ?? '';
$edt = [];
if ($niveau) {
    $stmt = $pdo->prepare("SELECT et.*, m.libelle FROM emplois_du_temps et 
                           JOIN matieres m ON m.id = et.matiere_id 
                           WHERE et.niveau = ?");
    $stmt->execute([$niveau]);
    foreach ($stmt->fetchAll() as $row) $edt[$row['jour']][] = $row;
}
?>

<h2 class="mb-4">📅 Emplois du temps</h2>

<div class="card mb-4 shadow-sm"><div class="card-body">
<form method="POST" class="row g-2">
    <div class="col-md-2">
        <select name="niveau" class="form-select" required>
            <option value="">-- Niveau --</option>
            <?php foreach ($niveaux as $n): ?><option><?= $n ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="matiere_id" class="form-select" required>
            <option value="">-- Matière --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>"><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><input name="enseignant" class="form-control" placeholder="Enseignant" required></div>
    <div class="col-md-2">
        <select name="jour" class="form-select" required>
            <?php foreach ($jours as $j): ?><option><?= $j ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-1"><input type="time" name="heure_debut" class="form-control" required></div>
    <div class="col-md-1"><input type="time" name="heure_fin" class="form-control" required></div>
    <div class="col-md-1"><input name="salle" class="form-control" placeholder="Salle" required></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">+</button></div>
</form>
</div></div>

<form method="GET" class="mb-3">
    <select name="niveau" class="form-select w-25" onchange="this.form.submit()">
        <option value="">-- Choisir un niveau --</option>
        <?php foreach ($niveaux as $n): ?>
            <option <?= $n === $niveau ? 'selected' : '' ?>><?= $n ?></option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($niveau): ?>
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
                    <div class="bg-primary text-white p-2 rounded mb-1 position-relative">
                        <strong><?= $c['libelle'] ?></strong><br>
                        <small><?= $c['enseignant'] ?> — <?= $c['salle'] ?></small>
                        <a href="?supprimer=<?= $c['id'] ?>&niveau=<?= urlencode($niveau) ?>" 
                           class="text-white position-absolute top-0 end-0 p-1"
                           onclick="return confirm('Supprimer ?')">×</a>
                    </div>
                <?php endif; endforeach; ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>