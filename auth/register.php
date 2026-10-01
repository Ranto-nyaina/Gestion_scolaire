<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifier.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['nom']);
    $prenom    = trim($_POST['prenom']);
    $email     = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $matricule = trim($_POST['matricule']);
    $niveau    = $_POST['niveau'];
    $pass      = $_POST['mot_de_passe'];
    $pass2     = $_POST['mot_de_passe2'];

    if (!preg_match('/^[0-9]{3}[A-Z]-[A-Z]{3}$/', $matricule)) {
        $errors[] = "Matricule invalide (format : 200H-TOL)";
    }
    if (!preg_match('/^0[0-9]{9}$/', $telephone)) {
        $errors[] = "Téléphone invalide (format : 0340100101)";
    }
    if (!in_array($niveau, ['L1','L2','L3','M1','M2'])) {
        $errors[] = "Niveau invalide";
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
        $errors[] = "Mot de passe faible (min 8 car., 1 maj., 1 min., 1 chiffre, 1 spécial)";
    }
    if ($pass !== $pass2) {
        $errors[] = "Les deux mots de passe ne correspondent pas";
    }

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ? OR matricule = ?");
        $chk->execute([$email, $matricule]);
        if ($chk->fetchColumn() > 0) {
            $errors[] = "Email ou matricule déjà utilisé";
        }
    }

    if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs 
            (nom, prenom, email, telephone, matricule, niveau, mot_de_passe, role, statut) 
            VALUES (?,?,?,?,?,?,?, 'etudiant', 'en_attente')");
        $stmt->execute([$nom, $prenom, $email, $telephone, $matricule, $niveau, $hash]);
        
        notifierAdmins($pdo, 
            '🆕 Nouvelle inscription', 
            "$prenom $nom ($matricule) attend validation",
            '/gestion-scolaire/admin/etudiants.php',
            'person-plus',
            'warning'
        );
        
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center py-5">
    <div class="card shadow-lg" style="width: 550px;">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">🎓 Inscription Étudiant</h4>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ Votre demande a été envoyée.<br>
                    <strong>Elle sera validée par l'administrateur.</strong><br>
                    <a href="login.php" class="btn btn-primary mt-2">Retour connexion</a>
                </div>
            <?php else: ?>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="formInscription">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Nom</label>
                            <input name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Prénom</label>
                            <input name="prenom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Téléphone</label>
                            <input name="telephone" class="form-control" 
                                   placeholder="0340100101" pattern="0[0-9]{9}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Matricule</label>
                            <input name="matricule" class="form-control" 
                                   placeholder="200H-TOL" pattern="[0-9]{3}[A-Z]-[A-Z]{3}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Niveau</label>
                            <select name="niveau" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <option>L1</option><option>L2</option><option>L3</option>
                                <option>M1</option><option>M2</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Mot de passe</label>
                            <input type="password" name="mot_de_passe" id="pass1" class="form-control" required>
                            <small id="passInfo" class="text-muted"></small>
                        </div>
                        <div class="col-md-6">
                            <label>Confirmer</label>
                            <input type="password" name="mot_de_passe2" id="pass2" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-success w-100 mt-3">S'inscrire</button>
                    <a href="login.php" class="btn btn-link w-100">Déjà inscrit ? Se connecter</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const pass1 = document.getElementById('pass1');
const pass2 = document.getElementById('pass2');
const info  = document.getElementById('passInfo');

pass1?.addEventListener('input', () => {
    const v = pass1.value;
    const ok = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(v);
    info.textContent = v ? (ok ? "✅ Fort" : "❌ Faible (min 8, maj, min, chiffre, spécial)") : "";
    info.className = "small " + (ok ? "text-success" : "text-danger");
});

document.getElementById('formInscription')?.addEventListener('submit', e => {
    if (pass1.value !== pass2.value) {
        e.preventDefault();
        alert("Les mots de passe ne correspondent pas");
    }
});
</script>
</body>
</html>