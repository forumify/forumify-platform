<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $exception = $request->attributes->get('exception');
        return $this->render('@Forumify/frontend/error.html.twig', [
            'exception' => $exception,
        ]);
    }
}
