# 🎓 Gestion Scolaire

Plateforme de gestion scolaire **PHP · MySQL · Bootstrap 5** avec espaces Admin & Étudiant.

## ✨ Fonctionnalités

- 🔐 Authentification (admin / étudiant) avec validation des inscriptions
- 👥 Gestion des étudiants, matières, notes
- 📅 Emplois du temps par niveau
- 📢 Annonces ciblées (niveau / rôle)
- ✍️ QCM interactifs avec chronomètre
- 📄 Export PDF des bulletins
- 📈 Graphiques (Chart.js)
- 🔔 Notifications en temps réel

## 🛠️ Technologies

- **Backend** : PHP 8+ (PDO, sessions)
- **Base de données** : MySQL / MariaDB
- **Frontend** : Bootstrap 5, Chart.js, SweetAlert2
- **PDF** : Dompdf

## 📦 Installation

```bash
# 1. Cloner
git clone https://github.com/TON_USERNAME/gestion-scolaire.git
cd gestion-scolaire

# 2. Installer les dépendances
composer install

# 3. Importer la base
# - Créer la BDD "gestion_scolaire" dans phpMyAdmin
# - Importer gestion_scolaire.sql
# - Importer donnees_test.sql (optionnel)

# 4. Configurer
# Copier config/database.example.php vers config/database.php
# Remplir les identifiants MySQL

# 5. Créer l'admin
# Aller sur http://localhost/gestion-scolaire/scripts/init_admin.php
# Puis SUPPRIMER ce fichier