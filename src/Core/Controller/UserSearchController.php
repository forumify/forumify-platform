<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/users/search', 'user_search')]
class UserSearchController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $searchTerm = $request->get('query');

        $result = $this->userRepository->createQueryBuilder('u')
            ->select('u.id', 'u.username', 'u.displayName')
            ->where('u.username LIKE :query')
            ->orWhere('u.displayName LIKE :query')
            ->setParameter('query', "%$searchTerm%")
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        return $this->json($result);
    }
}
