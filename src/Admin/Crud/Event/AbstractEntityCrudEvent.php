<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEntityCrudEvent extends Event
{
    public function __construct(private readonly bool $new, private readonly mixed $entity)
    {
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }
}
