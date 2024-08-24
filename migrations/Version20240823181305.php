<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240823181305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add position to roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD position INT NOT NULL');

        $roles = $this->connection->fetchAllAssociative('SELECT id, slug FROM role');

        $pos = 1;
        $updates = [];
        foreach ($roles as $role) {
            if ($role['slug'] === 'super-admin') {
                $updates[] = [0, $role['id']];
                continue;
            }

            if ($role['slug'] === 'user') {
                $updates[] = [9999, $role['id']];
                continue;
            }

            if ($role['slug'] === 'guest') {
                $updates[] = [10000, $role['id']];
                continue;
            }

            $updates[] = [$pos, $role['id']];
            $pos++;
        }

        foreach ($updates as $update) {
            $this->addSql('UPDATE role SET position = ? WHERE id = ?', $update);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role DROP position');
    }
}
