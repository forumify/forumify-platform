<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Message;

/**
 * @extends AbstractRepository<Message>
 */
class MessageRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Message::class;
    }
}
