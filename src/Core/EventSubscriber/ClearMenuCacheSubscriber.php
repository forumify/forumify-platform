<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Entity\ACL;
use Forumify\Core\Entity\MenuItem;
use Forumify\Core\Event\EntityEvent;
use Forumify\Core\Event\EntityPostRemoveEvent;
use Forumify\Core\Event\EntityPostSaveEvent;
use Forumify\Core\Twig\Extension\MenuRuntime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\Cache\CacheInterface;

class ClearMenuCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // changes in menu items
            EntityPostSaveEvent::getName(MenuItem::class) => 'clearAllMenuCaches',
            EntityPostRemoveEvent::getName(MenuItem::class) => 'clearAllMenuCaches',

            // changes in permissions
            LoginSuccessEvent::class => 'clearUserMenuCache',
            EntityPostSaveEvent::getName(ACL::class) => 'clearAllMenuCaches',
            EntityPostRemoveEvent::getName(ACL::class) => 'clearAllMenuCaches',
        ];
    }

    public function clearAllMenuCaches(EntityEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof ACL && $entity->getEntity() !== MenuItem::class) {
            return;
        }

        $this->cache->invalidateTags([MenuRuntime::MENU_CACHE_TAG]);
    }

    public function clearUserMenuCache(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $cacheKey = MenuRuntime::createMenuCacheKey($user);
        $this->cache->delete($cacheKey);
    }
}
