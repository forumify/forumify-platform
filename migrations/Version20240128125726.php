<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240128125726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'de-tree forum entity';
    }

    public function up(Schema $schema): void
    {
        $forums = $this->connection->fetchAllAssociative('SELECT * FROM forum ORDER BY lft');
        $byParent = [];
        foreach ($forums as $forum) {
            $byParent[$forum['parent'] ?? 0][] = $forum;
        }

        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD16F4F95B');
        $this->addSql('DROP INDEX IDX_852BBECD16F4F95B ON forum');
        $this->addSql('ALTER TABLE forum ADD position INT NOT NULL, DROP root, DROP lft, DROP lvl, DROP rgt');

        foreach ($byParent as $forums) {
            $position = 0;
            foreach ($forums as $forum) {
                $this->addSql('UPDATE forum SET position = ? WHERE id = ?', [$position, $forum['id']]);
                $position++;
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('This migration cannot be reversed as data was removed during the UP migration.');
    }
}
