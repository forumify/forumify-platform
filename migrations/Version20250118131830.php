<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250118131830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add show on forum for roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD show_on_forum TINYINT(1) DEFAULT 0 NOT NULL, ADD color VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role DROP show_on_forum, DROP color');
    }
}
