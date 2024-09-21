<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240921113315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add timezone to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD timezone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP timezone');
    }
}
