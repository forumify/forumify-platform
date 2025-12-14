<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826184800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add users to oauth clients';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY `FK_DAD4E5A357698A6A`');
        $this->addSql('ALTER TABLE oauth_client_role DROP FOREIGN KEY `FK_DAD4E5A3C7440455`');
        $this->addSql('DROP TABLE oauth_client_role');
        $this->addSql('ALTER TABLE oauth_client DROP name, DROP last_activity');
        $this->addSql('ALTER TABLE user ADD o_auth_client_id INT DEFAULT NULL, CHANGE email email VARCHAR(128) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494DAE4A33 FOREIGN KEY (o_auth_client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6494DAE4A33 ON user (o_auth_client_id)');

        $clients = $this->connection->fetchAllAssociative('SELECT * FROM oauth_client');
        foreach ($clients as $client) {
            $this->addSql('INSERT INTO user (username, display_name, language, email_verified, banned, last_activity, o_auth_client_id) VALUES (?, ?, ?, ?, ?, ?, ?)', [
                $client['client_id'],
                $client['name'],
                'en',
                1,
                0,
                $client['last_activity'],
                $client['id'],
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
