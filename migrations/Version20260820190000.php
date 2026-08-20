<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the topic_image table with a simple_array column on topic.';
    }

    public function up(Schema $schema): void
    {
        $images = $this
            ->connection
            ->executeQuery('SELECT topic_id, image FROM topic_image ORDER BY topic_id ASC, created_at ASC, id ASC')
            ->fetchAllAssociative()
        ;

        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD images LONGTEXT DEFAULT NULL
        SQL);

        foreach ($this->groupByTopic($images, 'topic_id', 'image') as $topicId => $topicImages) {
            $this->addSql('UPDATE topic SET images = ? WHERE id = ?', [
                implode(',', $topicImages),
                $topicId,
            ]);
        }

        $this->addSql(<<<'SQL'
            ALTER TABLE topic_image DROP FOREIGN KEY FK_7E0EA83D1F55203D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE topic_image
        SQL);
    }

    public function down(Schema $schema): void
    {
        $topics = $this
            ->connection
            ->executeQuery("SELECT id, images FROM topic WHERE images IS NOT NULL AND images != ''")
            ->fetchAllAssociative()
        ;

        $this->addSql(<<<'SQL'
            CREATE TABLE topic_image (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7E0EA83D1F55203D (topic_id), INDEX IDX_7E0EA83D8B8E8428 (created_at), INDEX IDX_7E0EA83D43625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic_image ADD CONSTRAINT FK_7E0EA83D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE
        SQL);

        foreach ($topics as $topic) {
            foreach (explode(',', (string) $topic['images']) as $image) {
                $this->addSql('INSERT INTO topic_image (topic_id, image, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)', [
                    $topic['id'],
                    $image,
                ]);
            }
        }

        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP images
        SQL);
    }

    /**
     * @param array<array<string, mixed>> $rows
     * @return array<int, list<string>>
     */
    private function groupByTopic(array $rows, string $key, string $value): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row[$key]][] = (string) $row[$value];
        }

        return $grouped;
    }
}
