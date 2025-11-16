<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250817194830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add end date and banners to calendar events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar_event ADD end DATETIME DEFAULT NULL, ADD banner VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar_event DROP end, DROP banner');
    }
}
