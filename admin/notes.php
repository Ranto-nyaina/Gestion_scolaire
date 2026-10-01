<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifier.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO notes (etudiant_id, matiere_id, valeur, semestre) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['etudiant_id'], $_POST['matiere_id'], $_POST['valeur'], $_POST['semestre']]);
    
    $matiereNom = $pdo->query("SELECT libelle FROM matieres WHERE id={$_POST['matiere_id']}")->fetchColumn();
    notifier($pdo, $_POST['etudiant_id'], '📝 Nouvelle note',
        "Note de {$_POST['valeur']}/20 en $matiereNom",
        '/gestion-scolaire/etudiant/notes.php', 'journal-check', 'info');
    
    header('Location: notes.php'); exit;
}

if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM notes WHERE id=?")->execute([$_GET['supprimer']]);
    header('Location: notes.php'); exit;
}

$niveau    = $_GET['niveau'] ?? '';
$matiereId = $_GET['matiere'] ?? '';
$search    = trim($_GET['search'] ?? '');
$semestre  = $_GET['semestre'] ?? '';

$sql = "SELECT n.*, u.nom, u.prenom, u.matricule, u.niveau, m.libelle 
        FROM notes n 
        JOIN utilisateurs u ON u.id = n.etudiant_id
        JOIN matieres m ON m.id = n.matiere_id
        WHERE 1=1";
$params = [];
if ($niveau)    { $sql .= " AND u.niveau = ?"; $params[] = $niveau; }
if ($matiereId) { $sql .= " AND n.matiere_id = ?"; $params[] = $matiereId; }
if ($semestre)  { $sql .= " AND n.semestre = ?"; $params[] = $semestre; }
if ($search) {
    $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.matricule LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY n.date_saisie DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll();

$totalFiltre   = count($notes);
$moyFiltre     = $totalFiltre ? round(array_sum(array_column($notes, 'valeur')) / $totalFiltre, 2) : 0;
$notesReussies = count(array_filter($notes, fn($n) => $n['valeur'] >= 10));
$tauxReussite  = $totalFiltre ? round(($notesReussies / $totalFiltre) * 100, 1) : 0;

$etudiants = $pdo->query("SELECT id, nom, prenom, matricule, niveau FROM utilisateurs 
                          WHERE role='etudiant' AND statut='valide' ORDER BY nom")->fetchAll();
$matieres  = $pdo->query("SELECT * FROM matieres ORDER BY libelle")->fetchAll();
?>

<h2 class="mb-4">📊 Gestion des notes</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card bg-primary text-white p-3">
        <h6>Notes</h6><h3 class="mb-0"><?= $totalFiltre ?></h3></div></div>
    <div class="col-md-3"><div class="card bg-<?= $moyFiltre >= 10 ? 'success' : 'danger' ?> text-white p-3">
        <h6>Moyenne</h6><h3 class="mb-0"><?= $moyFiltre ?>/20</h3></div></div>
    <div class="col-md-3"><div class="card bg-info text-white p-3">
        <h6>Taux réussite</h6><h3 class="mb-0"><?= $tauxReussite ?>%</h3></div></div>
    <div class="col-md-3"><div class="card bg-warning text-dark p-3">
        <h6>Notes ≥ 10</h6><h3 class="mb-0"><?= $notesReussies ?>/<?= $totalFiltre ?></h3></div></div>
</div>

<div class="card mb-4 shadow-sm"><div class="card-body">
<form method="POST" class="row g-2">
    <div class="col-md-3">
        <select name="etudiant_id" class="form-select" required>
            <option value="">-- Étudiant --</option>
            <?php foreach ($etudiants as $e): ?>
                <option value="<?= $e['id'] ?>">[<?= $e['niveau'] ?>] <?= $e['nom'].' '.$e['prenom'].' ('.$e['matricule'].')' ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="matiere_id" class="form-select" required>
            <option value="">-- Matière --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>"><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <input type="number" step="0.01" min="0" max="20" name="valeur" class="form-control" placeholder="Note /20" required>
    </div>
    <div class="col-md-2">
        <select name="semestre" class="form-select">
            <option value="1">Semestre 1</option><option value="2">Semestre 2</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
</form>
</div></div>

<div class="card mb-4 shadow-sm"><div class="card-body">
<form method="GET" class="row g-2">
    <div class="col-md-2">
        <select name="niveau" class="form-select">
            <option value="">-- Niveau --</option>
            <?php foreach (['L1','L2','L3','M1','M2'] as $n): ?>
                <option <?= $n===$niveau?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="matiere" class="form-select">
            <option value="">-- Toutes matières --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $m['id']==$matiereId?'selected':'' ?>><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="semestre" class="form-select">
            <option value="">-- Semestre --</option>
            <option value="1" <?= $semestre==='1'?'selected':'' ?>>S1</option>
            <option value="2" <?= $semestre==='2'?'selected':'' ?>>S2</option>
        </select>
    </div>
    <div class="col-md-3">
        <input name="search" class="form-control" placeholder="Nom / Matricule" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 d-flex gap-1">
        <button class="btn btn-primary flex-grow-1">🔍</button>
        <a href="notes.php" class="btn btn-outline-danger">✕</a>
    </div>
</form>
</div></div>

<div class="card shadow-sm"><div class="card-body">
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr><th>Étudiant</th><th>Niveau</th><th>Matière</th><th>Note</th><th>Sem.</th><th>Date</th><th>PDF</th><th></th></tr>
    </thead>
    <tbody>
    <?php if (empty($notes)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Aucune note</td></tr>
    <?php endif; ?>
    <?php foreach ($notes as $n): ?>
        <tr>
            <td><?= htmlspecialchars($n['nom'].' '.$n['prenom']) ?> <small class="text-muted">(<?= $n['matricule'] ?>)</small></td>
            <td><span class="badge bg-info"><?= $n['niveau'] ?></span></td>
            <td><?= htmlspecialchars($n['libelle']) ?></td>
            <td><span class="badge bg-<?= $n['valeur'] >= 10 ? 'success' : 'danger' ?>"><?= $n['valeur'] ?>/20</span></td>
            <td>S<?= $n['semestre'] ?></td>
            <td><small><?= date('d/m/Y', strtotime($n['date_saisie'])) ?></small></td>
            <td>
                <a href="/gestion-scolaire/export/pdf_bulletin.php?id=<?= $n['etudiant_id'] ?>" 
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </td>
            <td>
                <a href="?supprimer=<?= $n['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>