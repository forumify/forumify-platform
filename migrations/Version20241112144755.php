<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241112144755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add display setting to only show own topics';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum ADD display_settings_only_show_own_topics TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum DROP display_settings_only_show_own_topics');
    }
}
