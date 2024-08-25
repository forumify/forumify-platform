<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240825094237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add read markers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE read_marker (subject VARCHAR(255) NOT NULL, subject_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_32EB64FA76ED395 (user_id), PRIMARY KEY(user_id, subject, subject_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE read_marker ADD CONSTRAINT FK_32EB64FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE read_marker DROP FOREIGN KEY FK_32EB64FA76ED395');
        $this->addSql('DROP TABLE read_marker');
    }
}
