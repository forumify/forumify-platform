<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\OAuthClientType;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/oauth-clients', 'oauth_clients')]
class OAuthClientController extends AbstractCrudController
{
    protected ?string $permissionView = 'forumify.admin.settings.oauth_clients.view';
    protected ?string $permissionCreate = 'forumify.admin.settings.oauth_clients.manage';
    protected ?string $permissionEdit = 'forumify.admin.settings.oauth_clients.manage';
    protected ?string $permissionDelete = 'forumify.admin.settings.oauth_clients.manage';

    protected function getEntityClass(): string
    {
        return OAuthClient::class;
    }

    protected function getTableName(): string
    {
        return 'Forumify\\OAuthClientTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createForm(OAuthClientType::class, $data);
    }

    protected function redirectAfterSave(mixed $entity): Response
    {
        return $this->redirectToRoute($this->getRoute('edit'), ['identifier' => $entity->getId()]);
    }
}
