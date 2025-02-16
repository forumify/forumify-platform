<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250216123105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'cascade forum group deletion when parent is deleted';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_group DROP FOREIGN KEY FK_75B4E212B6011601');
        $this->addSql('ALTER TABLE forum_group ADD CONSTRAINT FK_75B4E212B6011601 FOREIGN KEY (parent_forum_id) REFERENCES forum (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum_group DROP FOREIGN KEY FK_75B4E212B6011601');
        $this->addSql('ALTER TABLE forum_group ADD CONSTRAINT FK_75B4E212B6011601 FOREIGN KEY (parent_forum_id) REFERENCES forum (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
