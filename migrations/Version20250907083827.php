<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907083827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add identity providers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE identity_provider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(255) NOT NULL, type VARCHAR(64) NOT NULL, data JSON DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D12F2F55989D9B62 ON identity_provider (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_D12F2F55989D9B62 ON identity_provider');
        $this->addSql('DROP TABLE identity_provider');
    }
}
