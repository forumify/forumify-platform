<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides the forum tree without lazy loading a query per forum.
 */
class ForumTreeService implements ResetInterface
{
    /** @var array<int, Forum>|null */
    private ?array $forums = null;

    public function __construct(private readonly ForumRepository $forumRepository)
    {
    }

    /**
     * @return array<int, Forum> keyed by id, with initialized children collections
     */
    public function getForums(): array
    {
        return $this->forums ??= $this->forumRepository->findTree();
    }

    /**
     * @return array<Forum> ordered by position
     */
    public function getChildren(?Forum $parent): array
    {
        $forums = $this->getForums();

        return $parent !== null
            ? array_values($parent->getChildren()->toArray())
            : array_values(array_filter($forums, static fn (Forum $forum) => $forum->getParent() === null));
    }

    public function reset(): void
    {
        $this->forums = null;
    }
}
