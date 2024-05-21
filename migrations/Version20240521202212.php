<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240521202212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add user media';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE media (path VARCHAR(255) NOT NULL, created_by INT DEFAULT NULL, INDEX IDX_6A2CA10CDE12AB56 (created_by), PRIMARY KEY(path)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CDE12AB56');
        $this->addSql('DROP TABLE media');
    }
}
