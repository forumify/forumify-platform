<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Subscribe all participants of existing message threads to their threads.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT IGNORE INTO subscription (`user`, type, subject_id)
            SELECT mtu.user_id, 'message_reply', mtu.message_thread_id
            FROM message_thread_user mtu
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM subscription WHERE type = 'message_reply'");
    }
}
