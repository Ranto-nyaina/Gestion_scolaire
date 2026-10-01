<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifier.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $hash = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs 
        (nom, prenom, email, telephone, mot_de_passe, role, matricule, niveau, statut) 
        VALUES (?,?,?,?,?, 'etudiant', ?, ?, 'valide')");
    $stmt->execute([
        $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'],
        $hash, $_POST['matricule'], $_POST['niveau']
    ]);
    header('Location: etudiants.php'); exit;
}

if (isset($_GET['valider'])) {
    $pdo->prepare("UPDATE utilisateurs SET statut='valide' WHERE id=?")->execute([$_GET['valider']]);
    notifier($pdo, $_GET['valider'], '✅ Compte validé', 
        'Votre inscription a été validée. Bienvenue !', 
        '/gestion-scolaire/auth/login.php', 'check-circle', 'success');
    header('Location: etudiants.php'); exit;
}
if (isset($_GET['rejeter'])) {
    $pdo->prepare("UPDATE utilisateurs SET statut='rejete' WHERE id=?")->execute([$_GET['rejeter']]);
    notifier($pdo, $_GET['rejeter'], '❌ Inscription rejetée', 
        'Votre demande a été rejetée.', 
        null, 'x-circle', 'danger');
    header('Location: etudiants.php'); exit;
}
if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM utilisateurs WHERE id=? AND role='etudiant'")->execute([$_GET['supprimer']]);
    header('Location: etudiants.php'); exit;
}

$niveau = $_GET['niveau'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM utilisateurs WHERE role='etudiant'";
$params = [];
if ($niveau) { $sql .= " AND niveau = ?"; $params[] = $niveau; }
if ($search) { 
    $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR matricule LIKE ?)"; 
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY statut, nom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$etudiants = $stmt->fetchAll();

$nbEnAttente = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant' AND statut='en_attente'")->fetchColumn();
?>

<h2 class="mb-4">👥 Gestion des étudiants</h2>

<?php if ($nbEnAttente > 0): ?>
<div class="alert alert-warning">
    <i class="bi bi-hourglass-split"></i> <strong><?= $nbEnAttente ?></strong> inscription(s) en attente.
</div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3 gap-2">
    <form class="d-flex gap-2 flex-grow-1" method="GET">
        <select name="niveau" class="form-select" style="max-width:150px;" onchange="this.form.submit()">
            <option value="">-- Niveau --</option>
            <?php foreach (['L1','L2','L3','M1','M2'] as $n): ?>
                <option <?= $n===$niveau?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
        <input name="search" class="form-control" placeholder="Rechercher" 
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-secondary">🔍</button>
        <a href="etudiants.php" class="btn btn-outline-danger">✕</a>
    </form>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjout">
        <i class="bi bi-plus-circle"></i> Nouvel étudiant
    </button>
</div>

<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover">
    <thead class="table-dark">
        <tr><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Email</th>
            <th>Tél</th><th>Niveau</th><th>Statut</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($etudiants as $e): ?>
        <tr class="<?= $e['statut']==='en_attente'?'table-warning':'' ?>">
            <td><?= htmlspecialchars($e['matricule']) ?></td>
            <td><?= htmlspecialchars($e['nom']) ?></td>
            <td><?= htmlspecialchars($e['prenom']) ?></td>
            <td><?= htmlspecialchars($e['email']) ?></td>
            <td><?= htmlspecialchars($e['telephone']) ?></td>
            <td><span class="badge bg-info"><?= $e['niveau'] ?></span></td>
            <td>
                <?php if ($e['statut']==='valide'): ?>
                    <span class="badge bg-success">✅ Validé</span>
                <?php elseif ($e['statut']==='en_attente'): ?>
                    <span class="badge bg-warning text-dark">⏳ En attente</span>
                <?php else: ?>
                    <span class="badge bg-danger">❌ Rejeté</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($e['statut']==='en_attente'): ?>
                    <a href="?valider=<?= $e['id'] ?>" class="btn btn-sm btn-success">✔</a>
                    <a href="?rejeter=<?= $e['id'] ?>" class="btn btn-sm btn-warning">✖</a>
                <?php endif; ?>
                <a href="/gestion-scolaire/export/pdf_bulletin.php?id=<?= $e['id'] ?>" 
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-pdf"></i>
                </a>
                <a href="?supprimer=<?= $e['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>

<div class="modal fade" id="modalAjout">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header"><h5>Nouvel étudiant</h5></div>
      <div class="modal-body">
        <input name="nom" class="form-control mb-2" placeholder="Nom" required>
        <input name="prenom" class="form-control mb-2" placeholder="Prénom" required>
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input name="telephone" class="form-control mb-2" placeholder="0340100101" pattern="0[0-9]{9}" required>
        <input name="mot_de_passe" type="password" class="form-control mb-2" placeholder="Mot de passe" required>
        <input name="matricule" class="form-control mb-2" placeholder="200H-TOL" required>
        <select name="niveau" class="form-select mb-2" required>
            <option value="">-- Niveau --</option>
            <option>L1</option><option>L2</option><option>L3</option>
            <option>M1</option><option>M2</option>
        </select>
      </div>
      <div class="modal-footer">
        <button name="ajouter" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>