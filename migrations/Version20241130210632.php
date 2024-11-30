<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241130210632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add topic templates to forums';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum ADD topic_template LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum DROP topic_template');
    }
}
