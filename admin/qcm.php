<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_qcm'])) {
    $niveau = !empty($_POST['niveau']) ? $_POST['niveau'] : null;
    $stmt = $pdo->prepare("INSERT INTO qcm (titre, matiere_id, niveau, duree_minutes) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['titre'], $_POST['matiere_id'], $niveau, $_POST['duree']]);
    header('Location: qcm.php?qcm=' . $pdo->lastInsertId()); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_question'])) {
    $stmt = $pdo->prepare("INSERT INTO questions (qcm_id, texte_question, points) VALUES (?,?,?)");
    $stmt->execute([$_POST['qcm_id'], $_POST['texte_question'], $_POST['points']]);
    $qid = $pdo->lastInsertId();
    $ins = $pdo->prepare("INSERT INTO propositions (question_id, texte_option, est_correcte) VALUES (?,?,?)");
    foreach (['a','b','c','d'] as $opt) {
        if (!empty($_POST['option_'.$opt])) {
            $ins->execute([$qid, $_POST['option_'.$opt], ($_POST['bonne'] ?? '') === $opt ? 1 : 0]);
        }
    }
    header('Location: qcm.php?qcm=' . $_POST['qcm_id']); exit;
}

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY libelle")->fetchAll();
$niveaux  = ['L1','L2','L3','M1','M2'];

$filterNiveau  = $_GET['f_niveau'] ?? '';
$filterMatiere = $_GET['f_matiere'] ?? '';

$sql = "SELECT q.*, m.libelle FROM qcm q JOIN matieres m ON m.id = q.matiere_id WHERE 1=1";
$params = [];
if ($filterNiveau) { $sql .= " AND q.niveau = ?"; $params[] = $filterNiveau; }
if ($filterMatiere) { $sql .= " AND q.matiere_id = ?"; $params[] = $filterMatiere; }
$sql .= " ORDER BY q.niveau, m.libelle";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$qcmList = $stmt->fetchAll();

$qcmActif = null;
$questions = [];
if (isset($_GET['qcm'])) {
    $stmt = $pdo->prepare("SELECT q.*, m.libelle FROM qcm q JOIN matieres m ON m.id = q.matiere_id WHERE q.id=?");
    $stmt->execute([$_GET['qcm']]);
    $qcmActif = $stmt->fetch();
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE qcm_id=?");
    $stmt->execute([$qcmActif['id']]);
    $questions = $stmt->fetchAll();
    foreach ($questions as &$q) {
        $s = $pdo->prepare("SELECT * FROM propositions WHERE question_id=?");
        $s->execute([$q['id']]);
        $q['propositions'] = $s->fetchAll();
    }
    unset($q);
}
?>

<h2 class="mb-4">📝 Gestion des QCM</h2>

<div class="card mb-3 shadow-sm"><div class="card-body">
<form method="POST" class="row g-2">
    <div class="col-md-3"><input name="titre" class="form-control" placeholder="Titre" required></div>
    <div class="col-md-2">
        <select name="matiere_id" class="form-select" required>
            <option value="">-- Matière --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>"><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="niveau" class="form-select">
            <option value="">-- Tous niveaux --</option>
            <?php foreach ($niveaux as $n): ?><option><?= $n ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><input type="number" name="duree" class="form-control" value="30"></div>
    <div class="col-md-2"><button name="creer_qcm" class="btn btn-primary w-100">Créer</button></div>
</form>
</div></div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="f_niveau" class="form-select" onchange="this.form.submit()">
            <option value="">-- Tous niveaux --</option>
            <?php foreach ($niveaux as $n): ?>
                <option <?= $n===$filterNiveau?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="f_matiere" class="form-select" onchange="this.form.submit()">
            <option value="">-- Toutes matières --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $m['id']==$filterMatiere?'selected':'' ?>><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><a href="qcm.php" class="btn btn-outline-danger w-100">✕</a></div>
</form>

<div class="row">
    <div class="col-md-4">
        <div class="list-group">
        <?php foreach ($qcmList as $q): ?>
            <a href="?qcm=<?= $q['id'] ?>" 
               class="list-group-item list-group-item-action <?= ($qcmActif && $qcmActif['id']==$q['id']) ? 'active' : '' ?>">
                <strong><?= htmlspecialchars($q['titre']) ?></strong><br>
                <small>
                    <?php if ($q['niveau']): ?>
                        <span class="badge bg-info"><?= $q['niveau'] ?></span>
                    <?php endif; ?>
                    <?= $q['libelle'] ?> — <?= $q['duree_minutes'] ?> min
                </small>
            </a>
        <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-8">
    <?php if ($qcmActif): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <h5><?= htmlspecialchars($qcmActif['titre']) ?></h5>
                <span>
                    <?php if ($qcmActif['niveau']): ?>
                        <span class="badge bg-info"><?= $qcmActif['niveau'] ?></span>
                    <?php endif; ?>
                    <span class="badge bg-secondary"><?= $qcmActif['libelle'] ?></span>
                </span>
            </div>
            <div class="card-body">
                <?php foreach ($questions as $i => $q): ?>
                    <div class="border rounded p-2 mb-2">
                        <strong>Q<?= $i+1 ?>. <?= htmlspecialchars($q['texte_question']) ?></strong>
                        <span class="badge bg-info"><?= $q['points'] ?> pt(s)</span>
                        <ul class="mt-2 mb-0">
                        <?php foreach ($q['propositions'] as $p): ?>
                            <li><?= htmlspecialchars($p['texte_option']) ?> <?= $p['est_correcte'] ? '✅' : '' ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <hr>
                <form method="POST">
                    <input type="hidden" name="qcm_id" value="<?= $qcmActif['id'] ?>">
                    <textarea name="texte_question" class="form-control mb-2" placeholder="Question" required></textarea>
                    <div class="row g-2">
                        <div class="col-md-6"><input name="option_a" class="form-control" placeholder="Option A" required></div>
                        <div class="col-md-6"><input name="option_b" class="form-control" placeholder="Option B" required></div>
                        <div class="col-md-6"><input name="option_c" class="form-control" placeholder="Option C"></div>
                        <div class="col-md-6"><input name="option_d" class="form-control" placeholder="Option D"></div>
                        <div class="col-md-4">
                            <select name="bonne" class="form-select">
                                <option value="a">Réponse A</option>
                                <option value="b">Réponse B</option>
                                <option value="c">Réponse C</option>
                                <option value="d">Réponse D</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input type="number" name="points" class="form-control" value="1"></div>
                        <div class="col-md-5"><button name="ajouter_question" class="btn btn-success w-100">Ajouter</button></div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Sélectionnez un QCM ou créez-en un.</div>
    <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>