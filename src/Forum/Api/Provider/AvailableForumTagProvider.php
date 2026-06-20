<?php

declare(strict_types=1);

namespace Forumify\Forum\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\ForumTagRepository;

/**
 * @implements ProviderInterface<ForumTag>
 */
class AvailableForumTagProvider implements ProviderInterface
{
    public function __construct(
        private readonly ForumTagRepository $forumTagRepository,
        private readonly ForumRepository $forumRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $forumId = $uriVariables['forumId'] ?? null;
        $forum = $forumId ? $this->forumRepository->find($forumId) : null;

        return $this->forumTagRepository->findByForum($forum);
    }
}
