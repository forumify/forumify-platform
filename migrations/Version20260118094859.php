<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260118094859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add forum tags';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE forum_tag (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, color CHAR(7) NOT NULL, allow_in_subforums TINYINT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, slug VARCHAR(255) NOT NULL, forum_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, INDEX IDX_EEA7C17E8B8E8428 (created_at), INDEX IDX_EEA7C17E43625D9F (updated_at), UNIQUE INDEX UNIQ_EEA7C17E989D9B62 (slug), INDEX IDX_EEA7C17E29CCBAD0 (forum_id), INDEX IDX_EEA7C17EDE12AB56 (created_by), INDEX IDX_EEA7C17E16FE72E1 (updated_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE forum_tag ADD `default` TINYINT DEFAULT 0 NOT NULL AFTER allow_in_subforums, CHANGE allow_in_subforums allow_in_subforums TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE TABLE topic_tag (topic_id INT NOT NULL, forum_tag_id INT NOT NULL, INDEX IDX_302AC6211F55203D (topic_id), INDEX IDX_302AC621A27D50C0 (forum_tag_id), PRIMARY KEY (topic_id, forum_tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE forum_tag ADD CONSTRAINT FK_EEA7C17E29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum_tag ADD CONSTRAINT FK_EEA7C17EDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE forum_tag ADD CONSTRAINT FK_EEA7C17E16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE topic_tag ADD CONSTRAINT FK_302AC6211F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic_tag ADD CONSTRAINT FK_302AC621A27D50C0 FOREIGN KEY (forum_tag_id) REFERENCES forum_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_tag DROP FOREIGN KEY FK_EEA7C17E29CCBAD0');
        $this->addSql('ALTER TABLE forum_tag DROP FOREIGN KEY FK_EEA7C17EDE12AB56');
        $this->addSql('ALTER TABLE forum_tag DROP FOREIGN KEY FK_EEA7C17E16FE72E1');
        $this->addSql('ALTER TABLE topic_tag DROP FOREIGN KEY FK_302AC6211F55203D');
        $this->addSql('ALTER TABLE topic_tag DROP FOREIGN KEY FK_302AC621A27D50C0');
        $this->addSql('DROP TABLE forum_tag');
        $this->addSql('DROP TABLE topic_tag');
    }
}
