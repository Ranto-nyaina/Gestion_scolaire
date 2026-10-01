    </div><!-- /.page-content -->
</main><!-- /.main-content -->

</div><!-- /.app-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let lastNotifCount = 0;

async function loadNotifCount() {
    try {
        const res = await fetch('/gestion-scolaire/api/notifications.php?action=count');
        const data = await res.json();
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        if (data.count > 0) {
            badge.textContent = data.count > 9 ? '9+' : data.count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
        lastNotifCount = data.count;
    } catch (e) {}
}

async function loadNotifList() {
    try {
        const res = await fetch('/gestion-scolaire/api/notifications.php?action=list');
        const data = await res.json();
        const list = document.getElementById('notifList');
        if (!list) return;
        
        if (data.length === 0) {
            list.innerHTML = '<div class="text-center p-4 text-muted">Aucune notification</div>';
            return;
        }
        
        list.innerHTML = data.map(n => `
            <div class="notif-item ${n.lue == 0 ? 'unread' : ''}" onclick="readNotif(${n.id}, '${n.lien || ''}')">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-${n.icone} text-${n.couleur} fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="title">${n.titre}</div>
                        <div class="msg">${n.message}</div>
                        <div class="time">${timeAgo(n.cree_le)}</div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (e) {}
}

function timeAgo(dateStr) {
    const date = new Date(dateStr.replace(' ', 'T'));
    const diff = Math.floor((new Date() - date) / 1000);
    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff/60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff/3600)} h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff/86400)} j`;
    return date.toLocaleDateString('fr-FR');
}

async function readNotif(id, lien) {
    await fetch('/gestion-scolaire/api/notifications.php?action=read&id=' + id);
    loadNotifCount();
    loadNotifList();
    if (lien) setTimeout(() => window.location.href = lien, 200);
}

async function markAllRead() {
    await fetch('/gestion-scolaire/api/notifications.php?action=read_all');
    loadNotifCount();
    loadNotifList();
}

document.addEventListener('DOMContentLoaded', () => {
    loadNotifCount();
    const btn = document.getElementById('notifBtn');
    if (btn) btn.addEventListener('click', loadNotifList);
    setInterval(loadNotifCount, 10000);
});
</script>
</body>
</html>