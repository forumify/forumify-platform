<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230913175338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, administrator TINYINT(1) NOT NULL, moderator TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_57698A6A989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user INT NOT NULL, role INT NOT NULL, INDEX IDX_2DE8C6A38D93D649 (user), INDEX IDX_2DE8C6A357698A6A (role), PRIMARY KEY(user, role)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A38D93D649 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A357698A6A FOREIGN KEY (role) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A38D93D649');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A357698A6A');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL');
    }
}
