<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240921181301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add positions to badges';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge ADD position INT NOT NULL');
        $badges = $this->connection->fetchAllAssociative('SELECT * FROM badge');
        $i = 0;
        foreach ($badges as $badge) {
            $this->addSql('UPDATE `badge` SET position=? WHERE id=?', [++$i, $badge['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge DROP position');
    }
}
