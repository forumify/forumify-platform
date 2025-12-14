<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template TSubject of object
 */
#[AutoconfigureTag('forumify.read_marker.service')]
interface ReadMarkerServiceInterface
{
    /**
     * @return class-string<TSubject>
     */
    public static function getEntityClass(): string;

    /**
     * @param TSubject $subject
     */
    public function read(User $user, mixed $subject): bool;

    /**
     * @param TSubject $subject
     */
    public function markAsRead(User $user, mixed $subject): void;
}
