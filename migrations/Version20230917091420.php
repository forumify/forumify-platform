<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230917091420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add ACL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE acl (id INT AUTO_INCREMENT NOT NULL, entity VARCHAR(255) NOT NULL, entity_id VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, UNIQUE INDEX entity_uniq (entity, entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acl_role (acl INT NOT NULL, role INT NOT NULL, INDEX IDX_7065EB79BC806D12 (acl), INDEX IDX_7065EB7957698A6A (role), PRIMARY KEY(acl, role)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acl_role ADD CONSTRAINT FK_7065EB79BC806D12 FOREIGN KEY (acl) REFERENCES acl (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acl_role ADD CONSTRAINT FK_7065EB7957698A6A FOREIGN KEY (role) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE acl_role DROP FOREIGN KEY FK_7065EB79BC806D12');
        $this->addSql('ALTER TABLE acl_role DROP FOREIGN KEY FK_7065EB7957698A6A');
        $this->addSql('DROP TABLE acl');
        $this->addSql('DROP TABLE acl_role');
    }
}
