<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240804125647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 're-order all forums';
    }

    public function up(Schema $schema): void
    {
        $forums = $this->connection
            ->executeQuery('SELECT id, parent, group_id FROM forum ORDER BY position ASC')
            ->fetchAllAssociative();

        $highestPosMap = [];
        foreach ($forums as $forum) {
            $posMapKey = ($forum['parent'] ?? '') . '-' . ($forum['group_id'] ?? '');
            $position = ($highestPosMap[$posMapKey] ?? 0) + 1;

            $this->addSql('UPDATE forum SET position = :position WHERE id = :id', ['position' => $position, 'id' => $forum['id']]);

            $highestPosMap[$posMapKey] = $position;
        }
    }

    public function down(Schema $schema): void
    {
    }
}
