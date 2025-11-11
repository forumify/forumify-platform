<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250727092823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add roles to oauth clients';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth_client_role (client INT NOT NULL, role INT NOT NULL, INDEX IDX_DAD4E5A3C7440455 (client), INDEX IDX_DAD4E5A357698A6A (role), PRIMARY KEY (client, role)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE oauth_client_role ADD CONSTRAINT FK_DAD4E5A3C7440455 FOREIGN KEY (client) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_client_role ADD CONSTRAINT FK_DAD4E5A357698A6A FOREIGN KEY (role) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_client ADD name VARCHAR(255) NOT NULL, ADD last_activity DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth_client CHANGE redirect_uris redirect_uris LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_client CHANGE redirect_uris redirect_uris LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY FK_DAD4E5A3C7440455');
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY FK_DAD4E5A357698A6A');
        $this->addSql('DROP TABLE oauth_client_role');
        $this->addSql('ALTER TABLE oauth_client DROP name, DROP last_activity');
    }
}
