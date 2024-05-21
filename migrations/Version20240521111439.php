<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20240521111439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set display name on existing users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `user` SET display_name=username');
        $rows = $this->connection->fetchAllAssociative('SELECT id, username FROM `user`');

        $slugger = new AsciiSlugger();
        foreach ($rows as $row) {
            $this->addSql('UPDATE `user` SET username=:username WHERE id=:id', [
                'id' => $row['id'],
                'username' => $slugger->slug($row['username'], '_')->toString(),
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        throw new IrreversibleMigration();
    }
}
