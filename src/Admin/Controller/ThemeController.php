<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\ThemeType;
use Forumify\Core\Entity\Theme;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('themes', 'themes')]
class ThemeController extends AbstractCrudController
{
    protected bool $allowCreate = false;
    protected bool $allowDelete = false;

    protected ?string $permissionView = 'forumify.admin.settings.themes.view';
    protected ?string $permissionEdit = 'forumify.admin.settings.themes.manage';

    protected string $formTemplate = '@Forumify/admin/theme/theme.html.twig';

    protected function getEntityClass(): string
    {
        return Theme::class;
    }

    protected function getTableName(): string
    {
        return 'Forumify\\ThemeTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(ThemeType::class, $data);
    }

    #[Route('/{id}/templates', '_templates')]
    #[IsGranted('forumify.admin.settings.themes.manage')]
    public function templateEditor(Theme $theme): Response
    {
        return $this->render('@Forumify/admin/theme/template_editor.html.twig', [
            'theme' => $theme,
        ]);
    }
}
