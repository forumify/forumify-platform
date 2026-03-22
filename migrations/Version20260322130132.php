<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260322130132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add reputation to reactions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reaction ADD reputation INT NOT NULL');
        $this->addSql('UPDATE reaction SET reputation = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reaction DROP reputation');
    }
}
