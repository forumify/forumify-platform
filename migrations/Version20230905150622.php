<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230905150622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix forum delete cascades';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1F55203D');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD111D17F9');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B29CCBAD0');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B111D17F9');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1F55203D');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B29CCBAD0');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B111D17F9');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD111D17F9');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
