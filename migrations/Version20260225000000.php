<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nb_places column to events table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD nb_places INT NOT NULL DEFAULT 20');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP COLUMN nb_places');
    }
}
