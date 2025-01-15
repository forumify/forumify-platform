<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115193958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change last login to last activity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE last_login last_activity DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE last_activity last_login DATE DEFAULT NULL');
    }
}
