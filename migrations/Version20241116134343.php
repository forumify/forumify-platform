<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116134343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add marketplace subscription versions to plugins';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plugin ADD subscription_version VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plugin DROP subscription_version');
    }
}
