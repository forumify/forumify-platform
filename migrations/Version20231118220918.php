<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231118220918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add seen to notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification ADD seen TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user ADD display_name VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP seen');
        $this->addSql('ALTER TABLE user DROP display_name');
    }
}
