<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240603185156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add type on forum';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum ADD type VARCHAR(255) NOT NULL DEFAULT "text"');
        $this->addSql('ALTER TABLE topic ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum DROP type');
        $this->addSql('ALTER TABLE topic DROP image');
    }
}
