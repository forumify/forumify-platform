<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\CalendarType;
use Forumify\Calendar\Entity\Calendar;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<Calendar>
 */
#[Route('/calendars', 'calendars')]
class CalendarController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.calendars.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.calendars.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.calendars.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.calendars.manage';

    protected function getEntityClass(): string
    {
        return Calendar::class;
    }

    protected function getTableName(): string
    {
        return 'Forumify\\CalendarTable';
    }

    /**
     * @param Calendar|null $data
     * @return FormInterface<object|null>
     */
    protected function getForm(?object $data): FormInterface
    {
        /** @var FormInterface<object|null> */
        return $this->createForm(CalendarType::class, $data);
    }
}
