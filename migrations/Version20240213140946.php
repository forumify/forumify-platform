<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240213140946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Makes settings value nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE setting CHANGE value value JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE setting CHANGE value value JSON NOT NULL');
    }
}
