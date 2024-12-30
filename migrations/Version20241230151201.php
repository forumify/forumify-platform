<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241230151201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add automations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE automation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, `trigger` VARCHAR(255) NOT NULL, trigger_arguments JSON NOT NULL, `condition` VARCHAR(255) NOT NULL, condition_arguments JSON NOT NULL, action VARCHAR(255) NOT NULL, action_arguments JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE automation');
    }
}
