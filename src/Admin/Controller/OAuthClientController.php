<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Api\Entity\OAuthClient;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/clients', 'api_clients')]
class OAuthClientController extends AbstractCrudController
{
    protected bool $allowView = false;

    protected function getEntityClass(): string
    {
        return OAuthClient::class;
    }

    protected function getTableName(): string
    {
        return 'OAuthClientTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        return $this->createFormBuilder()->getForm();
    }

    protected function getTranslationPrefix(): string
    {
        return 'admin.api.clients.crud.';
    }
}
