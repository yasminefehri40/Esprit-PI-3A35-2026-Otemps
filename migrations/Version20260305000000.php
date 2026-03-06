<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add utilisateurs table and migrate events/participations/reviews foreign keys to utilisateurs';
    }

    public function up(Schema $schema): void
    {
        // Create utilisateurs table (from OTemps-finaleuser)
        $this->addSql('CREATE TABLE IF NOT EXISTS utilisateurs (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, motdepasse VARCHAR(255) NOT NULL, dateinscription DATETIME NOT NULL, role VARCHAR(50) NOT NULL, face_image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create messenger_messages table if not exists
        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Insert default admin into utilisateurs (matching existing users data)
        $this->addSql("INSERT IGNORE INTO utilisateurs (nom, prenom, email, motdepasse, dateinscription, role) VALUES ('Admin', 'OTEMPS', 'admin@otemps.fr', 'changeme', NOW(), 'ROLE_ADMIN')");

        // Drop old FK constraints on events, participations, reviews that reference users
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY IF EXISTS FK_5387574A61220EA6');
        $this->addSql('ALTER TABLE participations DROP FOREIGN KEY IF EXISTS FK_FDC6C6E8A76ED395');

        // Add creator_utilisateur_id column to events
        $this->addSql('ALTER TABLE events ADD creator_utilisateur_id INT DEFAULT NULL');
        $this->addSql('UPDATE events SET creator_utilisateur_id = (SELECT id FROM utilisateurs WHERE role = \'ROLE_ADMIN\' LIMIT 1)');
        $this->addSql('ALTER TABLE events MODIFY creator_utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_events_utilisateurs FOREIGN KEY (creator_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE events DROP COLUMN creator_id');

        // Add user_utilisateur_id column to participations
        $this->addSql('ALTER TABLE participations ADD user_utilisateur_id INT DEFAULT NULL');
        $this->addSql('UPDATE participations SET user_utilisateur_id = (SELECT id FROM utilisateurs WHERE role = \'ROLE_ADMIN\' LIMIT 1)');
        $this->addSql('ALTER TABLE participations MODIFY user_utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE participations ADD CONSTRAINT FK_participations_utilisateurs FOREIGN KEY (user_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE participations DROP COLUMN user_id');

        // Add user_utilisateur_id column to reviews
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY IF EXISTS FK_6970EB0FA76ED395');
        $this->addSql('ALTER TABLE reviews ADD user_utilisateur_id INT DEFAULT NULL');
        $this->addSql('UPDATE reviews SET user_utilisateur_id = (SELECT id FROM utilisateurs WHERE role = \'ROLE_ADMIN\' LIMIT 1)');
        $this->addSql('ALTER TABLE reviews MODIFY user_utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_reviews_utilisateurs FOREIGN KEY (user_utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE reviews DROP COLUMN user_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_reviews_utilisateurs');
        $this->addSql('ALTER TABLE participations DROP FOREIGN KEY FK_participations_utilisateurs');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_events_utilisateurs');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
