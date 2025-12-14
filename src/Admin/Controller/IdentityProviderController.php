<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\IdentityProviderType;
use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<IdentityProvider>
 */
#[Route('/identity-providers', 'identity_providers')]
class IdentityProviderController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.identity_providers.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.identity_providers.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.identity_providers.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.identity_providers.manage';

    protected function getEntityClass(): string
    {
        return IdentityProvider::class;
    }

    protected function getTableName(): string
    {
        return 'Forumify\\IdentityProviderTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(IdentityProviderType::class, $data);
    }

    protected function redirectAfterSave(mixed $entity, bool $isNew): Response
    {
        if ($isNew) {
            return $this->redirectToRoute($this->getRoute('edit'), ['identifier' => $entity->getId()]);
        }
        return parent::redirectAfterSave($entity, $isNew);
    }
}
