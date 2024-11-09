<?php

declare(strict_types=1);

namespace Forumify\Forum\EventSubscriber;

use Forumify\Core\Event\FrontendEvents;
use Forumify\Core\Event\FrontendRenderEvent;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ForumFrontendEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FrontendEvents::getName(FrontendEvents::RENDER, Forum::class) => 'setTemplateParams',
        ];
    }

    public function setTemplateParams(FrontendRenderEvent $renderEvent): void
    {
        /** @var ForumRepository $forumRepository */
        $forumRepository = $renderEvent->repository;
        /** @var Forum|null $forum */
        $forum = $renderEvent->item;

        $ungroupedForums = [];
        $groups = [];
        $childForums = $forumRepository->findByParent($forum);
        foreach ($childForums as $childForum) {
            $group = $childForum->getGroup();
            if ($group === null) {
                $ungroupedForums[] = $childForum;
                continue;
            }
            $groups[$group->getId()] = $group;
        }

        uasort($groups, static fn (ForumGroup $a, ForumGroup $b) => $a->getPosition() - $b->getPosition());

        $renderEvent->templateParameters = [
            'forum' => $forum,
            'ungroupedForums' => $ungroupedForums,
            'groups' => $groups,
        ];
    }
}
