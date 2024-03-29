<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240318200955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add user badges';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_FEF0481DDE12AB56 (created_by), INDEX IDX_FEF0481D16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_user (badge_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_299D3A50F7A2C2FC (badge_id), INDEX IDX_299D3A50A76ED395 (user_id), PRIMARY KEY(badge_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_badge (user INT NOT NULL, badge INT NOT NULL, INDEX IDX_1C32B3458D93D649 (user), INDEX IDX_1C32B345FEF0481D (badge), PRIMARY KEY(user, badge)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481DDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE badge ADD CONSTRAINT FK_FEF0481D16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE badge_user ADD CONSTRAINT FK_299D3A50F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge_user ADD CONSTRAINT FK_299D3A50A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B3458D93D649 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345FEF0481D FOREIGN KEY (badge) REFERENCES badge (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE badge DROP FOREIGN KEY FK_FEF0481DDE12AB56');
        $this->addSql('ALTER TABLE badge DROP FOREIGN KEY FK_FEF0481D16FE72E1');
        $this->addSql('ALTER TABLE badge_user DROP FOREIGN KEY FK_299D3A50F7A2C2FC');
        $this->addSql('ALTER TABLE badge_user DROP FOREIGN KEY FK_299D3A50A76ED395');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B3458D93D649');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345FEF0481D');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE badge_user');
        $this->addSql('DROP TABLE user_badge');
    }
}
