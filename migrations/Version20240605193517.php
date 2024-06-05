<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240605193517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add topic view count';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE topic ADD views INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE topic DROP views');
    }
}
