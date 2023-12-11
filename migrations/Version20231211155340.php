<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231211155340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reactions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE comment_reaction (id INT AUTO_INCREMENT NOT NULL, comment_id INT DEFAULT NULL, user_id INT DEFAULT NULL, reaction_id INT DEFAULT NULL, INDEX IDX_B99364F1F8697D13 (comment_id), INDEX IDX_B99364F1A76ED395 (user_id), INDEX IDX_B99364F1813C7171 (reaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reaction (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_A4D707F7DE12AB56 (created_by), INDEX IDX_A4D707F716FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment_reaction ADD CONSTRAINT FK_B99364F1F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_reaction ADD CONSTRAINT FK_B99364F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment_reaction ADD CONSTRAINT FK_B99364F1813C7171 FOREIGN KEY (reaction_id) REFERENCES reaction (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F716FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F1F8697D13');
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F1A76ED395');
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F1813C7171');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7DE12AB56');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F716FE72E1');
        $this->addSql('DROP TABLE comment_reaction');
        $this->addSql('DROP TABLE reaction');
    }
}
