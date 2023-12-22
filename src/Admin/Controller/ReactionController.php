<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Forum\Entity\Reaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReactionController extends AbstractController
{
    #[Route('/reactions/{id<\d+>}', 'reaction')]
    public function __invoke(Reaction $reaction)
    {

    }
}
