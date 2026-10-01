<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$errors = [];
$matiereEdit = null;

// Ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $code    = strtoupper(trim($_POST['code']));
    $libelle = trim($_POST['libelle']);
    $coef    = (int)$_POST['coefficient'];

    if (empty($code))    $errors[] = "Le code est obligatoire";
    if (empty($libelle)) $errors[] = "Le libellé est obligatoire";
    if ($coef < 1)       $errors[] = "Coefficient minimum : 1";

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE code = ?");
        $chk->execute([$code]);
        if ($chk->fetchColumn() > 0) $errors[] = "Ce code existe déjà";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO matieres (code, libelle, coefficient) VALUES (?,?,?)");
        $stmt->execute([$code, $libelle, $coef]);
        header('Location: matieres.php?added=1'); exit;
    }
}

// Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $id      = (int)$_POST['id'];
    $code    = strtoupper(trim($_POST['code']));
    $libelle = trim($_POST['libelle']);
    $coef    = (int)$_POST['coefficient'];

    if (empty($code))    $errors[] = "Le code est obligatoire";
    if (empty($libelle)) $errors[] = "Le libellé est obligatoire";
    if ($coef < 1)       $errors[] = "Coefficient minimum : 1";

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE code = ? AND id != ?");
        $chk->execute([$code, $id]);
        if ($chk->fetchColumn() > 0) $errors[] = "Ce code existe déjà";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE matieres SET code=?, libelle=?, coefficient=? WHERE id=?");
        $stmt->execute([$code, $libelle, $coef, $id]);
        header('Location: matieres.php?updated=1'); exit;
    }
}

// Suppression
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    
    $chkNotes = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE matiere_id = ?");
    $chkNotes->execute([$id]);
    $nbNotes = $chkNotes->fetchColumn();

    $chkEdt = $pdo->prepare("SELECT COUNT(*) FROM emplois_du_temps WHERE matiere_id = ?");
    $chkEdt->execute([$id]);
    $nbEdt = $chkEdt->fetchColumn();

    $chkQcm = $pdo->prepare("SELECT COUNT(*) FROM qcm WHERE matiere_id = ?");
    $chkQcm->execute([$id]);
    $nbQcm = $chkQcm->fetchColumn();

    if ($nbNotes + $nbEdt + $nbQcm > 0) {
        header('Location: matieres.php?error=used'); exit;
    }

    $pdo->prepare("DELETE FROM matieres WHERE id=?")->execute([$id]);
    header('Location: matieres.php?deleted=1'); exit;
}

// Édition
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM matieres WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $matiereEdit = $stmt->fetch();
}

// Liste des matières avec compteurs
$matieres = $pdo->query("
    SELECT m.*, 
        (SELECT COUNT(*) FROM notes WHERE matiere_id = m.id) AS nb_notes,
        (SELECT COUNT(*) FROM emplois_du_temps WHERE matiere_id = m.id) AS nb_edt,
        (SELECT COUNT(*) FROM qcm WHERE matiere_id = m.id) AS nb_qcm,
        (SELECT ROUND(AVG(valeur), 2) FROM notes WHERE matiere_id = m.id) AS moy
    FROM matieres m 
    ORDER BY m.libelle
")->fetchAll();

$totalMatieres = count($matieres);
$coefTotal = array_sum(array_column($matieres, 'coefficient'));
?>

<h2 class="mb-4">📚 Gestion des matières</h2>

<?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success">✅ Matière ajoutée avec succès !</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-info">✏️ Matière modifiée avec succès !</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-warning">🗑️ Matière supprimée.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'used'): ?>
    <div class="alert alert-danger">
        ❌ Impossible de supprimer : cette matière est utilisée dans des notes, EDT ou QCM.
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat bg-primary p-3">
            <h6>Total matières</h6>
            <h2 class="mb-0"><?= $totalMatieres ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat bg-success p-3">
            <h6>Coefficient total</h6>
            <h2 class="mb-0"><?= $coefTotal ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat bg-warning p-3">
            <h6>Coefficient moyen</h6>
            <h2 class="mb-0"><?= $totalMatieres ? round($coefTotal / $totalMatieres, 2) : 0 ?></h2>
        </div>
    </div>
</div>

<!-- Formulaire -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
        <strong><?= $matiereEdit ? '✏️ Modifier la matière' : '➕ Nouvelle matière' ?></strong>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-2">
            <?php if ($matiereEdit): ?>
                <input type="hidden" name="id" value="<?= $matiereEdit['id'] ?>">
            <?php endif; ?>
            
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input name="code" class="form-control" placeholder="PHP101" required
                       value="<?= htmlspecialchars($matiereEdit['code'] ?? '') ?>"
                       pattern="[A-Za-z0-9]+" 
                       title="Lettres et chiffres uniquement">
            </div>
            <div class="col-md-5">
                <label class="form-label">Libellé</label>
                <input name="libelle" class="form-control" placeholder="Programmation PHP" required
                       value="<?= htmlspecialchars($matiereEdit['libelle'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Coefficient</label>
                <input type="number" name="coefficient" class="form-control" min="1" max="10" required
                       value="<?= htmlspecialchars($matiereEdit['coefficient'] ?? 1) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-1">
                <?php if ($matiereEdit): ?>
                    <button name="modifier" class="btn btn-info w-100">💾 Modifier</button>
                    <a href="matieres.php" class="btn btn-outline-secondary">✕</a>
                <?php else: ?>
                    <button name="ajouter" class="btn btn-primary w-100">➕ Ajouter</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Liste -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <strong>📋 Liste des matières</strong>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th class="text-center">Coef.</th>
                    <th class="text-center">Notes</th>
                    <th class="text-center">EDT</th>
                    <th class="text-center">QCM</th>
                    <th class="text-center">Moyenne</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($matieres)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">
                    Aucune matière. Ajoutez-en une !
                </td></tr>
            <?php endif; ?>
            <?php foreach ($matieres as $m): ?>
                <tr>
                    <td><span class="badge bg-dark"><?= htmlspecialchars($m['code']) ?></span></td>
                    <td><strong><?= htmlspecialchars($m['libelle']) ?></strong></td>
                    <td class="text-center">
                        <span class="badge bg-info">×<?= $m['coefficient'] ?></span>
                    </td>
                    <td class="text-center"><?= $m['nb_notes'] ?></td>
                    <td class="text-center"><?= $m['nb_edt'] ?></td>
                    <td class="text-center"><?= $m['nb_qcm'] ?></td>
                    <td class="text-center">
                        <?php if ($m['moy'] !== null): ?>
                            <span class="badge bg-<?= $m['moy'] >= 10 ? 'success' : 'danger' ?>">
                                <?= $m['moy'] ?>/20
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="?edit=<?= $m['id'] ?>" class="btn btn-sm btn-info" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="?supprimer=<?= $m['id'] ?>" class="btn btn-sm btn-danger" 
                           title="Supprimer"
                           onclick="return confirm('Supprimer la matière « <?= htmlspecialchars($m['libelle']) ?> » ?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>