<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230904184048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, topic_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_9474526C1F55203D (topic_id), INDEX IDX_9474526CDE12AB56 (created_by), INDEX IDX_9474526C16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forum (id INT AUTO_INCREMENT NOT NULL, root INT DEFAULT NULL, parent INT DEFAULT NULL, group_id INT DEFAULT NULL, last_comment_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, UNIQUE INDEX UNIQ_852BBECD989D9B62 (slug), INDEX IDX_852BBECD16F4F95B (root), INDEX IDX_852BBECD3D8E604F (parent), INDEX IDX_852BBECDFE54D947 (group_id), INDEX IDX_852BBECD111D17F9 (last_comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forum_group (id INT AUTO_INCREMENT NOT NULL, parent_forum_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_75B4E212B6011601 (parent_forum_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, thread_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B6BD307FE2904019 (thread_id), INDEX IDX_B6BD307FDE12AB56 (created_by), INDEX IDX_B6BD307F16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_thread (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_607D18CDE12AB56 (created_by), INDEX IDX_607D18C16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_thread_user (message_thread_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_695D943E8829462F (message_thread_id), INDEX IDX_695D943EA76ED395 (user_id), PRIMARY KEY(message_thread_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, recipient_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, context JSON NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_BF5476CAE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, url_key VARCHAR(255) NOT NULL, source LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_140AB620DFAB7B3B (url_key), UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), INDEX IDX_140AB620DE12AB56 (created_by), INDEX IDX_140AB62016FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (`key` VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(`key`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, type VARCHAR(255) NOT NULL, subject_id INT NOT NULL, INDEX IDX_A3C664D38D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, forum_id INT DEFAULT NULL, last_comment_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9D40DE1B989D9B62 (slug), INDEX IDX_9D40DE1B29CCBAD0 (forum_id), INDEX IDX_9D40DE1B111D17F9 (last_comment_id), INDEX IDX_9D40DE1BDE12AB56 (created_by), INDEX IDX_9D40DE1B16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, username VARCHAR(32) NOT NULL, email VARCHAR(128) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, language VARCHAR(2) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, banned TINYINT(1) NOT NULL, last_login DATE DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649DE12AB56 (created_by), INDEX IDX_8D93D64916FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD16F4F95B FOREIGN KEY (root) REFERENCES forum (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD3D8E604F FOREIGN KEY (parent) REFERENCES forum (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECDFE54D947 FOREIGN KEY (group_id) REFERENCES forum_group (id)');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECD111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE forum_group ADD CONSTRAINT FK_75B4E212B6011601 FOREIGN KEY (parent_forum_id) REFERENCES forum (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE2904019 FOREIGN KEY (thread_id) REFERENCES message_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message_thread ADD CONSTRAINT FK_607D18CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message_thread ADD CONSTRAINT FK_607D18C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message_thread_user ADD CONSTRAINT FK_695D943E8829462F FOREIGN KEY (message_thread_id) REFERENCES message_thread (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_thread_user ADD CONSTRAINT FK_695D943EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62016FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D38D93D649 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B29CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B111D17F9 FOREIGN KEY (last_comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1F55203D');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CDE12AB56');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C16FE72E1');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD16F4F95B');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD3D8E604F');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECDFE54D947');
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECD111D17F9');
        $this->addSql('ALTER TABLE forum_group DROP FOREIGN KEY FK_75B4E212B6011601');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE2904019');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FDE12AB56');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F16FE72E1');
        $this->addSql('ALTER TABLE message_thread DROP FOREIGN KEY FK_607D18CDE12AB56');
        $this->addSql('ALTER TABLE message_thread DROP FOREIGN KEY FK_607D18C16FE72E1');
        $this->addSql('ALTER TABLE message_thread_user DROP FOREIGN KEY FK_695D943E8829462F');
        $this->addSql('ALTER TABLE message_thread_user DROP FOREIGN KEY FK_695D943EA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAE92F8F78');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620DE12AB56');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62016FE72E1');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D38D93D649');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B29CCBAD0');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B111D17F9');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BDE12AB56');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B16FE72E1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DE12AB56');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64916FE72E1');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE forum');
        $this->addSql('DROP TABLE forum_group');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE message_thread');
        $this->addSql('DROP TABLE message_thread_user');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
