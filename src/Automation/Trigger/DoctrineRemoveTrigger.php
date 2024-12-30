<?php

declare(strict_types=1);

namespace Forumify\Automation\Trigger;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postRemove)]
class DoctrineRemoveTrigger extends AbstractDoctrineTrigger implements TriggerInterface
{
    public static function getType(): string
    {
        return 'Doctrine: Remove';
    }

    public function postRemove(PostRemoveEventArgs $event): void
    {
        $this->trigger($event->getObject());
    }
}
