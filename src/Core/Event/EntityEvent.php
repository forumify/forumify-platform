<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class EntityEvent extends Event
{
    public function __construct(private readonly object $entity)
    {
    }

    abstract public static function getName(string $entity): string;

    public function getEntity(): object
    {
        return $this->entity;
    }
}
