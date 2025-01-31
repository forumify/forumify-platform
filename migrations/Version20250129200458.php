<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129200458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change last activity to datetime';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE last_activity last_activity DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE last_activity last_activity DATE DEFAULT NULL');
    }
}
