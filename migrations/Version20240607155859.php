<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240607155859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add forum display settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum ADD display_settings_show_topic_author TINYINT(1) DEFAULT 1 NOT NULL, ADD display_settings_show_topic_statistics TINYINT(1) DEFAULT 1 NOT NULL, ADD display_settings_show_topic_last_comment_by TINYINT(1) DEFAULT 1 NOT NULL, ADD display_settings_show_topic_preview TINYINT(1) DEFAULT 0 NOT NULL, ADD display_settings_show_last_comment_by TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum DROP display_settings_show_topic_author, DROP display_settings_show_topic_statistics, DROP display_settings_show_topic_last_comment_by, DROP display_settings_show_topic_preview, DROP display_settings_show_last_comment_by');
    }
}
