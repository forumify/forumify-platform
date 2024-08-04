<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240803124904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add oauth clients';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, client_id VARCHAR(32) NOT NULL, client_secret VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_AD73274D19EB6921 (client_id), INDEX IDX_AD73274DDE12AB56 (created_by), INDEX IDX_AD73274D16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_client_role (client INT NOT NULL, role INT NOT NULL, INDEX IDX_DAD4E5A3C7440455 (client), INDEX IDX_DAD4E5A357698A6A (role), PRIMARY KEY(client, role)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_client ADD CONSTRAINT FK_AD73274DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oauth_client ADD CONSTRAINT FK_AD73274D16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oauth_client_role ADD CONSTRAINT FK_DAD4E5A3C7440455 FOREIGN KEY (client) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_client_role ADD CONSTRAINT FK_DAD4E5A357698A6A FOREIGN KEY (role) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_client DROP FOREIGN KEY FK_AD73274DDE12AB56');
        $this->addSql('ALTER TABLE oauth_client DROP FOREIGN KEY FK_AD73274D16FE72E1');
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY FK_DAD4E5A3C7440455');
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY FK_DAD4E5A357698A6A');
        $this->addSql('DROP TABLE oauth_client');
        $this->addSql('DROP TABLE oauth_client_role');
    }
}
