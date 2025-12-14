<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251031194455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove moderator flag from roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role DROP moderator');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD moderator TINYINT(1) NOT NULL');
    }
}
