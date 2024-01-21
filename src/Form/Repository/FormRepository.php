<?php

declare(strict_types=1);

namespace Forumify\Form\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Form\Entity\Form;

class FormRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Form::class;
    }
}
