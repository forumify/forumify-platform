<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function __invoke(): Response
    {
        $isCloudInstance = (bool)($_SERVER['FORUMIFY_HOSTED_INSTANCE'] ?? false);
        return $this->render('@Forumify/admin/dashboard/dashboard.html.twig', [
            'isCloudInstance' => $isCloudInstance,
        ]);
    }
}
