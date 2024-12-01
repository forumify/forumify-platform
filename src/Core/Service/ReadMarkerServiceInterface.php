<?php
declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.read_marker.service')]
interface ReadMarkerServiceInterface
{
    public function supports(mixed $subject): bool;

    public function read(User $user, mixed $subject): bool;

    public function markAsRead(User $user, mixed $subject): void;
}
