<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230911123648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'enforce subscription uniqueness & add email verification';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE sub1 FROM subscription sub1 INNER JOIN subscription sub2 WHERE sub1.id < sub2.id AND sub1.`user` = sub2.`user` AND sub1.`type` = sub2.`type` AND sub1.subject_id = sub2.subject_id');
        $this->addSql('CREATE UNIQUE INDEX subscription_uniq ON subscription (user, type, subject_id)');
        $this->addSql('ALTER TABLE user ADD email_verified TINYINT(1) NOT NULL AFTER avatar');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX subscription_uniq ON subscription');
        $this->addSql('ALTER TABLE user DROP email_verified');
    }
}
