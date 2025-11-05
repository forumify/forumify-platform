<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @template TEntity
 */
abstract class AbstractEntityCrudEvent extends Event
{
    /**
     * @param FormInterface<TEntity> $form
     * @param TEntity $entity
     */
    public function __construct(
        private readonly bool $new,
        private readonly FormInterface $form,
        private readonly mixed $entity,
    ) {
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * @return FormInterface<TEntity>
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return TEntity
     */
    public function getEntity(): mixed
    {
        return $this->entity;
    }
}
