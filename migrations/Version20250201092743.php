<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250201092743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add page types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD type VARCHAR(255) DEFAULT \'twig\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP type');
    }
}
