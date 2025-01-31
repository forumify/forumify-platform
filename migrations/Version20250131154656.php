<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250131154656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add index on automation trigger';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX trigger_idx ON automation (`trigger`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX trigger_idx ON automation');
    }
}
