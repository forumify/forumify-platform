<?php

declare(strict_types=1);

namespace Forumify\Automation\Trigger;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postUpdate)]
class DoctrineUpdateTrigger extends AbstractDoctrineTrigger implements TriggerInterface
{
    public static function getType(): string
    {
        return 'Doctrine: Update';
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $this->trigger($event->getObject());
    }
}
