<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251130115354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add indexes to position fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_FEF0481D462CE4F5 ON badge (position)');
        $this->addSql('CREATE INDEX IDX_852BBECD462CE4F5 ON forum (position)');
        $this->addSql('CREATE INDEX IDX_75B4E212462CE4F5 ON forum_group (position)');
        $this->addSql('CREATE INDEX IDX_D754D550462CE4F5 ON menu_item (position)');
        $this->addSql('CREATE INDEX IDX_57698A6A462CE4F5 ON role (position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_FEF0481D462CE4F5 ON badge');
        $this->addSql('DROP INDEX IDX_852BBECD462CE4F5 ON forum');
        $this->addSql('DROP INDEX IDX_75B4E212462CE4F5 ON forum_group');
        $this->addSql('DROP INDEX IDX_D754D550462CE4F5 ON menu_item');
        $this->addSql('DROP INDEX IDX_57698A6A462CE4F5 ON role');
    }
}
