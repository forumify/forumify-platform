<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250809135552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add indexes on blameable timestamps';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_FEF0481D8B8E8428 ON badge (created_at)');
        $this->addSql('CREATE INDEX IDX_FEF0481D43625D9F ON badge (updated_at)');
        $this->addSql('CREATE INDEX IDX_57FA09C98B8E8428 ON calendar_event (created_at)');
        $this->addSql('CREATE INDEX IDX_57FA09C943625D9F ON calendar_event (updated_at)');
        $this->addSql('CREATE INDEX IDX_9474526C8B8E8428 ON comment (created_at)');
        $this->addSql('CREATE INDEX IDX_9474526C43625D9F ON comment (updated_at)');
        $this->addSql('CREATE INDEX IDX_B6BD307F8B8E8428 ON message (created_at)');
        $this->addSql('CREATE INDEX IDX_B6BD307F43625D9F ON message (updated_at)');
        $this->addSql('CREATE INDEX IDX_607D18C8B8E8428 ON message_thread (created_at)');
        $this->addSql('CREATE INDEX IDX_607D18C43625D9F ON message_thread (updated_at)');
        $this->addSql('CREATE INDEX IDX_BF5476CA8B8E8428 ON notification (created_at)');
        $this->addSql('CREATE INDEX IDX_BF5476CA43625D9F ON notification (updated_at)');
        $this->addSql('CREATE INDEX IDX_140AB6208B8E8428 ON page (created_at)');
        $this->addSql('CREATE INDEX IDX_140AB62043625D9F ON page (updated_at)');
        $this->addSql('CREATE INDEX IDX_A4D707F78B8E8428 ON reaction (created_at)');
        $this->addSql('CREATE INDEX IDX_A4D707F743625D9F ON reaction (updated_at)');
        $this->addSql('CREATE INDEX IDX_BC91F4168B8E8428 ON resource (created_at)');
        $this->addSql('CREATE INDEX IDX_BC91F41643625D9F ON resource (updated_at)');
        $this->addSql('CREATE INDEX IDX_961C8CD58B8E8428 ON snippet (created_at)');
        $this->addSql('CREATE INDEX IDX_961C8CD543625D9F ON snippet (updated_at)');
        $this->addSql('CREATE INDEX IDX_9775E7088B8E8428 ON theme (created_at)');
        $this->addSql('CREATE INDEX IDX_9775E70843625D9F ON theme (updated_at)');
        $this->addSql('CREATE INDEX IDX_9D40DE1B8B8E8428 ON topic (created_at)');
        $this->addSql('CREATE INDEX IDX_9D40DE1B43625D9F ON topic (updated_at)');
        $this->addSql('CREATE INDEX IDX_7E0EA83D8B8E8428 ON topic_image (created_at)');
        $this->addSql('CREATE INDEX IDX_7E0EA83D43625D9F ON topic_image (updated_at)');
        $this->addSql('CREATE INDEX IDX_8D93D6498B8E8428 ON user (created_at)');
        $this->addSql('CREATE INDEX IDX_8D93D64943625D9F ON user (updated_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_FEF0481D8B8E8428 ON badge');
        $this->addSql('DROP INDEX IDX_FEF0481D43625D9F ON badge');
        $this->addSql('DROP INDEX IDX_57FA09C98B8E8428 ON calendar_event');
        $this->addSql('DROP INDEX IDX_57FA09C943625D9F ON calendar_event');
        $this->addSql('DROP INDEX IDX_9474526C8B8E8428 ON comment');
        $this->addSql('DROP INDEX IDX_9474526C43625D9F ON comment');
        $this->addSql('DROP INDEX IDX_B6BD307F8B8E8428 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F43625D9F ON message');
        $this->addSql('DROP INDEX IDX_607D18C8B8E8428 ON message_thread');
        $this->addSql('DROP INDEX IDX_607D18C43625D9F ON message_thread');
        $this->addSql('DROP INDEX IDX_BF5476CA8B8E8428 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA43625D9F ON notification');
        $this->addSql('DROP INDEX IDX_140AB6208B8E8428 ON page');
        $this->addSql('DROP INDEX IDX_140AB62043625D9F ON page');
        $this->addSql('DROP INDEX IDX_A4D707F78B8E8428 ON reaction');
        $this->addSql('DROP INDEX IDX_A4D707F743625D9F ON reaction');
        $this->addSql('DROP INDEX IDX_BC91F4168B8E8428 ON resource');
        $this->addSql('DROP INDEX IDX_BC91F41643625D9F ON resource');
        $this->addSql('DROP INDEX IDX_961C8CD58B8E8428 ON snippet');
        $this->addSql('DROP INDEX IDX_961C8CD543625D9F ON snippet');
        $this->addSql('DROP INDEX IDX_9775E7088B8E8428 ON theme');
        $this->addSql('DROP INDEX IDX_9775E70843625D9F ON theme');
        $this->addSql('DROP INDEX IDX_9D40DE1B8B8E8428 ON topic');
        $this->addSql('DROP INDEX IDX_9D40DE1B43625D9F ON topic');
        $this->addSql('DROP INDEX IDX_7E0EA83D8B8E8428 ON topic_image');
        $this->addSql('DROP INDEX IDX_7E0EA83D43625D9F ON topic_image');
        $this->addSql('DROP INDEX IDX_8D93D6498B8E8428 ON user');
        $this->addSql('DROP INDEX IDX_8D93D64943625D9F ON user');
    }
}
