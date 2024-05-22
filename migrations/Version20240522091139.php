<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240522091139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add signature to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD signature LONGTEXT DEFAULT NULL AFTER avatar');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP signature');
    }
}
