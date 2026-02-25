-- OTEMPS Database Schema
-- Execute this SQL in phpMyAdmin to create the database structure

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(`id`),
  UNIQUE INDEX `UNIQ_1483A5E9E7927C74` (`email`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Create events table
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `creator_id` INT NOT NULL,
  `titre` VARCHAR(255) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `date_debut` DATETIME NOT NULL,
  `date_fin` DATETIME NOT NULL,
  `lieu` VARCHAR(255) NOT NULL,
  `nb_places` INT NOT NULL DEFAULT 20,
  `statut` VARCHAR(50) NOT NULL DEFAULT 'actif',
  PRIMARY KEY(`id`),
  INDEX `IDX_5387574A61220EA6` (`creator_id`),
  CONSTRAINT `FK_5387574A61220EA6` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Create participations table
CREATE TABLE IF NOT EXISTS `participations` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `event_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `date_inscription` DATETIME NOT NULL,
  `statut` VARCHAR(50) NOT NULL DEFAULT 'confirmée',
  PRIMARY KEY(`id`),
  INDEX `IDX_FDC6C6E871F7E88B` (`event_id`),
  INDEX `IDX_FDC6C6E8A76ED395` (`user_id`),
  CONSTRAINT `FK_FDC6C6E871F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FDC6C6E8A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Create reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `event_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment` LONGTEXT NOT NULL,
  `rating` INT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `IDX_6970EB0F71F7E88B` (`event_id`),
  INDEX `IDX_6970EB0FA76ED395` (`user_id`),
  CONSTRAINT `FK_reviews_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Insert default users (1 admin + 4 regular users)
INSERT INTO `users` (`name`, `email`, `is_admin`) VALUES 
  ('Admin OTEMPS', 'admin@otemps.fr', 1),
  ('Alice Martin', 'alice.martin@example.com', 0),
  ('Bob Dupont', 'bob.dupont@example.com', 0),
  ('Claire Bernard', 'claire.bernard@example.com', 0),
  ('David Rousseau', 'david.rousseau@example.com', 0);

-- Insert sample events
-- event IDs: 1=Visite artisanale, 2=Atelier restauration, 3=Conférence musique, 4=Visite nocturne
INSERT INTO `events` (`creator_id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `nb_places`) VALUES
  (1, 'Visite guidée du patrimoine artisanal', 'Découvrez les secrets des artisans d''autrefois à travers une visite immersive de notre collection d''objets oubliés. Un voyage dans le temps pour comprendre les savoir-faire ancestraux.', '2026-03-15 14:00:00', '2026-03-15 17:00:00', 'Musée OTEMPS, Paris', 30),
  (1, 'Atelier de restauration d''objets anciens', 'Participez à un atelier pratique où vous apprendrez les techniques de base pour restaurer et préserver les objets patrimoniaux. Encadré par des experts en conservation.', '2026-03-20 10:00:00', '2026-03-20 16:00:00', 'Atelier OTEMPS, Lyon', 15),
  (1, 'Conférence : Musique traditionnelle et instruments oubliés', 'Une conférence fascinante sur l''histoire des instruments de musique traditionnels tombés dans l''oubli. Avec démonstrations en direct et écoutes d''enregistrements rares.', '2026-04-05 18:30:00', '2026-04-05 20:30:00', 'Auditorium OTEMPS, Marseille', 50),
  (1, 'Visite nocturne : Rituels et symboles culturels', 'Une expérience unique en soirée pour explorer les objets rituels et leur signification dans différentes cultures. Ambiance tamisée et récits captivants.', '2026-04-12 20:00:00', '2026-04-12 22:00:00', 'Musée OTEMPS, Paris', 20);

-- Insert sample participations
-- user IDs: 2=Alice, 3=Bob, 4=Claire, 5=David
INSERT INTO `participations` (`event_id`, `user_id`, `date_inscription`, `statut`) VALUES
  (1, 2, '2026-02-10 09:00:00', 'confirmée'),
  (1, 3, '2026-02-11 14:30:00', 'confirmée'),
  (1, 4, '2026-02-12 10:15:00', 'annulée'),
  (2, 2, '2026-02-13 11:00:00', 'confirmée'),
  (2, 5, '2026-02-14 16:00:00', 'confirmée'),
  (3, 3, '2026-02-15 08:45:00', 'confirmée'),
  (3, 4, '2026-02-16 13:20:00', 'confirmée'),
  (3, 5, '2026-02-17 09:30:00', 'confirmée'),
  (4, 2, '2026-02-18 17:00:00', 'confirmée'),
  (4, 3, '2026-02-19 12:00:00', 'annulée');

-- Insert sample reviews
-- Rules enforced: only confirmed participants, one review per user per event
-- Event 1: Alice ✓ confirmed → reviewed | Bob ✓ confirmed → reviewed | Claire ✗ annulée → cannot review
-- Event 2: Alice ✓ confirmed → reviewed | David ✓ confirmed → reviewed
-- Event 3: Bob ✓ confirmed → reviewed | Claire ✓ confirmed → reviewed | David ✓ confirmed → NOT yet reviewed (form stays visible for David)
-- Event 4: Alice ✓ confirmed → reviewed | Bob ✗ annulée → cannot review
INSERT INTO `reviews` (`event_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES
  (1, 2, 'Une visite absolument fascinante ! Les guides étaient passionnés et les objets exposés d''une richesse incroyable. Je recommande vivement.', 5, '2026-02-15 10:00:00'),
  (1, 3, 'Très belle découverte du patrimoine artisanal. Quelques passages un peu rapides mais l''ensemble était de grande qualité.', 4, '2026-02-16 14:00:00'),
  (2, 2, 'Atelier très bien organisé. J''ai appris des techniques de restauration que je ne connaissais pas du tout. Super expérience !', 5, '2026-02-20 09:00:00'),
  (2, 5, 'Bonne initiation à la restauration d''objets anciens. Le formateur était compétent mais le temps était un peu court.', 3, '2026-02-21 11:30:00'),
  (3, 3, 'Conférence captivante sur des instruments que je n''avais jamais entendus. Les démonstrations en direct étaient le point fort.', 5, '2026-02-22 16:00:00'),
  (3, 4, 'Très intéressant et bien documenté. J''aurais aimé plus de temps pour les questions-réponses avec le conférencier.', 4, '2026-02-23 10:00:00'),
  (4, 2, 'Visite nocturne magique ! L''ambiance tamisée et les récits étaient parfaits. Une expérience unique à ne pas manquer.', 5, '2026-02-25 08:00:00');

-- Doctrine migration versions table
-- Tells Symfony which migrations have already been applied so they won't run again after importing this file
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` VARCHAR(191) NOT NULL,
  `executed_at` DATETIME DEFAULT NULL,
  `execution_time` INT DEFAULT NULL,
  PRIMARY KEY(`version`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
  ('DoctrineMigrations\\Version20260211000000', '2026-02-11 00:00:00', 50),
  ('DoctrineMigrations\\Version20260225000000', '2026-02-25 00:00:00', 30),
  ('DoctrineMigrations\\Version20260225000001', '2026-02-25 00:00:01', 40);
