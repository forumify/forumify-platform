<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class IndexController extends AbstractController
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    #[Route('/', name: 'index', methods: ['GET'], priority: -999)]
    public function __invoke(SettingRepository $settingRepository): Response
    {
        $indexOverride = $settingRepository->get('forumify.index') ?? '/';
        if (!str_starts_with($indexOverride, '/')) {
            $indexOverride = '/' . $indexOverride;
        }

        if ($indexOverride === '/') {
            return $this->render('@Forumify/frontend/index.html.twig');
        }

        $res = $this->router->match($indexOverride);
        if (!empty($res['_controller'])) {
            return $this->forward($res['_controller']);
        }

        return $this->redirect($indexOverride, Response::HTTP_TEMPORARY_REDIRECT);
    }
}
