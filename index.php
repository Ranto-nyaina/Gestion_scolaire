<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $path = $_SESSION['role'] === 'admin' ? 'admin' : 'etudiant';
    header("Location: /gestion-scolaire/$path/dashboard.php");
} else {
    header('Location: /gestion-scolaire/auth/login.php');
}
exit;