<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Cms\Entity\Page;
use Forumify\Cms\Form\PageType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<Page>
 */
#[Route('pages', 'page')]
class PageController extends AbstractCrudController
{
    protected string $formTemplate = '@Forumify/admin/cms/page/page.html.twig';

    protected ?string $permissionView = 'forumify.admin.cms.pages.view';
    protected ?string $permissionCreate = 'forumify.admin.cms.pages.manage';
    protected ?string $permissionEdit = 'forumify.admin.cms.pages.manage';
    protected ?string $permissionDelete = 'forumify.admin.cms.pages.manage';

    protected function getTranslationPrefix(): string
    {
        return 'admin.cms.pages.';
    }

    protected function getEntityClass(): string
    {
        return Page::class;
    }

    protected function getTableName(): string
    {
        return 'Forumify\\PageTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(PageType::class, $data);
    }

    protected function redirectAfterSave(mixed $entity): Response
    {
        return $this->redirectToRoute('forumify_admin_cms_page_edit', ['identifier' => $entity->getId()]);
    }
}
