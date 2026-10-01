# 🎓 Application web de gestion scolaire en ligne

Application web permettant à un établissement d'enseignement de gérer les étudiants, les matières, les notes, les emplois du temps, les annonces et les QCM, avec un **espace administrateur** et un **espace étudiant** distincts.

Projet personnel de développement full-stack, réalisé pour mettre en pratique **PHP, MySQL, Bootstrap et JavaScript** dans un contexte réel de gestion scolaire.

> Application pédagogique — Portfolio personnel
> Réalisé en autodidacte — année universitaire 2024-2025

---

## 🎯 Contexte et objectif

Aujourd'hui, la gestion scolaire dans de nombreux établissements se fait encore de manière manuelle ou avec des outils dispersés : les notes sont saisies sur papier, les emplois du temps affichés sur des tableaux, les annonces communiquées oralement et les évaluations corrigées à la main.

Cette dispersion entraîne :

- une perte de temps pour les enseignants et l'administration ;
- des erreurs de calcul de moyennes ;
- une difficulté pour les étudiants à consulter leurs informations ;
- un manque de traçabilité et de centralisation.

**Objectif :** concevoir et développer une application web unique qui centralise la gestion scolaire, offre aux administrateurs un outil complet et donne aux étudiants un accès autonome à leurs données — notes, emploi du temps, annonces et évaluations en ligne.

---

## ✨ Fonctionnalités

Les cas d'utilisation sont classés par ordre de priorité :

| Priorité | Fonctionnalité | Acteur(s) | Description |
|---|---|---|---|
| 1 | S'authentifier | Administrateur, étudiant | Connexion par e-mail et mot de passe ; le serveur détermine le rôle et redirige vers l'espace correspondant |
| 2 | S'inscrire | Étudiant | Inscription en ligne avec validation des données (matricule, téléphone, nom, niveau) ; le compte est activé par l'administrateur |
| 3 | Gérer les étudiants | Administrateur | Ajout, modification, suppression, filtrage par niveau et recherche par nom ou matricule |
| 4 | Gérer les matières | Administrateur | Création, modification et suppression des matières avec leur coefficient |
| 5 | Gérer les notes | Administrateur | Saisie des notes par étudiant et par matière, filtrage par niveau, matière, semestre et recherche |
| 6 | Gérer les emplois du temps | Administrateur | Planification des cours par niveau, jour, heure, salle et enseignant |
| 7 | Publier des annonces | Administrateur | Annonces ciblées par rôle (tous, étudiants, admin) et par niveau |
| 8 | Créer des QCM | Administrateur | Création de QCM par niveau et matière avec questions et propositions |
| 9 | Consulter ses notes | Étudiant | Consultation des notes, calcul de la moyenne pondérée et téléchargement du bulletin PDF |
| 10 | Consulter son emploi du temps | Étudiant | Visualisation hebdomadaire de l'emploi du temps selon le niveau |
| 11 | Consulter les annonces | Étudiant | Affichage des annonces ciblées selon le niveau |
| 12 | Passer un QCM | Étudiant | QCM interactif avec **chronomètre** et soumission automatique en fin de temps |
| 13 | Recevoir des notifications | Administrateur, étudiant | Notifications en temps réel sur les nouvelles inscriptions, notes, annonces et validations |
| 14 | Visualiser des statistiques | Administrateur, étudiant | Tableaux de bord avec graphiques (moyennes, évolution, répartition) |

> **Niveau :** classe de l'étudiant (L1, L2, L3, M1, M2), utilisée pour filtrer les contenus pertinents.

---

## 🏗️ Architecture

L'application suit une **architecture MVC simplifiée** avec un backend PHP qui communique avec une base MySQL, et une interface responsive en Bootstrap 5.

```text
┌─────────────────────────────┐
│  Couche présentation        │
│  Bootstrap 5 + JavaScript   │
│  (Chart.js, SweetAlert2)    │
└──────────────┬──────────────┘
               │  Requêtes HTTP / AJAX
               ▼
┌─────────────────────────────┐
│  Couche métier              │
│  PHP 8 (PDO, sessions)      │
└──────────────┬──────────────┘
               │  Requêtes préparées PDO
               ▼
┌─────────────────────────────┐
│  Couche données             │
│  MySQL / MariaDB            │
└─────────────────────────────┘
```

---

## 🛠️ Technologies utilisées

| Domaine | Choix | Alternative comparée |
|---|---|---|
| Backend | PHP 8 + PDO (requêtes préparées) | Node.js, Django |
| Base de données | MySQL / MariaDB | PostgreSQL |
| Frontend | Bootstrap 5 + JavaScript natif | React, Vue.js |
| Authentification | Sessions PHP + hachage `password_hash` (bcrypt) | JWT |
| Graphiques | Chart.js | ApexCharts |
| Alertes | SweetAlert2 | Toastr |
| Export PDF | Dompdf | TCPDF, FPDF |
| Icônes | Bootstrap Icons | Font Awesome |
| Serveur local | Laragon (Apache + MySQL + PHP) | XAMPP, WAMP |
| Éditeur | Visual Studio Code | PhpStorm |

Les dépendances PHP sont gérées avec **Composer** (voir `composer.json`).

---

## 📁 Structure du dépôt

Principaux éléments :

```text
Gestion_scolaire/
├── config/
│   ├── database.php           # connexion PDO à MySQL
│   └── database.example.php   # modèle de configuration
├── includes/
│   ├── header.php             # en-tête + topbar notifications
│   ├── sidebar.php            # menu latéral (admin / étudiant)
│   ├── footer.php             # scripts JS communs
│   └── notifier.php           # helper d'envoi de notifications
├── auth/
│   ├── login.php              # connexion (admin + étudiant)
│   ├── register.php           # inscription étudiant (validation admin)
│   └── logout.php             # déconnexion
├── admin/
│   ├── dashboard.php          # tableau de bord + graphiques
│   ├── etudiants.php          # gestion des étudiants
│   ├── matieres.php           # gestion des matières
│   ├── notes.php              # gestion des notes (filtres avancés)
│   ├── emplois.php            # emplois du temps par niveau
│   ├── annonces.php           # publication d'annonces ciblées
│   └── qcm.php                # création de QCM
├── etudiant/
│   ├── dashboard.php          # accueil avec stats personnelles
│   ├── notes.php              # mes notes + bulletin PDF
│   ├── edt.php                # mon emploi du temps
│   ├── annonces.php           # annonces de mon niveau
│   └── qcm.php                # QCM avec chronomètre
├── api/
│   └── notifications.php      # API JSON pour les notifications
├── export/
│   └── pdf_bulletin.php       # génération PDF des bulletins
├── scripts/
│   ├── init_admin.php         # création du compte admin
│   └── fix_passwords.php      # régénération des mots de passe (démo)
├── sql/
│   ├── gestion_scolaire.sql   # structure de la base
│   └── donnees_test.sql       # données de démonstration
├── vendor/                    # dépendances Composer (Dompdf)
├── index.php                  # point d'entrée (redirection par rôle)
└── README.md
```

Écrans principaux :

| Fichier | Écran | Rôle |
|---|---|---|
| `auth/login.php` | Connexion | Admin + Étudiant |
| `auth/register.php` | Inscription | Étudiant |
| `admin/dashboard.php` | Tableau de bord | Admin |
| `admin/etudiants.php` | Gestion étudiants | Admin |
| `admin/matieres.php` | Gestion matières | Admin |
| `admin/notes.php` | Gestion notes | Admin |
| `admin/emplois.php` | Emplois du temps | Admin |
| `admin/annonces.php` | Annonces | Admin |
| `admin/qcm.php` | QCM | Admin |
| `etudiant/dashboard.php` | Accueil étudiant | Étudiant |
| `etudiant/notes.php` | Mes notes + PDF | Étudiant |
| `etudiant/edt.php` | Mon emploi du temps | Étudiant |
| `etudiant/qcm.php` | Passer un QCM (chrono) | Étudiant |

---

## 🧠 Modèle de données

Les principales données gérées sont l'**utilisateur** (admin ou étudiant), les **matières**, les **notes**, les **emplois du temps**, les **annonces**, les **QCM** et les **notifications**.

Règles de gestion :

- **RG1 :** un étudiant possède un et un seul matricule unique ;
- **RG2 :** un étudiant appartient à un et un seul niveau (L1, L2, L3, M1, M2) ;
- **RG3 :** une note est associée à un seul étudiant et à une seule matière ;
- **RG4 :** un emploi du temps concerne un seul niveau, une matière, un jour et une plage horaire ;
- **RG5 :** une annonce est publiée par un seul auteur, et peut cibler un niveau précis ou tous les niveaux ;
- **RG6 :** un QCM contient une ou plusieurs questions, chaque question contenant plusieurs propositions dont une seule correcte ;
- **RG7 :** une notification appartient à un seul utilisateur ;
- **RG8 :** un étudiant ne peut passer qu'une seule fois le même QCM (résultat unique).

Tables principales de la base :

- `utilisateurs` — comptes admin et étudiants (nom, prénom, email, téléphone, matricule, niveau, statut, mot de passe haché) ;
- `matieres` — code, libellé, coefficient ;
- `notes` — étudiant, matière, valeur, semestre, date de saisie ;
- `emplois_du_temps` — niveau, matière, enseignant, jour, heure de début/fin, salle ;
- `annonces` — titre, contenu, auteur, cible, niveau, date de publication ;
- `qcm`, `questions`, `propositions` — structure des QCM ;
- `resultats_qcm` — note obtenue, total des points, temps utilisé ;
- `notifications` — utilisateur, titre, message, lien, icône, statut de lecture.

Les moyennes pondérées sont calculées **côté PHP** en tenant compte du coefficient de chaque matière.

---

## 🔒 Sécurité

- **Authentification par sessions PHP** : après connexion, un identifiant de session est stocké côté serveur.
- **Hachage des mots de passe** : `password_hash()` avec l'algorithme bcrypt ; les mots de passe ne sont **jamais** renvoyés par l'application.
- **Vérification du rôle côté serveur** : un étudiant qui tente d'accéder à une URL admin est redirigé.
- **Inscription contrôlée** : validation stricte du matricule (format `200H-TOL`), du téléphone (format `0340100101`) et de la force du mot de passe (majuscule, minuscule, chiffre, caractère spécial, 8 caractères minimum).
- **Validation admin obligatoire** : tout nouveau compte étudiant passe par le statut `en_attente` avant activation.
- **Requêtes préparées PDO** : toutes les requêtes SQL utilisent des paramètres nommés pour prévenir les injections.
- **Assainissement HTML** : tous les affichages utilisent `htmlspecialchars()` pour éviter les failles XSS.
- **Configuration hors du code** : identifiants MySQL dans `config/database.php` (à copier depuis `database.example.php`), non versionné.
- **Chronomètre QCM côté client** : soumission automatique à la fin du temps imparti, avec enregistrement du temps réellement utilisé.

---

## 🚀 Installation

### Prérequis

- **Laragon** (ou XAMPP/WAMP) avec **Apache**, **PHP 8+** et **MySQL/MariaDB**
- **Composer** (pour Dompdf)
- Un navigateur moderne (Chrome, Firefox, Edge)
- **phpMyAdmin** (fourni avec Laragon)

### Récupérer le projet

```bash
git clone https://github.com/Ranto-nyaina/Gestion_scolaire.git
cd Gestion_scolaire
```

### 1. Base de données

Ouvre **phpMyAdmin** (`http://localhost/phpmyadmin/`) puis :

1. Importe `sql/gestion_scolaire.sql` (structure complète) ;
2. Importe `sql/donnees_test.sql` (données de démonstration, optionnel).

La base s'appelle `gestion_scolaire`.

### 2. Backend PHP

```bash
cd C:\laragon\www\Gestion_scolaire
composer require dompdf/dompdf
```

Copie `config/database.example.php` en `config/database.php` et remplis :

| Variable | Rôle |
|---|---|
| `$host` | hôte MySQL (`localhost`) |
| `$dbname` | nom de la base (`gestion_scolaire`) |
| `$user` | utilisateur MySQL (`root`) |
| `$pass` | mot de passe MySQL (vide sous Laragon par défaut) |

### 3. Compte administrateur

Exécute une seule fois le script d'initialisation :

**Option A — Terminal Laragon :**

```bash
php scripts/init_admin.php
php scripts/fix_passwords.php
```

**Option B — Navigateur :**

```text
http://localhost/Gestion_scolaire/scripts/init_admin.php
http://localhost/Gestion_scolaire/scripts/fix_passwords.php
```

⚠️ **Supprime ensuite ces deux fichiers** du dossier `scripts/` pour éviter tout risque.

### 4. Lancer l'application

```text
http://localhost/Gestion_scolaire/
```

Le point d'entrée `index.php` redirige automatiquement vers l'espace selon le rôle.

---

## 🔑 Comptes de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | `admin@ecole.com` | `Admin123!` |
| Étudiant (L1) | `jean@ecole.com` | `Etudiant123!` |
| Étudiant (L2) | `marie@ecole.com` | `Etudiant123!` |
| Étudiant (M2) | `lova@ecole.com` | `Etudiant123!` |
| Étudiant en attente | `test@ecole.com` | `Etudiant123!` |

> Les comptes étudiants sont fournis par `sql/donnees_test.sql`. À changer avant tout usage réel.

---

## 👤 Espaces utilisateur

### Espace administrateur

L'administrateur accède à :

- **Tableau de bord** avec statistiques (nombre d'étudiants, moyenne générale, évolution des moyennes, répartition des notes, top 5 étudiants) ;
- **Gestion des étudiants** : validation des inscriptions, filtres par niveau, recherche par nom ou matricule ;
- **Gestion des matières** : ajout, modification, suppression avec compteurs d'utilisation ;
- **Gestion des notes** : saisie, filtrage par niveau / matière / semestre / recherche ;
- **Emplois du temps** : planification par niveau ;
- **Annonces** : publication ciblée par rôle et par niveau ;
- **QCM** : création et gestion par niveau et matière.

### Espace étudiant

L'étudiant accède à :

- **Accueil** avec sa moyenne, son niveau, ses cours du jour, ses dernières notes et les QCM à passer ;
- **Mes notes** avec calcul de moyenne pondérée et **téléchargement du bulletin PDF** ;
- **Mon emploi du temps** filtré selon son niveau ;
- **Mes annonces** filtrées selon son niveau ;
- **QCM** avec chronomètre et historique personnel.

---

## ⚠️ Limites du projet

- L'application est prévue pour un usage **en développement local** (Laragon) : aucun déploiement en production n'est documenté ;
- aucun test automatisé (unitaires ou d'intégration) n'est fourni ;
- le chronomètre QCM fonctionne côté client uniquement (pas de vérification serveur du temps écoulé) ;
- les mots de passe de démonstration sont identiques pour tous les étudiants (`Etudiant123!`) : à changer avant tout usage réel ;
- la limitation de débit sur les routes publiques n'est pas implémentée ;
- aucune vérification par e-mail n'est demandée à l'inscription (le matricule fait office de preuve d'identité) ;
- la photo de profil étudiant n'est pas gérée ;
- l'envoi d'e-mails transactionnels n'est pas intégré (pas de PHPMailer ni de SMTP) ;
- l'export PDF génère uniquement le bulletin individuel (pas d'export groupé par niveau ou par promotion).

---

## 🚀 Perspectives d'amélioration

- **Déployer en production** avec HTTPS, variables d'environnement et serveur dédié ;
- **ajouter des tests** unitaires (PHPUnit) et d'intégration ;
- **implémenter la vérification d'e-mail** à l'inscription ;
- **ajouter un module de messagerie** interne entre admin et étudiants ;
- **gérer l'assiduité** (présences / absences) ;
- **ajouter un calendrier visuel** pour les emplois du temps ;
- **système de badges** (meilleure note, assiduité, progression) ;
- **export PDF groupé** par niveau ou promotion ;
- **application mobile** (React Native / Flutter) connectée à la même API ;
- **API REST sécurisée** pour permettre l'intégration avec d'autres systèmes.

---

## 📚 Compétences mises en œuvre

- Analyse des besoins et conception d'une application web complète
- Développement backend en **PHP 8** avec architecture MVC simplifiée
- Modélisation et administration d'une base **MySQL** (PDO, requêtes préparées)
- Développement frontend responsive avec **Bootstrap 5** et **JavaScript**
- Sécurisation d'une application (sessions, hachage, validation, assainissement)
- Génération de documents PDF (**Dompdf**)
- Visualisation de données avec **Chart.js**
- Système de **notifications en temps réel** avec API JSON
- **Chronométrage** côté client avec soumission automatique

---

## 👨‍🎓 Contexte

- **Auteur :** FANOMEZANTSOA Rantoniaina Harlivah
- **Projet :** Application web de gestion scolaire
- **Type :** Projet personnel / Portfolio
- **Année :** 2024-2025
- **Technologies principales :** PHP, MySQL, Bootstrap 5, JavaScript
- **Dépôt GitHub :** https://github.com/Ranto-nyaina/Gestion_scolaire

---

## 📌 Conclusion

Ce projet propose une chaîne complète, de l'analyse des besoins à la réalisation d'une application web : **Inscription → Validation admin → Saisie des notes → Consultation autonome → QCM interactif**, pour faciliter la gestion scolaire des établissements d'enseignement.

Sa valeur repose sur une démarche de bout en bout, avec une transparence assumée sur ses limites : environnement de développement uniquement, chronomètre côté client, et absence de tests automatisés.

---

## 📄 Licence

Ce projet est distribué sous licence **MIT**.