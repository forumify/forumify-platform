<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241222124122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add simple calendar';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE calendar (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6EA9A146989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE calendar_event (id INT AUTO_INCREMENT NOT NULL, calendar_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, start DATETIME NOT NULL, `repeat` VARCHAR(255) DEFAULT NULL, repeat_end DATETIME DEFAULT NULL, content LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_57FA09C9989D9B62 (slug), INDEX IDX_57FA09C9A40A2C8 (calendar_id), INDEX IDX_57FA09C9DE12AB56 (created_by), INDEX IDX_57FA09C916FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_57FA09C9A40A2C8 FOREIGN KEY (calendar_id) REFERENCES calendar (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_57FA09C9DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE calendar_event ADD CONSTRAINT FK_57FA09C916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_57FA09C9A40A2C8');
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_57FA09C9DE12AB56');
        $this->addSql('ALTER TABLE calendar_event DROP FOREIGN KEY FK_57FA09C916FE72E1');
        $this->addSql('DROP TABLE calendar');
        $this->addSql('DROP TABLE calendar_event');
    }
}
