<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240714112732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'prevent existing forums from showing installer';
    }

    public function up(Schema $schema): void
    {
        $res = $this->connection->fetchOne('SELECT `value` FROM `setting` WHERE `key` = "forumify.title"');
        if (!empty($res)) {
            $this->addSql('INSERT INTO `setting` VALUES ("forumify.platform_installed", "true")');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `setting` WHERE `key` = "forumify.platform_installed"');
    }
}
