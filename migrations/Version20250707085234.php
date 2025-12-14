<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707085234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'update orm/dbal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar CHANGE color color CHAR(7) NOT NULL');
        $this->addSql('ALTER TABLE oauth_client CHANGE redirect_uris redirect_uris LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE permissions permissions LONGTEXT DEFAULT NULL, CHANGE color color CHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE language language CHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar CHANGE color color VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE oauth_client CHANGE redirect_uris redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE role CHANGE permissions permissions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE color color VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE language language VARCHAR(2) NOT NULL');
    }
}
