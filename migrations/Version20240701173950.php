<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240701173950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add themes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, plugin_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, theme_config JSON NOT NULL, css LONGTEXT NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_9775E708EC942BCF (plugin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugin (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708EC942BCF');
        $this->addSql('DROP TABLE theme');
    }
}
