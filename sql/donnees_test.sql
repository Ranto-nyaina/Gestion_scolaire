USE gestion_scolaire;

INSERT INTO matieres (code, libelle, coefficient) VALUES
('PHP101', 'Programmation PHP', 3),
('BDD201', 'Bases de données', 2),
('WEB301', 'Développement Web', 3),
('RES401', 'Réseaux', 2),
('SEC501', 'Sécurité Informatique', 4);

-- Mot de passe : Etudiant123! (hash régénéré par fix_passwords.php)
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, matricule, niveau, statut) VALUES
('Rakoto',   'Jean',   'jean@ecole.com',   '0340100101', 'temp', 'etudiant', '200H-TOL', 'L1', 'valide'),
('Rabe',     'Marie',  'marie@ecole.com',  '0340100102', 'temp', 'etudiant', '201H-TOL', 'L2', 'valide'),
('Andria',   'Paul',   'paul@ecole.com',   '0340100103', 'temp', 'etudiant', '202H-TOL', 'L3', 'valide'),
('Rasoa',    'Nina',   'nina@ecole.com',   '0340100104', 'temp', 'etudiant', '203H-TOL', 'M1', 'valide'),
('Randria',  'Lova',   'lova@ecole.com',   '0340100105', 'temp', 'etudiant', '204H-TOL', 'M2', 'valide'),
('Test',     'Nouveau','test@ecole.com',   '0340100106', 'temp', 'etudiant', '205H-TOL', 'L1', 'en_attente');

INSERT INTO notes (etudiant_id, matiere_id, valeur, semestre) VALUES
(2, 1, 15.50, 1), (2, 2, 12.00, 1), (2, 3, 14.75, 1),
(3, 1, 10.00, 1), (3, 2, 8.50, 1),  (3, 3, 11.25, 1),
(4, 1, 17.00, 1), (4, 2, 16.50, 1), (4, 4, 15.00, 1),
(5, 1, 18.00, 1), (5, 5, 19.50, 1);

INSERT INTO emplois_du_temps (niveau, matiere_id, enseignant, jour, heure_debut, heure_fin, salle) VALUES
('L1', 1, 'Dr. Rakoto', 'Lundi',    '08:00', '10:00', 'A101'),
('L1', 2, 'Dr. Rabe',   'Mardi',    '10:00', '12:00', 'A102'),
('L1', 3, 'M. Andria',  'Mercredi', '14:00', '16:00', 'B201'),
('L2', 1, 'Dr. Rakoto', 'Lundi',    '10:00', '12:00', 'A101'),
('L2', 2, 'Dr. Rabe',   'Mardi',    '08:00', '10:00', 'A102'),
('L3', 3, 'M. Andria',  'Lundi',    '14:00', '16:00', 'B201'),
('L3', 4, 'Dr. Rasoa',  'Mercredi', '08:00', '10:00', 'C301'),
('M1', 5, 'Dr. Rakoto', 'Jeudi',    '08:00', '10:00', 'D401'),
('M2', 5, 'Dr. Rakoto', 'Vendredi', '10:00', '12:00', 'D401');

INSERT INTO annonces (titre, contenu, auteur_id, cible, niveau) VALUES
('Rentrée universitaire', 'La rentrée est prévue le 15 Octobre 2024.', 1, 'etudiants', NULL),
('Examens L1',            'Les examens du semestre 1 pour L1 débutent le 5 Décembre.', 1, 'etudiants', 'L1'),
('Soutenances M2',        'Les soutenances de mémoire M2 auront lieu en Juin.', 1, 'etudiants', 'M2');

INSERT INTO qcm (titre, matiere_id, niveau, duree_minutes) VALUES
('Introduction PHP', 1, 'L1', 20),
('Bases SQL',        2, 'L1', 30),
('POO en PHP',       1, 'L2', 25),
('Sécurité Web',     5, 'M1', 40);

INSERT INTO questions (qcm_id, texte_question, points) VALUES
(1, 'Que signifie PHP ?', 1),
(1, 'Quel symbole ouvre un bloc PHP ?', 1);

INSERT INTO propositions (question_id, texte_option, est_correcte) VALUES
(1, 'Personal Home Page', 1),
(1, 'Private Hosting Protocol', 0),
(1, 'Pre Hypertext Processor', 0),
(2, '<?php', 1),
(2, '<script>', 0),
(2, '{{', 0);

INSERT INTO questions (qcm_id, texte_question, points) VALUES
(2, 'Quelle commande affiche toutes les tables ?', 1),
(2, 'Quel mot-clé pour filtrer les résultats ?', 1);

INSERT INTO propositions (question_id, texte_option, est_correcte) VALUES
(3, 'SHOW TABLES', 1),
(3, 'DISPLAY TABLES', 0),
(3, 'LIST TABLES', 0),
(4, 'WHERE', 1),
(4, 'FILTER', 0),
(4, 'HAVING', 0);