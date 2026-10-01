<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$etudiantId = $_SESSION['user_id'];
$niveau     = $_SESSION['niveau'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_qcm'])) {
    $qcmId = $_POST['qcm_id'];
    $q = $pdo->prepare("SELECT * FROM questions WHERE qcm_id = ?");
    $q->execute([$qcmId]);
    $questions = $q->fetchAll();

    $note = 0; $totalPoints = 0;
    foreach ($questions as $quest) {
        $totalPoints += $quest['points'];
        $rep = $_POST['q_'.$quest['id']] ?? null;
        if ($rep) {
            $check = $pdo->prepare("SELECT est_correcte FROM propositions WHERE id=? AND question_id=?");
            $check->execute([$rep, $quest['id']]);
            if ($check->fetchColumn()) $note += $quest['points'];
        }
    }

    $tempsUtilise = (int)($_POST['temps_utilise'] ?? 0);
    $ins = $pdo->prepare("INSERT INTO resultats_qcm 
        (etudiant_id, qcm_id, note_obtenue, total_points, temps_utilise) 
        VALUES (?,?,?,?,?)");
    $ins->execute([$etudiantId, $qcmId, $note, $totalPoints, $tempsUtilise]);

    $message = "✅ Score : $note / $totalPoints";
    if (!empty($_POST['auto_submit'])) $message .= " (temps écoulé)";
}

$filterMatiere = $_GET['matiere'] ?? '';

$qcmActif = null; $questions = [];
if (isset($_GET['passer'])) {
    $stmt = $pdo->prepare("SELECT q.*, m.libelle FROM qcm q JOIN matieres m ON m.id = q.matiere_id 
                           WHERE q.id=? AND (q.niveau IS NULL OR q.niveau = ?)");
    $stmt->execute([$_GET['passer'], $niveau]);
    $qcmActif = $stmt->fetch();

    if ($qcmActif) {
        $q = $pdo->prepare("SELECT * FROM questions WHERE qcm_id=?");
        $q->execute([$qcmActif['id']]);
        $questions = $q->fetchAll();
        foreach ($questions as &$quest) {
            $p = $pdo->prepare("SELECT * FROM propositions WHERE question_id=?");
            $p->execute([$quest['id']]);
            $quest['propositions'] = $p->fetchAll();
        }
        unset($quest);
    }
}

$sql = "SELECT q.*, m.libelle FROM qcm q JOIN matieres m ON m.id = q.matiere_id 
        WHERE (q.niveau IS NULL OR q.niveau = ?)";
$params = [$niveau];
if ($filterMatiere) { $sql .= " AND q.matiere_id = ?"; $params[] = $filterMatiere; }
$sql .= " ORDER BY m.libelle";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$liste = $stmt->fetchAll();

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY libelle")->fetchAll();

$hist = $pdo->prepare("SELECT r.*, q.titre FROM resultats_qcm r 
                       JOIN qcm q ON q.id = r.qcm_id 
                       WHERE r.etudiant_id = ? ORDER BY r.date_passage DESC");
$hist->execute([$etudiantId]);
$historique = $hist->fetchAll();
?>

<h2 class="mb-4">✍️ QCM — <?= htmlspecialchars($niveau) ?></h2>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if ($qcmActif): ?>

    <div class="card shadow-sm mb-3 position-sticky" style="top: 60px; z-index: 100;">
        <div class="card-body d-flex justify-content-between align-items-center bg-dark text-white">
            <h5 class="mb-0">⏱️ Temps restant :</h5>
            <span id="timer" class="badge bg-success fs-4 px-3 py-2">--:--</span>
        </div>
        <div class="progress" style="height: 6px;">
            <div id="progressBar" class="progress-bar bg-success" style="width: 100%;"></div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5><?= htmlspecialchars($qcmActif['titre']) ?> — <?= htmlspecialchars($qcmActif['libelle']) ?></h5>
            <small>Durée : <?= $qcmActif['duree_minutes'] ?> minutes</small>
        </div>
        <div class="card-body">
        <form method="POST" id="qcmForm">
            <input type="hidden" name="qcm_id" value="<?= $qcmActif['id'] ?>">
            <input type="hidden" name="auto_submit" id="autoSubmit" value="0">
            <input type="hidden" name="temps_utilise" id="tempsUtilise" value="0">

            <?php foreach ($questions as $i => $q): ?>
                <div class="mb-4">
                    <h6><?= ($i+1).'. '.htmlspecialchars($q['texte_question']) ?> 
                        <span class="badge bg-info"><?= $q['points'] ?> pt(s)</span>
                    </h6>
                    <?php foreach ($q['propositions'] as $p): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="q_<?= $q['id'] ?>" value="<?= $p['id'] ?>">
                            <label class="form-check-label"><?= htmlspecialchars($p['texte_option']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <button name="submit_qcm" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Valider mes réponses
            </button>
            <a href="qcm.php" class="btn btn-secondary">Annuler</a>
        </form>
        </div>
    </div>

    <script>
    (function() {
        const dureeSecondes = <?= (int)$qcmActif['duree_minutes'] ?> * 60;
        let tempsRestant = dureeSecondes;
        const timerEl    = document.getElementById('timer');
        const progressEl = document.getElementById('progressBar');
        const form       = document.getElementById('qcmForm');

        function formatTemps(s) {
            const m = Math.floor(s / 60).toString().padStart(2, '0');
            const sec = (s % 60).toString().padStart(2, '0');
            return `${m}:${sec}`;
        }

        function mettreAJour() {
            timerEl.textContent = formatTemps(tempsRestant);
            const pct = (tempsRestant / dureeSecondes) * 100;
            progressEl.style.width = pct + '%';

            timerEl.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            progressEl.classList.remove('bg-success', 'bg-warning', 'bg-danger');

            if (tempsRestant <= 30) {
                timerEl.classList.add('bg-danger'); progressEl.classList.add('bg-danger');
            } else if (tempsRestant <= 120) {
                timerEl.classList.add('bg-warning'); progressEl.classList.add('bg-warning');
            } else {
                timerEl.classList.add('bg-success'); progressEl.classList.add('bg-success');
            }
        }

        const interval = setInterval(() => {
            tempsRestant--;
            mettreAJour();
            if (tempsRestant <= 0) {
                clearInterval(interval);
                document.getElementById('autoSubmit').value = '1';
                document.getElementById('tempsUtilise').value = dureeSecondes;
                Swal.fire({
                    icon: 'warning', title: 'Temps écoulé !',
                    text: 'Vos réponses vont être envoyées automatiquement.',
                    timer: 2000, timerProgressBar: true, showConfirmButton: false
                }).then(() => form.submit());
            }
        }, 1000);

        mettreAJour();
        form.addEventListener('submit', () => {
            document.getElementById('tempsUtilise').value = dureeSecondes - tempsRestant;
        });
        window.addEventListener('beforeunload', e => {
            if (tempsRestant > 0) { e.preventDefault(); e.returnValue = ''; }
        });
    })();
    </script>

<?php else: ?>

    <form method="GET" class="mb-3">
        <select name="matiere" class="form-select w-25" onchange="this.form.submit()">
            <option value="">-- Toutes matières --</option>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $m['id']==$filterMatiere?'selected':'' ?>><?= $m['libelle'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="row g-3 mb-4">
    <?php if (empty($liste)): ?>
        <div class="col-12"><div class="alert alert-info">Aucun QCM disponible.</div></div>
    <?php endif; ?>
    <?php foreach ($liste as $q): ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5><?= htmlspecialchars($q['titre']) ?></h5>
                    <p class="text-muted mb-2">
                        <span class="badge bg-info"><?= $q['niveau'] ?? 'Tous' ?></span>
                        <?= htmlspecialchars($q['libelle']) ?>
                    </p>
                    <p class="mb-2">⏱ <?= $q['duree_minutes'] ?> min</p>
                    <a href="?passer=<?= $q['id'] ?>" class="btn btn-primary">▶ Commencer</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <h4>📜 Mon historique</h4>
    <div class="card shadow-sm"><div class="card-body">
    <table class="table table-sm">
        <thead class="table-dark">
            <tr><th>QCM</th><th>Score</th><th>Temps utilisé</th><th>Date</th></tr>
        </thead>
        <tbody>
        <?php foreach ($historique as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['titre']) ?></td>
                <td><span class="badge bg-success"><?= $h['note_obtenue'] ?> / <?= $h['total_points'] ?></span></td>
                <td>
                    <?php if ($h['temps_utilise']): ?>
                        <?= floor($h['temps_utilise'] / 60) ?> min <?= $h['temps_utilise'] % 60 ?> sec
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td><?= $h['date_passage'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div></div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>