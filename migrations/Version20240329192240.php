<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240329192240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add css and javascript to pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD css LONGTEXT NOT NULL, ADD javascript LONGTEXT NOT NULL, DROP type, CHANGE source twig LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD source LONGTEXT NOT NULL, ADD type VARCHAR(255) NOT NULL, DROP twig, DROP css, DROP javascript');
    }
}
