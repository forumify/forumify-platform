<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230917123509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add immutable system roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD description LONGTEXT NOT NULL, ADD `system` TINYINT(1) NOT NULL');

        $this->addSql("INSERT INTO role (slug, title, description, administrator, moderator, `system`) VALUES ('super-admin', 'Super Admin', 'This special role grants access to everything.', 1, 1, 1)");
        $this->addSql("INSERT INTO role (slug, title, description, administrator, moderator, `system`) VALUES ('user', 'User', 'This role is automatically given to all logged in users.', 0, 0, 1)");
        $this->addSql("INSERT INTO role (slug, title, description, administrator, moderator, `system`) VALUES ('guest', 'Guest', 'This role is automatically given to all visitors that are not logged in.', 0, 0, 1)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD slug VARCHAR(255) NOT NULL, DROP description, DROP `system`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A989D9B62 ON role (slug)');
    }
}

