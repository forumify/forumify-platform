<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241112174555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove last comment from topics and forums';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD111D17F9');
        $this->addSql('DROP INDEX IDX_852BBECD111D17F9 ON forum');
        $this->addSql('ALTER TABLE forum DROP last_comment_id');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B111D17F9');
        $this->addSql('DROP INDEX IDX_9D40DE1B111D17F9 ON topic');
        $this->addSql('ALTER TABLE topic DROP last_comment_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum ADD last_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_852BBECD111D17F9 ON forum (last_comment_id)');
        $this->addSql('ALTER TABLE topic ADD last_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9D40DE1B111D17F9 ON topic (last_comment_id)');
    }
}
