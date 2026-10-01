<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';

if ($action === 'count') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lue = FALSE");
    $stmt->execute([$userId]);
    echo json_encode(['count' => (int)$stmt->fetchColumn()]);
    exit;
}

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? 
                           ORDER BY cree_le DESC LIMIT 10");
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'read' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET lue = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $userId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'read_all') {
    $stmt = $pdo->prepare("UPDATE notifications SET lue = TRUE WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'invalid action']);