-- ============================================================
-- Insertion des utilisateurs de test — OTEMPS
-- Mot de passe pour tous : 123123
-- Hash bcrypt généré via : php bin/console security:hash-password 123123
-- ============================================================

INSERT INTO `utilisateurs`
    (`nom`, `prenom`, `email`, `motdepasse`, `dateinscription`, `role`, `face_image`)
VALUES
    -- Administrateur
    (
        'Admin',
        'OTEMPS',
        'admin@otemps.fr',
        '$2y$13$YJuTziGSeQVSLQyIQJ7YWO7/A0cHxAi1NiujEtBurWm8yY0yV74c2',
        NOW(),
        'ROLE_ADMIN',
        NULL
    ),

    -- Utilisateur 1
    (
        'Dupont',
        'Jean',
        'jean.dupont@example.com',
        '$2y$13$YJuTziGSeQVSLQyIQJ7YWO7/A0cHxAi1NiujEtBurWm8yY0yV74c2',
        NOW(),
        'ROLE_USER',
        NULL
    ),

    -- Utilisateur 2
    (
        'Benali',
        'Sara',
        'sara.benali@example.com',
        '$2y$13$YJuTziGSeQVSLQyIQJ7YWO7/A0cHxAi1NiujEtBurWm8yY0yV74c2',
        NOW(),
        'ROLE_USER',
        NULL
    ),

    -- Utilisateur 3
    (
        'Martin',
        'Alice',
        'alice.martin@example.com',
        '$2y$13$YJuTziGSeQVSLQyIQJ7YWO7/A0cHxAi1NiujEtBurWm8yY0yV74c2',
        NOW(),
        'ROLE_USER',
        NULL
    ),

    -- Utilisateur 4
    (
        'Trabelsi',
        'Mohamed',
        'mohamed.trabelsi@example.com',
        '$2y$13$YJuTziGSeQVSLQyIQJ7YWO7/A0cHxAi1NiujEtBurWm8yY0yV74c2',
        NOW(),
        'ROLE_USER',
        NULL
    );
