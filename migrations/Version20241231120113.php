<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241231120113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'make conditions nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE automation CHANGE trigger_arguments trigger_arguments JSON DEFAULT NULL, CHANGE `condition` `condition` VARCHAR(255) DEFAULT NULL, CHANGE condition_arguments condition_arguments JSON DEFAULT NULL, CHANGE action_arguments action_arguments JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE automation CHANGE trigger_arguments trigger_arguments JSON NOT NULL, CHANGE `condition` `condition` VARCHAR(255) NOT NULL, CHANGE condition_arguments condition_arguments JSON NOT NULL, CHANGE action_arguments action_arguments JSON NOT NULL');
    }
}
