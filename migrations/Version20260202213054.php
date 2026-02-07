<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260202213054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add audit logs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_log (uid BINARY(16) NOT NULL, action VARCHAR(255) NOT NULL, target_entity_class VARCHAR(255) NOT NULL, target_entity_id VARCHAR(255) NOT NULL, target_name VARCHAR(255) NOT NULL, changeset JSON DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_F6E1C0F547CC8C92 (action), INDEX IDX_F6E1C0F5A76ED395 (user_id), INDEX IDX_F6E1C0F5A1EDFF37B5910F71 (target_entity_class, target_entity_id), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE audit_log CHANGE target_entity_class target_entity_class VARCHAR(255) DEFAULT NULL, CHANGE target_entity_id target_entity_id VARCHAR(255) DEFAULT NULL, CHANGE target_name target_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5A76ED395');
        $this->addSql('DROP TABLE audit_log');
    }
}
