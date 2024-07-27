<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240713162711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Nested Permissions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role ADD permissions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE role DROP permissions');
    }
}
