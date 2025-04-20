<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250420091404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $topicsWithImages = $this
            ->connection
            ->executeQuery('SELECT id, image FROM topic WHERE image IS NOT NULL AND image != ""')
            ->fetchAllAssociative()
        ;

        $this->addSql(<<<'SQL'
            CREATE TABLE topic_image (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7E0EA83D1F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic_image ADD CONSTRAINT FK_7E0EA83D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP image
        SQL);

        foreach ($topicsWithImages as $topic) {
            $this->addSql('INSERT INTO topic_image (topic_id, image, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)', [
                $topic['id'],
                $topic['image'],
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE topic_image DROP FOREIGN KEY FK_7E0EA83D1F55203D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE topic_image
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD image VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
