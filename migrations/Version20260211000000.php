<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users, events and participations tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE events (
            id INT AUTO_INCREMENT NOT NULL,
            creator_id INT NOT NULL,
            titre VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin DATETIME NOT NULL,
            lieu VARCHAR(255) NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_5387574A61220EA6 (creator_id),
            CONSTRAINT FK_5387574A61220EA6 FOREIGN KEY (creator_id) REFERENCES users (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE participations (
            id INT AUTO_INCREMENT NOT NULL,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            date_inscription DATETIME NOT NULL,
            statut VARCHAR(50) NOT NULL DEFAULT \'confirmée\',
            PRIMARY KEY(id),
            INDEX IDX_FDC6C6E871F7E88B (event_id),
            INDEX IDX_FDC6C6E8A76ED395 (user_id),
            CONSTRAINT FK_FDC6C6E871F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
            CONSTRAINT FK_FDC6C6E8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Insert default users
        $this->addSql("INSERT INTO users (name, email, is_admin) VALUES 
            ('Admin OTEMPS', 'admin@otemps.fr', 1),
            ('Alice Martin', 'alice.martin@example.com', 0),
            ('Bob Dupont', 'bob.dupont@example.com', 0),
            ('Claire Bernard', 'claire.bernard@example.com', 0),
            ('David Rousseau', 'david.rousseau@example.com', 0)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE participations');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE users');
    }
}
