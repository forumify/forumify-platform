<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230930132349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'include permission in ACL uniqueness';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX entity_uniq ON acl');
        $this->addSql('CREATE UNIQUE INDEX entity_uniq ON acl (entity, entity_id, permission)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX entity_uniq ON acl');
        $this->addSql('CREATE UNIQUE INDEX entity_uniq ON acl (entity, entity_id)');
    }
}
