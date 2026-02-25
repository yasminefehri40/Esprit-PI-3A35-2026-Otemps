<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add statut column to events table for weather-based cancellation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE events ADD statut VARCHAR(50) NOT NULL DEFAULT 'actif'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP COLUMN statut');
    }
}
