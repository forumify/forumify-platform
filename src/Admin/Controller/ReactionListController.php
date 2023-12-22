<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Forum\Repository\ReactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactionListController extends AbstractController
{
    #[Route('/reactions', name: 'reaction_list')]
    public function __invoke(ReactionRepository $reactionRepository): Response
    {
        return $this->render('@Forumify/admin/reaction/reaction_list.html.twig');
    }
}
