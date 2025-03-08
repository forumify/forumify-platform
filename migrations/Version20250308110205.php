<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250308110205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add option to hide badges on the forum';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge ADD show_on_forum TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE badge SET show_on_forum = 1'); // backwards compatibility
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge DROP show_on_forum');
    }
}
