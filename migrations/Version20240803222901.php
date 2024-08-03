<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240803222901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fixed forums not having a position';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            -- Update positions for grouped forums
            SET @group_position = 0;
            
            UPDATE forum AS f
            JOIN (
                SELECT id, group_id,
                       @group_position := IF(@prev_group_id = group_id, @group_position + 1, 1) AS new_position,
                       @prev_group_id := group_id AS prev_group_id
                FROM forum
                WHERE position = 0
                AND group_id IS NOT NULL
                ORDER BY group_id, id
            ) AS subquery
            ON f.id = subquery.id
            SET f.position = subquery.new_position;
        ");

        $this->addSql("
            -- Reset the group position variable
            SET @group_position = 0;
            
            UPDATE forum AS f
            JOIN (
                SELECT id, parent,
                       @parent_position := IF(@prev_parent = parent, @parent_position + 1, 1) AS new_position,
                       @prev_parent := parent AS prev_parent
                FROM forum
                WHERE position = 0
                AND group_id IS NULL
                ORDER BY parent, id
            ) AS subquery
            ON f.id = subquery.id
            SET f.position = subquery.new_position;
        ");
    }

    public function down(Schema $schema): void
    {

    }
}
