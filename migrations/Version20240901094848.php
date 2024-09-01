<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240901094848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE oauth_authorization_code (code VARCHAR(255) NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, scope LONGTEXT NOT NULL, valid_until DATETIME NOT NULL, redirect_uri VARCHAR(255) NOT NULL, INDEX IDX_793B081719EB6921 (client_id), INDEX IDX_793B0817A76ED395 (user_id), PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, client_id VARCHAR(255) NOT NULL, client_secret VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_AD73274D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B081719EB6921 FOREIGN KEY (client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B0817A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B081719EB6921');
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B0817A76ED395');
        $this->addSql('DROP TABLE oauth_authorization_code');
        $this->addSql('DROP TABLE oauth_client');
    }
}
