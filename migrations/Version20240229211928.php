<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240229211928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add more moderation options';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE topic ADD first_comment_id INT DEFAULT NULL, ADD locked TINYINT(1) NOT NULL, ADD pinned TINYINT(1) NOT NULL, ADD hidden TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B69F11C14 FOREIGN KEY (first_comment_id) REFERENCES comment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9D40DE1B69F11C14 ON topic (first_comment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B69F11C14');
        $this->addSql('DROP INDEX IDX_9D40DE1B69F11C14 ON topic');
        $this->addSql('ALTER TABLE topic DROP first_comment_id, DROP locked, DROP pinned, DROP hidden');
    }
}
