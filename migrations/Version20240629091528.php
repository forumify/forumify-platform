<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240629091528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add plugin types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plugin ADD type VARCHAR(255) DEFAULT \'plugin\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plugin DROP type');
    }
}
