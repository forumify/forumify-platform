<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230923204214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add notification settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_notification_settings (user_id INT NOT NULL, auto_subscribe_to_topics TINYINT(1) NOT NULL, auto_subscribe_to_own_topics TINYINT(1) NOT NULL, email_on_message TINYINT(1) NOT NULL, email_on_notification TINYINT(1) NOT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_notification_settings ADD CONSTRAINT FK_7051D51EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_notification_settings DROP FOREIGN KEY FK_7051D51EA76ED395');
        $this->addSql('DROP TABLE user_notification_settings');
    }
}
