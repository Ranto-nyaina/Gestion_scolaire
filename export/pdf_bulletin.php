<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id'])) {
    header('Location: /gestion-scolaire/auth/login.php');
    exit;
}

$etudiantId = $_GET['id'] ?? $_SESSION['user_id'];

if ($_SESSION['role'] === 'etudiant' && $etudiantId != $_SESSION['user_id']) {
    die("Accès refusé");
}

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ? AND role='etudiant'");
$stmt->execute([$etudiantId]);
$etudiant = $stmt->fetch();

if (!$etudiant) die("Étudiant introuvable");

$stmt = $pdo->prepare("SELECT n.*, m.libelle, m.coefficient 
                       FROM notes n 
                       JOIN matieres m ON m.id = n.matiere_id 
                       WHERE n.etudiant_id = ? 
                       ORDER BY m.libelle");
$stmt->execute([$etudiantId]);
$notes = $stmt->fetchAll();

$total = 0; $coefs = 0;
foreach ($notes as $n) {
    $total += $n['valeur'] * $n['coefficient'];
    $coefs += $n['coefficient'];
}
$moyenne = $coefs ? round($total / $coefs, 2) : 0;

if ($moyenne >= 16)      $mention = "Très Bien";
elseif ($moyenne >= 14)  $mention = "Bien";
elseif ($moyenne >= 12)  $mention = "Assez Bien";
elseif ($moyenne >= 10)  $mention = "Passable";
else                     $mention = "Insuffisant";

$html = '
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
    .header { text-align: center; border-bottom: 3px solid #1e293b; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { color: #1e293b; margin: 0; }
    .header p { margin: 3px 0; color: #666; }
    .info-box { background: #f1f5f9; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    .info-box table { width: 100%; }
    .info-box td { padding: 3px; }
    table.notes { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.notes th { background: #1e293b; color: white; padding: 8px; text-align: left; }
    table.notes td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
    table.notes tr:nth-child(even) { background: #f8fafc; }
    .moyenne-box { margin-top: 20px; padding: 15px; background: ' . ($moyenne >= 10 ? '#dcfce7' : '#fee2e2') . '; border-radius: 8px; text-align: center; }
    .moyenne-box h2 { margin: 0; color: ' . ($moyenne >= 10 ? '#16a34a' : '#dc2626') . '; font-size: 32px; }
    .moyenne-box p { margin: 5px 0 0; font-weight: bold; }
    .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    .badge { padding: 3px 8px; border-radius: 4px; color: white; font-size: 10px; }
    .badge-success { background: #16a34a; }
    .badge-danger { background: #dc2626; }
</style></head><body>
<div class="header">
    <h1>🎓 RELEVÉ DE NOTES</h1>
    <p>Année universitaire ' . date('Y') . ' - ' . (date('Y')+1) . '</p>
</div>
<div class="info-box">
    <table>
        <tr><td><strong>Nom :</strong> ' . htmlspecialchars($etudiant['nom']) . '</td>
            <td><strong>Prénom :</strong> ' . htmlspecialchars($etudiant['prenom']) . '</td></tr>
        <tr><td><strong>Matricule :</strong> ' . htmlspecialchars($etudiant['matricule']) . '</td>
            <td><strong>Niveau :</strong> ' . htmlspecialchars($etudiant['niveau']) . '</td></tr>
        <tr><td><strong>Email :</strong> ' . htmlspecialchars($etudiant['email']) . '</td>
            <td><strong>Téléphone :</strong> ' . htmlspecialchars($etudiant['telephone']) . '</td></tr>
    </table>
</div>
<table class="notes">
    <thead><tr>
        <th>Matière</th><th style="text-align:center">Coef.</th>
        <th style="text-align:center">Semestre</th><th style="text-align:center">Note /20</th>
        <th style="text-align:center">Résultat</th>
    </tr></thead><tbody>';

foreach ($notes as $n) {
    $html .= '<tr>
        <td>' . htmlspecialchars($n['libelle']) . '</td>
        <td style="text-align:center">' . $n['coefficient'] . '</td>
        <td style="text-align:center">S' . $n['semestre'] . '</td>
        <td style="text-align:center"><strong>' . $n['valeur'] . '</strong></td>
        <td style="text-align:center">
            <span class="badge ' . ($n['valeur'] >= 10 ? 'badge-success' : 'badge-danger') . '">
                ' . ($n['valeur'] >= 10 ? 'Validé' : 'Non validé') . '
            </span>
        </td>
    </tr>';
}

$html .= '</tbody></table>
<div class="moyenne-box">
    <p>MOYENNE GÉNÉRALE PONDÉRÉE</p>
    <h2>' . $moyenne . ' / 20</h2>
    <p>Mention : ' . $mention . '</p>
</div>
<div class="footer">Document généré le ' . date('d/m/Y à H:i') . ' — Gestion Scolaire</div>
</body></html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'bulletin_' . $etudiant['matricule'] . '_' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit;