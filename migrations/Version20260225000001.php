<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reviews table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reviews (
            id INT AUTO_INCREMENT NOT NULL,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            comment LONGTEXT NOT NULL,
            rating INT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_6970EB0F71F7E88B (event_id),
            INDEX IDX_6970EB0FA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_reviews_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_reviews_user FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_reviews_event');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_reviews_user');
        $this->addSql('DROP TABLE reviews');
    }
}
