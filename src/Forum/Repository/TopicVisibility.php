<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\User;

/**
 * Which topics of a forum a specific user is allowed to see.
 */
enum TopicVisibility: string
{
    case All = 'all';
    case NotHidden = 'not_hidden';
    case Own = 'own';
    case OwnNotHidden = 'own_not_hidden';

    public static function forPermissions(bool $canViewAll, bool $canViewHidden): self
    {
        return match (true) {
            $canViewAll && $canViewHidden => self::All,
            $canViewAll => self::NotHidden,
            $canViewHidden => self::Own,
            default => self::OwnNotHidden,
        };
    }

    /**
     * Restricts a query to the topics of the given forums that are visible to the user. Forums are
     * grouped by their visibility so a single query can cover a whole forum tree.
     *
     * @param array<int, self> $visibilityPerForum
     * @return bool false when the query cannot match anything
     */
    public static function applyTo(QueryBuilder $qb, string $topicAlias, array $visibilityPerForum, ?User $user): bool
    {
        $forumIdsByVisibility = [];
        foreach ($visibilityPerForum as $forumId => $visibility) {
            $forumIdsByVisibility[$visibility->value][] = $forumId;
        }

        $visibleTopics = $qb->expr()->orX();
        $needsUser = false;
        foreach ($forumIdsByVisibility as $value => $forumIds) {
            $visibility = self::from($value);
            if (!$visibility->includesOthers() && $user === null) {
                continue;
            }

            $parameter = "visibleForums_$value";
            $conditions = ["$topicAlias.forum IN (:$parameter)"];
            if (!$visibility->includesHidden()) {
                $conditions[] = "$topicAlias.hidden = false";
            }
            if (!$visibility->includesOthers()) {
                $conditions[] = "$topicAlias.createdBy = :visibleTopicsUser";
                $needsUser = true;
            }

            $visibleTopics->add($qb->expr()->andX(...$conditions));
            $qb->setParameter($parameter, $forumIds);
        }

        if ($visibleTopics->count() === 0) {
            return false;
        }

        $qb->andWhere($visibleTopics);
        if ($needsUser) {
            $qb->setParameter('visibleTopicsUser', $user);
        }

        return true;
    }

    public function includesHidden(): bool
    {
        return $this === self::All || $this === self::Own;
    }

    public function includesOthers(): bool
    {
        return $this === self::All || $this === self::NotHidden;
    }
}
