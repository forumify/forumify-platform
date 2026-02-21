<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213195617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add composite index to speed up notification queries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_BF5476CAE92F8F788CDE5729A4520A18 ON notification (recipient_id, type, seen)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_BF5476CAE92F8F788CDE5729A4520A18 ON notification');
    }
}
