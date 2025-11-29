<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251129111839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add precomputed last message date';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message_thread ADD last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('UPDATE message_thread mt SET last_message_at = (
            SELECT MAX(m.created_at)
            FROM message m
            WHERE m.thread_id = mt.id
        );');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message_thread DROP last_message_at');
    }
}
