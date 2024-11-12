<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241111193416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add answers to topics, and remove obsolete table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge_user DROP FOREIGN KEY FK_299D3A50A76ED395');
        $this->addSql('ALTER TABLE badge_user DROP FOREIGN KEY FK_299D3A50F7A2C2FC');
        $this->addSql('DROP TABLE badge_user');
        $this->addSql('ALTER TABLE topic ADD answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BAA334807 FOREIGN KEY (answer_id) REFERENCES comment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9D40DE1BAA334807 ON topic (answer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE badge_user (badge_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_299D3A50A76ED395 (user_id), INDEX IDX_299D3A50F7A2C2FC (badge_id), PRIMARY KEY(badge_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE badge_user ADD CONSTRAINT FK_299D3A50A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge_user ADD CONSTRAINT FK_299D3A50F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BAA334807');
        $this->addSql('DROP INDEX IDX_9D40DE1BAA334807 ON topic');
        $this->addSql('ALTER TABLE topic DROP answer_id');
    }
}
