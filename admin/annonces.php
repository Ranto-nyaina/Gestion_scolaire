<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifier.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $niveau = !empty($_POST['niveau']) ? $_POST['niveau'] : null;
    $stmt = $pdo->prepare("INSERT INTO annonces (titre, contenu, auteur_id, cible, niveau) VALUES (?,?,?,?,?)");
    $stmt->execute([$_POST['titre'], $_POST['contenu'], $_SESSION['user_id'], $_POST['cible'], $niveau]);
    
    // Notifier les étudiants concernés
    if ($_POST['cible'] === 'etudiants' || $_POST['cible'] === 'tous') {
        if ($niveau) {
            notifierNiveau($pdo, $niveau, '📢 Nouvelle annonce', $_POST['titre'],
                '/gestion-scolaire/etudiant/annonces.php', 'megaphone', 'info');
        } else {
            $etudiants = $pdo->query("SELECT id FROM utilisateurs WHERE role='etudiant' AND statut='valide'")->fetchAll();
            foreach ($etudiants as $e) {
                notifier($pdo, $e['id'], '📢 Nouvelle annonce', $_POST['titre'],
                    '/gestion-scolaire/etudiant/annonces.php', 'megaphone', 'info');
            }
        }
    }
    
    header('Location: annonces.php'); exit;
}

if (isset($_GET['sup'])) {
    $pdo->prepare("DELETE FROM annonces WHERE id=?")->execute([$_GET['sup']]);
    header('Location: annonces.php'); exit;
}

$niveau = $_GET['niveau'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, u.nom, u.prenom FROM annonces a 
        JOIN utilisateurs u ON u.id = a.auteur_id WHERE 1=1";
$params = [];
if ($niveau) { $sql .= " AND a.niveau = ?"; $params[] = $niveau; }
if ($search) { 
    $sql .= " AND (a.titre LIKE ? OR a.contenu LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY a.date_publication DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll();
?>

<h2 class="mb-4">📢 Annonces</h2>

<div class="card mb-4 shadow-sm"><div class="card-body">
<form method="POST">
    <input name="titre" class="form-control mb-2" placeholder="Titre" required>
    <textarea name="contenu" class="form-control mb-2" rows="3" placeholder="Contenu" required></textarea>
    <div class="d-flex gap-2">
        <select name="cible" class="form-select w-25">
            <option value="tous">Tous</option>
            <option value="etudiants">Étudiants</option>
            <option value="admin">Admin</option>
        </select>
        <select name="niveau" class="form-select w-25">
            <option value="">-- Tous niveaux --</option>
            <option>L1</option><option>L2</option><option>L3</option>
            <option>M1</option><option>M2</option>
        </select>
        <button class="btn btn-primary">Publier</button>
    </div>
</form>
</div></div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-2">
        <select name="niveau" class="form-select" onchange="this.form.submit()">
            <option value="">-- Tous niveaux --</option>
            <?php foreach (['L1','L2','L3','M1','M2'] as $n): ?>
                <option <?= $n===$niveau?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-5">
        <input name="search" class="form-control" placeholder="Rechercher" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">🔍</button></div>
    <div class="col-md-2"><a href="annonces.php" class="btn btn-outline-danger w-100">✕</a></div>
</form>

<?php foreach ($annonces as $a): ?>
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5><?= htmlspecialchars($a['titre']) ?></h5>
            <a href="?sup=<?= $a['id'] ?>" class="text-danger"
               onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
        </div>
        <p><?= nl2br(htmlspecialchars($a['contenu'])) ?></p>
        <small class="text-muted">
            Par <strong><?= $a['nom'].' '.$a['prenom'] ?></strong> — <?= $a['date_publication'] ?>
            <span class="badge bg-secondary"><?= $a['cible'] ?></span>
            <?php if ($a['niveau']): ?>
                <span class="badge bg-info"><?= $a['niveau'] ?></span>
            <?php else: ?>
                <span class="badge bg-light text-dark">Tous niveaux</span>
            <?php endif; ?>
        </small>
    </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>