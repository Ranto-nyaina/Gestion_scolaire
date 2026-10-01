<!-- ==================== SIDEBAR ==================== -->
<aside class="sidebar">
    <h4 class="text-center py-3 border-bottom border-secondary mb-0">
        <i class="bi bi-mortarboard-fill"></i>
        <span class="sidebar-text"> Scolarité</span>
    </h4>
    <div class="text-center text-light small py-2 border-bottom border-secondary">
        <span class="sidebar-text">Connecté : <strong><?= htmlspecialchars($prenom) ?></strong></span>
    </div>
    <nav class="mt-2">
    <?php if ($role === 'admin'): ?>
        <a href="/gestion-scolaire/admin/dashboard.php"><i class="bi bi-speedometer2"></i> <span class="sidebar-text">Dashboard</span></a>
        <a href="/gestion-scolaire/admin/etudiants.php"><i class="bi bi-people"></i> <span class="sidebar-text">Étudiants</span></a>
        <a href="/gestion-scolaire/admin/matieres.php"><i class="bi bi-book"></i> <span class="sidebar-text">Matières</span></a>
        <a href="/gestion-scolaire/admin/notes.php"><i class="bi bi-journal-check"></i> <span class="sidebar-text">Notes</span></a>
        <a href="/gestion-scolaire/admin/emplois.php"><i class="bi bi-calendar-week"></i> <span class="sidebar-text">Emplois du temps</span></a>
        <a href="/gestion-scolaire/admin/annonces.php"><i class="bi bi-megaphone"></i> <span class="sidebar-text">Annonces</span></a>
        <a href="/gestion-scolaire/admin/qcm.php"><i class="bi bi-question-circle"></i> <span class="sidebar-text">QCM</span></a>
    <?php else: ?>
        <a href="/gestion-scolaire/etudiant/dashboard.php"><i class="bi bi-house"></i> <span class="sidebar-text">Accueil</span></a>
        <a href="/gestion-scolaire/etudiant/notes.php"><i class="bi bi-journal-text"></i> <span class="sidebar-text">Mes notes</span></a>
        <a href="/gestion-scolaire/etudiant/edt.php"><i class="bi bi-calendar-week"></i> <span class="sidebar-text">Mon emploi du temps</span></a>
        <a href="/gestion-scolaire/etudiant/annonces.php"><i class="bi bi-megaphone"></i> <span class="sidebar-text">Annonces</span></a>
        <a href="/gestion-scolaire/etudiant/qcm.php"><i class="bi bi-pencil-square"></i> <span class="sidebar-text">QCM</span></a>
    <?php endif; ?>
        <a href="/gestion-scolaire/auth/logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> <span class="sidebar-text">Déconnexion</span>
        </a>
    </nav>
</aside>

<!-- ==================== CONTENU PRINCIPAL ==================== -->
<main class="main-content">

    <!-- TOPBAR avec notifications -->
    <div class="topbar">
        <div class="dropdown notif-bell">
            <button class="btn btn-light position-relative" data-bs-toggle="dropdown" id="notifBtn">
                <i class="bi bi-bell-fill fs-5"></i>
                <span class="notif-badge d-none" id="notifBadge">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                    <strong>🔔 Notifications</strong>
                    <button class="btn btn-sm btn-outline-primary" onclick="markAllRead()">Tout lire</button>
                </div>
                <div id="notifList">
                    <div class="text-center p-4 text-muted">Chargement...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENU DE LA PAGE -->
    <div class="page-content">