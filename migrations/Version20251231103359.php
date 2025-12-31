<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use function Symfony\Component\String\u;

final class Version20251231103359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'restore deleted oauth clients';
    }

    public function up(Schema $schema): void
    {
        $clients = $this->connection->fetchAllAssociative('SELECT id, client_id FROM oauth_client WHERE redirect_uris IS NULL');
        foreach ($clients as $client) {
            $existing = $this->connection->fetchFirstColumn('SELECT id FROM user WHERE o_auth_client_id = ?', [$client['id']]);
            if (!empty($existing)) {
                continue;
            }

            $this->addSql('INSERT INTO user (username, language, email_verified, banned, created_at, updated_at, display_name, o_auth_client_id) VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?)', [
                $client['client_id'],
                'en',
                1,
                0,
                u($client['client_id'])->replace('-', ' ')->title(true)->toString(),
                $client['id'],
            ]);
        }
    }

    public function down(Schema $schema): void
    {
    }
}
