<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250928121648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add a way to store what identity providers the user has signed up with';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE identity_provider_user (id INT AUTO_INCREMENT NOT NULL, external_identifier VARCHAR(255) NOT NULL, user_id INT NOT NULL, identity_provider_id INT NOT NULL, INDEX IDX_7326AC026DD00CB8 (external_identifier), INDEX IDX_7326AC02A76ED395 (user_id), INDEX IDX_7326AC02B5FB2C8E (identity_provider_id), UNIQUE INDEX user_idp_uniq (user_id, identity_provider_id), UNIQUE INDEX extid_idp_uniq (external_identifier, identity_provider_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE identity_provider_user ADD CONSTRAINT FK_7326AC02A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE identity_provider_user ADD CONSTRAINT FK_7326AC02B5FB2C8E FOREIGN KEY (identity_provider_id) REFERENCES identity_provider (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE identity_provider_user DROP FOREIGN KEY FK_7326AC02A76ED395');
        $this->addSql('ALTER TABLE identity_provider_user DROP FOREIGN KEY FK_7326AC02B5FB2C8E');
        $this->addSql('DROP TABLE identity_provider_user');
    }
}
