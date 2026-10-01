<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['mot_de_passe'])) {
        if ($user['role'] === 'etudiant') {
            if ($user['statut'] === 'en_attente') {
                $error = "⏳ Votre compte est en attente de validation par l'administrateur.";
            } elseif ($user['statut'] === 'rejete') {
                $error = "❌ Votre demande d'inscription a été rejetée.";
            }
        }

        if (empty($error)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['niveau']  = $user['niveau'];

            $redirect = $user['role'] === 'admin' ? 'admin' : 'etudiant';
            header("Location: /gestion-scolaire/$redirect/dashboard.php");
            exit;
        }
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg" style="width: 420px;">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">🎓 Connexion</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Se connecter</button>
                <a href="register.php" class="btn btn-link w-100">Pas de compte ? S'inscrire</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>