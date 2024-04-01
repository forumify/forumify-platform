<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240401133322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add configurable menu';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE menu_item (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, type VARCHAR(255) NOT NULL, payload JSON NOT NULL, INDEX IDX_D754D550727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO menu_item (parent_id, name, `position`, `type`, payload) VALUES (null, 'Forum', 1, 'route', '{\"route\": \"forumify_forum_forum\", \"parameters\": {}}'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu_item DROP FOREIGN KEY FK_D754D550727ACA70');
        $this->addSql('DROP TABLE menu_item');
    }
}
