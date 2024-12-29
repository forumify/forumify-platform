<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Forumify\Cms\Entity\Page;

final class Version20241229110916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add default ACL to all pages for backwards compatibility';
    }

    public function up(Schema $schema): void
    {
        $guestId = $this->connection->executeQuery('SELECT id FROM role WHERE slug = "guest"')->fetchOne();
        $userId = $this->connection->executeQuery('SELECT id FROM role WHERE slug = "user"')->fetchOne();

        $pageIds = $this->connection->executeQuery('SELECT id FROM page')->fetchFirstColumn();
        foreach ($pageIds as $pageId) {
            $this->connection->executeStatement('INSERT INTO acl (entity, entity_id, permission) VALUES (?, ?, ?)', [Page::class, $pageId, 'view']);
            $aclId = $this->connection->lastInsertId();

            $this->addSql('INSERT INTO acl_role VALUES (?, ?)', [$aclId, $guestId]);
            $this->addSql('INSERT INTO acl_role VALUES (?, ?)', [$aclId, $userId]);
        }
    }

    public function down(Schema $schema): void
    {
        // OK
    }
}
