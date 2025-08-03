<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250802134746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'consolidate messages per user';
    }

    public function up(Schema $schema): void
    {
        $usersPerThread = [];
        $participantResult = $this->connection->executeQuery("
            SELECT mtu.message_thread_id, mtu.user_id
            FROM message_thread_user mtu
            INNER JOIN message_thread mt ON mt.id = mtu.message_thread_id
            ORDER BY mt.created_at ASC
        ");
        while ($participant = $participantResult->fetchAssociative()) {
            $usersPerThread[$participant['message_thread_id']][] = $participant['user_id'];
        }

        $threadsToCombine = [];
        foreach ($usersPerThread as $threadId => $userIds) {
            if (count($userIds) <= 1) {
                $this->addSql('DELETE FROM message_thread WHERE id = ?', [$threadId]);
                continue;
            }

            sort($userIds);
            $threadsToCombine[implode('|', $userIds)][] = $threadId;
        }

        foreach ($threadsToCombine as $userIds => &$threadIds) {
            $firstThreadId = array_shift($threadIds);
            $this->addSql('UPDATE message_thread SET title = "" WHERE id = ?', [$firstThreadId]);
            foreach ($threadIds as $threadId) {
                $this->addSql('UPDATE message SET thread_id = ? WHERE thread_id = ?', [$firstThreadId, $threadId]);
                $this->addSql('DELETE FROM message_thread WHERE id = ?', [$threadId]);
            }
        }
    }

    public function down(Schema $schema): void
    {
    }
}
