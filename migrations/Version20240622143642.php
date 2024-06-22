<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240622143642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add SEO fields to pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD seo_description LONGTEXT NOT NULL, ADD seo_keywords VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP seo_description, DROP seo_keywords');
    }
}
