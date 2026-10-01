DROP DATABASE IF EXISTS gestion_scolaire;
CREATE DATABASE gestion_scolaire 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_scolaire;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20) NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','etudiant') NOT NULL DEFAULT 'etudiant',
    matricule VARCHAR(20) UNIQUE NULL,
    niveau ENUM('L1','L2','L3','M1','M2') NULL,
    statut ENUM('en_attente','valide','rejete') NOT NULL DEFAULT 'valide',
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    coefficient INT DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    matiere_id INT NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    semestre INT DEFAULT 1,
    date_saisie TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE emplois_du_temps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    niveau ENUM('L1','L2','L3','M1','M2') NOT NULL,
    matiere_id INT NOT NULL,
    enseignant VARCHAR(100) NULL,
    jour ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    salle VARCHAR(30) NOT NULL,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    contenu TEXT NOT NULL,
    auteur_id INT NOT NULL,
    cible ENUM('tous','etudiants','admin') DEFAULT 'tous',
    niveau ENUM('L1','L2','L3','M1','M2') NULL,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE qcm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    matiere_id INT NOT NULL,
    niveau ENUM('L1','L2','L3','M1','M2') NULL,
    duree_minutes INT DEFAULT 30,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qcm_id INT NOT NULL,
    texte_question TEXT NOT NULL,
    points INT DEFAULT 1,
    FOREIGN KEY (qcm_id) REFERENCES qcm(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE propositions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    texte_option VARCHAR(255) NOT NULL,
    est_correcte BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE resultats_qcm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    qcm_id INT NOT NULL,
    note_obtenue DECIMAL(4,2) NOT NULL,
    total_points INT NOT NULL,
    temps_utilise INT NULL,
    date_passage TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (qcm_id) REFERENCES qcm(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    lien VARCHAR(255) NULL,
    icone VARCHAR(30) DEFAULT 'bell',
    couleur VARCHAR(20) DEFAULT 'primary',
    lue BOOLEAN DEFAULT FALSE,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;