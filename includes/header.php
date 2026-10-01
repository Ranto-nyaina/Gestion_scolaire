<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /gestion-scolaire/auth/login.php');
    exit;
}

$role = $_SESSION['role'];
$prenom = $_SESSION['prenom'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Scolaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        html, body { height: 100%; }
        body { background: #f4f6f9; overflow-x: hidden; }

        /* Layout principal */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background: #1e293b;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar a {
            color: #cbd5e1;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            border-radius: 6px;
            margin: 4px 8px;
            transition: 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #334155;
            color: #fff;
        }

        /* CONTENU */
        .main-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 20px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .page-content {
            padding: 24px;
            flex: 1;
        }

        /* Stats cards */
        .card-stat { border: none; border-radius: 12px; color: #fff; }

        /* Notifications */
        .notif-bell { position: relative; }
        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: #ef4444; color: white;
            border-radius: 10px; padding: 2px 6px;
            font-size: 10px; font-weight: bold;
        }
        .notif-dropdown {
            width: 380px; max-height: 450px; overflow-y: auto;
            padding: 0; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .notif-item { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.2s; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #eff6ff; border-left: 3px solid #3b82f6; }
        .notif-item .title { font-weight: 600; font-size: 14px; }
        .notif-item .msg { font-size: 13px; color: #64748b; }
        .notif-item .time { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 70px; min-width: 70px; }
            .sidebar .sidebar-text { display: none; }
            .sidebar h4 { font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="app-wrapper">