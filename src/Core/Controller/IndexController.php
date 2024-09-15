<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly SettingRepository $settingRepository,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'], priority: -999)]
    public function __invoke(): Response
    {
        $indexOverride = $this->settingRepository->get('forumify.index') ?? '/';
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
