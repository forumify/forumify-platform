<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240330203752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add CMS resources';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_BC91F416989D9B62 (slug), INDEX IDX_BC91F416DE12AB56 (created_by), INDEX IDX_BC91F41616FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F41616FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416DE12AB56');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41616FE72E1');
        $this->addSql('DROP TABLE resource');
    }
}
