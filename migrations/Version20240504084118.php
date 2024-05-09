<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240504084118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add plugins';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE plugin (id INT AUTO_INCREMENT NOT NULL, package VARCHAR(255) NOT NULL, plugin_class VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, latest_version VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_E96E2794DE686795 (package), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE plugin');
    }
}
