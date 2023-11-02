<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'], priority: -999)]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/frontend/index.html.twig');
    }
}
