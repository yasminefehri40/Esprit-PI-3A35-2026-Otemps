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

-- Insert default users (1 admin + 4 regular users)
INSERT INTO `users` (`name`, `email`, `is_admin`) VALUES 
  ('Admin OTEMPS', 'admin@otemps.fr', 1),
  ('Alice Martin', 'alice.martin@example.com', 0),
  ('Bob Dupont', 'bob.dupont@example.com', 0),
  ('Claire Bernard', 'claire.bernard@example.com', 0),
  ('David Rousseau', 'david.rousseau@example.com', 0);

-- Insert sample events
INSERT INTO `events` (`creator_id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`) VALUES
  (1, 'Visite guidée du patrimoine artisanal', 'Découvrez les secrets des artisans d''autrefois à travers une visite immersive de notre collection d''objets oubliés. Un voyage dans le temps pour comprendre les savoir-faire ancestraux.', '2026-03-15 14:00:00', '2026-03-15 17:00:00', 'Musée OTEMPS, Paris'),
  (1, 'Atelier de restauration d''objets anciens', 'Participez à un atelier pratique où vous apprendrez les techniques de base pour restaurer et préserver les objets patrimoniaux. Encadré par des experts en conservation.', '2026-03-20 10:00:00', '2026-03-20 16:00:00', 'Atelier OTEMPS, Lyon'),
  (1, 'Conférence : Musique traditionnelle et instruments oubliés', 'Une conférence fascinante sur l''histoire des instruments de musique traditionnels tombés dans l''oubli. Avec démonstrations en direct et écoutes d''enregistrements rares.', '2026-04-05 18:30:00', '2026-04-05 20:30:00', 'Auditorium OTEMPS, Marseille'),
  (1, 'Visite nocturne : Rituels et symboles culturels', 'Une expérience unique en soirée pour explorer les objets rituels et leur signification dans différentes cultures. Ambiance tamisée et récits captivants.', '2026-04-12 20:00:00', '2026-04-12 22:00:00', 'Musée OTEMPS, Paris');
