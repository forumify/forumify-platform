<?php

declare(strict_types=1);

namespace Forumify\Automation\Trigger;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
class DoctrinePersistTrigger extends AbstractDoctrineTrigger implements TriggerInterface
{
    public static function getType(): string
    {
        return 'Doctrine: Persist';
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $this->trigger($event->getObject());
    }
}
