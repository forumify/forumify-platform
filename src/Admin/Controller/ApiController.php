<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.settings.api.manage')]
class ApiController extends AbstractController
{
    #[Route('api', 'api')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/admin/api/api.html.twig');
    }
}
